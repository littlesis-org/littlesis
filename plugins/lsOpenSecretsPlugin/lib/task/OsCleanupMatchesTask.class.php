<?php

class OsCleanupMatchesTask extends sfTask
{
  protected
    $db = null,
    $rawDb = null,
    $debugMode = null,
    $cycles = null,
    $startTime = null;


  protected function configure()
  {
    $this->namespace        = 'opensecrets';
    $this->name             = 'cleanup-matches';
    $this->briefDescription = 'Removes duplicate matches between entities and OpenSecrets donors';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of duplicate sets to clean up', 100);
  }


  protected function execute($arguments = array(), $options = array())
  {
    $this->init($arguments, $options);

    $sql = 'SELECT entity_id, cycle, transaction_id, COUNT(*) AS num FROM os_entity_transaction GROUP BY entity_id, cycle, transaction_id HAVING num > 1 LIMIT ' . $options['limit'];
    $stmt = $this->db->execute($sql);
    $dupes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($this->debugMode)
    {
      print("Found " . count($dupes) . " sets of duplicates\n");
    }

    foreach ($dupes as $dupe)
    {
      if ($this->debugMode)
      {
        print("Found duplicate records for " . $dupe['entity_id'] . ", " . $dupe['cycle'] . ", " . $dupe['transaction_id'] . "\n");        
      }

      $sql = 'SELECT * FROM os_entity_transaction WHERE entity_id = ? AND cycle = ? AND transaction_id = ? ORDER BY id ASC';
      $stmt = $this->db->execute($sql, array($dupe['entity_id'], $dupe['cycle'], $dupe['transaction_id']));
      $trans = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
      $keep_verified_id = null;
      $verified_count = 0;
      
      $keep_synced_id = null;
      $synced_count = 0;

      $all_synced = true;
      $hashes = array();
      $verified_hashes = array();
      $users = array();
      $first_id = $trans[0]['id'];
      $first_reviewed_id = null;
      
      foreach ($trans as $tran)
      {
        $hash = $tran['is_verified'] . $tran['is_processed'] . $tran['is_synced'] . $tran['reviewed_by_user_id'] . $tran['reviewed_at'];
        $hashes[$hash] = 1;

        if ($tran['reviewed_by_user_id'])
        {
          $users[$tran['reviewed_by_user_id']] = 1;
        }

        if (!$first_reviewed_id && $tran['reviewed_by_user_id'])
        {
          $first_reviewed_id = $tran['id'];
        }
      
        if ($tran['is_verified'] && $tran['is_synced'])
        {
          $verified_hashes[$hash] = 1;

          if (!$keep_verified_id)
          {
            $keep_verified_id = $tran['id'];
          }
          
          $verified_count++;
        }
        
        if ($tran['is_synced'] == 1 && $tran['reviewed_by_user_id'])
        {
          if (!$keep_synced_id)
          {
            $keep_synced_id = $tran['id'];
          }
          
          $synced_count++;
        }

        if ($tran['is_synced'] == 0)
        {
          $all_synced = false;
        }
      }

//var_dump($keep_verified_id, $verified_count, $keep_sycned_id, $synced_count, $all_synced, count($hashes), count($users));
      
      if ($all_synced && $keep_verified_id && $verified_count == 1)
      {
        if ($this->debugMode)
        {
          print("+ Keeping verified record " . $keep_verified_id . "; deleting " . (count($trans) - 1) . " others...\n");
        }

        //delete all the others
        $sql = 'DELETE FROM os_entity_transaction WHERE entity_id = ? AND cycle = ? AND transaction_id = ? AND id <> ?';
        $stmt = $this->db->execute($sql, array($dupe['entity_id'], $dupe['cycle'], $dupe['transaction_id'], $keep_verified_id));
      }
      elseif ($all_synced && $keep_synced_id && $verified_count == 0 && $synced_count == 1)
      {
        if ($this->debugMode)
        {
          print("+ Keeping synced and reviewed record " . $keep_synced_id . "; deleting " . (count($trans) - 1) . " others...\n");
        }

        //delete all the others
        $sql = 'DELETE FROM os_entity_transaction WHERE entity_id = ? AND cycle = ? AND transaction_id = ? AND id <> ?';
        $stmt = $this->db->execute($sql, array($dupe['entity_id'], $dupe['cycle'], $dupe['transaction_id'], $keep_synced_id));
      }
      elseif (count($hashes) == 1)
      {
        if ($this->debugMode)
        {
          print("+ Records all identical; keeping first record " . $first_id . "; deleting " . (count($trans) - 1) . " others...\n");
        }

        //delete all the others
        $sql = 'DELETE FROM os_entity_transaction WHERE entity_id = ? AND cycle = ? AND transaction_id = ? AND id <> ?';
        $stmt = $this->db->execute($sql, array($dupe['entity_id'], $dupe['cycle'], $dupe['transaction_id'], $first_id));      
      }
      elseif (count($users) == 1 && $all_synced && $verified_count == 0)
      {
        if ($this->debugMode)
        {
          print("+ Reviewed records all identical; keeping first reviewed record " . $first_reviewed_id . "; deleting " . (count($trans) - 1) . " others...\n");
        }

        //delete all the others
        $sql = 'DELETE FROM os_entity_transaction WHERE entity_id = ? AND cycle = ? AND transaction_id = ? AND id <> ?';
        $stmt = $this->db->execute($sql, array($dupe['entity_id'], $dupe['cycle'], $dupe['transaction_id'], $first_reviewed_id));            
      }
      elseif ($all_synced && count($verified_hashes) == 1 && $keep_verified_id)
      {
        if ($this->debugMode)
        {
          print("+ Verified records all identical; keeping first verified record " . $keep_verified_id . "; deleting " . (count($trans) - 1) . " others...\n");
        }

        //delete all the others
        $sql = 'DELETE FROM os_entity_transaction WHERE entity_id = ? AND cycle = ? AND transaction_id = ? AND id <> ?';
        $stmt = $this->db->execute($sql, array($dupe['entity_id'], $dupe['cycle'], $dupe['transaction_id'], $keep_verified_id));          
      }
    }
  }
  
  
  protected function init($arguments, $options)
  {
    $this->startTime = microtime(true);

    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);
    $this->db = Doctrine_Manager::connection();
    $rawDb = $databaseManager->getDatabase('raw');
    $this->rawDb = Doctrine_Manager::connection($rawDb->getParameter('dsn'));  
    
    $this->cycles = explode(',', $options['cycles']);
    $this->debugMode = $options['debug_mode'];
  }
}