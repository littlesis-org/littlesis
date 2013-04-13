<?php

class OsPreprocessMatchesTask extends sfTask
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
    $this->name             = 'preprocess-matches';
    $this->briefDescription = 'Identifies likely matches between entities and OpenSecrets donors';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of unprocessed entities to match', 100);
    $this->addOption('offset', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of unprocessed entities to skip', 0);
    $this->addOption('cycles', null, sfCommandOption::PARAMETER_REQUIRED, 'Comma-delimited list of election cycles to search', '2012,2010,2008,2006,2004,2002,2000,1998,1996,1994,1992,1990');
    $this->addOption('mode', null, sfCommandOption::PARAMETER_REQUIRED, '\'all\' will try all entities; \'recent\' will only try ones created in the past hour, \'all-update\' will preprocess entities that have already been preprocessed', 'recent');
    $this->addOption('period', null, sfCommandOption::PARAMETER_REQUIRED, 'Time period to look for new entities in, if mode is \'recent\'', '-1 hour');
  }


  protected function execute($arguments = array(), $options = array())
  {
    if (!$this->safeToRun())
    {
      if ($options['debug_mode'])
      {
        print("Script already running!\n");
        die;
      }
    }

    $this->init($arguments, $options);
    $update = ($this->mode == 'all-update');

    //get entities not in the preprocess log
    $entities = $this->getEntitiesToPreprocess($options['limit'], $options['offset'], $options['period']);

    foreach ($entities as $entity)
    {
      if (!$update && $entity['is_processed'])
      {
        print("Already processed entity " . $entity['name'] . " (" . $entity['id'] . "); skipping...\n");

        continue;
      }

      try
      {
        $this->db->beginTransaction();        

        $updated = $this->matchTransactionIds($entity, $this->cycles, $update);
        $this->logEntity($entity, $this->cycles, $update, $updated);

        $this->db->commit();
      }
      catch (Exception $e)
      {
        $this->db->rollback();
        throw $e;
      } 
    }
    
    print("Pre-processed " . count($entities) . " entities in " . (microtime(true) - $this->startTime) . " s\n");
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
    $this->mode = $options['mode'];
  }
  

  public function getEntitiesToPreprocess($limit, $offset, $period)
  {
    if ($this->mode == 'all' || $this->mode == 'all-update')
    {
      $sql = 'SELECT e.*, p.name_first, p.name_middle, p.name_last, p.name_nick, ' . 
             'IF(COUNT(ep.cycle) > 0, 1, 0) is_processed ' . 
             'FROM entity e LEFT JOIN os_entity_preprocess ep ON (e.id = ep.entity_id) ' .
             'LEFT JOIN person p ON (p.entity_id = e.id) ' .
             'WHERE e.primary_ext = ? AND e.is_deleted = 0 AND p.name_last IS NOT NULL AND p.name_first IS NOT NULL ' .
             'GROUP BY e.id LIMIT ' . $limit;
      $params = array('Person');
    }
    else
    {
      $sql = 'SELECT e.*, p.name_first, p.name_middle, p.name_last, p.name_nick, ' . 
             'IF(COUNT(ep.cycle) > 0, 1, 0) is_processed ' . 
             'FROM entity e LEFT JOIN os_entity_preprocess ep ON (e.id = ep.entity_id) ' .
             'LEFT JOIN person p ON (p.entity_id = e.id) ' .
             'WHERE e.created_at > ? AND e.primary_ext = ? AND e.is_deleted = 0 AND p.name_last IS NOT NULL AND p.name_first IS NOT NULL ' . 
             'GROUP BY e.id LIMIT ' . $limit;
      $params = array(date('Y-m-d H:i:s', strtotime($period)), 'Person');
    }
                      
    $stmt = $this->db->execute($sql, $params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  
  protected function matchDonorIds(Array $entity, $cycles)
  {
    if ($this->debugMode)
    {
      print("Processing entity " . $entity['id'] . " matches...\n");
    }

    //look for matches
    $where = 'donor_name_last = ? AND donor_name_first IN (?, ?) ' . 
             'AND cycle IN (' . implode(',', $cycles) . ') ' . 
             'AND donor_id <> ?';
    $params = array($entity['name_last'], $entity['name_first'], substr($entity['name_first'], 0, 1), '         ');
    
    if ($entity['name_middle'])
    {
      if (strlen($entity['name_middle']) > 1)
      {
        $where .= ' AND (donor_name_middle IS NULL OR donor_name_middle = ? OR donor_name_middle = ?)';
        $params = array_merge($params, array($entity['name_middle'], substr($entity['name_middle'], 0, 1)));
      }
      else
      {
        $where .= ' AND (donor_name_middle IS NULL OR donor_name_middle = ? OR donor_name_middle LIKE ?)';
        $params = array_merge($params, array($entity['name_middle'], $entity['name_middle'] . '%'));
      }      
    }

    $sql = 'SELECT DISTINCT donor_id FROM os_donation FORCE INDEX (last_first_idx) WHERE ' . $where;
    $stmt = $this->rawDb->execute($sql, $params);
    $donorIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    //log matches
    if (count($donorIds))
    {   
      $sql = 'INSERT INTO os_entity_donor (entity_id, donor_id) VALUES';
      $params = array();

      foreach ($donorIds as $donorId)
      {
        $sql .= ' (?, ?),';
        $params = array_merge($params, array($entity['id'], $donorId));
          
        if ($this->debugMode)
        {
          print("+ Found possible match with donor ID " . $donorId . "\n");
        }
      }
      
      $stmt = $this->db->execute(substr($sql, 0, strlen($sql) - 1), $params);
    }
  }


  protected function matchTransactionIds(Array $entity, $cycles, $update=false, $useAliases=true)
  {
    if ($this->debugMode)
    {
      print("Processing entity " . $entity['id'] . " matches...\n");
    }

    if (!$update)
    {
      //if there are preprocessed donor_ids, create the transaction matches based on those
      $sql = 'SELECT donor_id FROM os_entity_donor WHERE entity_id = ? AND is_verified = 1 LIMIT 1';
      $stmt = $this->db->execute($sql, array($entity['id']));
      $donorIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
      
      if (count($donorIds))
      {
        print("+ Found existing donor ID matches; converting to transaction matches\n");
  
        $sql = 'INSERT INTO os_entity_transaction ' .
               '(entity_id, cycle, transaction_id, match_code, is_verified, is_processed, is_synced, ' . 
               'reviewed_by_user_id, reviewed_at, locked_by_user_id, locked_at) ' . 
               'SELECT entity_id, cycle, row_id AS transaction_id, match_code, is_verified, ' .
               'is_processed, is_synced, reviewed_by_user_id, reviewed_at, locked_by_user_id, locked_at ' .
               'FROM ls_beta.os_entity_donor ed ' . 
               'JOIN ls_beta_raw.os_donation d ON (d.donor_id = ed.donor_id) ' .
               'WHERE ed.entity_id = ?';
        $stmt = $this->db->execute($sql, array($entity['id']));
  
        return false;
      }
    }

    //if we're updating entities that have already been preprocessed, first count how many
    //preprocess matches we already have for the specified cycles, for later comparison
    if ($update)
    {
      $sql = 'SELECT cycle, transaction_id FROM os_entity_transaction WHERE entity_id = ? AND cycle IN (' . implode(',', $cycles) . ') ';
      $stmt = $this->db->execute($sql, array($entity['id']));
      $old_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $beforeCount = count($old_transactions);
      $old_hashes = array();

      foreach ($old_transactions as $old_trans)
      {
        $old_hashes[$old_trans['cycle'] . ':' . $old_trans['transaction_id']] = true;
      }
    }

    $transactions = self::getMatches($entity, $cycles, $this->db, $this->rawDb, $useAliases);

    if (!$update || ($update && (count($transactions) > $beforeCount)))
    {
      //log matches
      if (count($transactions))
      {
        //insert statement should ignore duplicate errors if we're updating entities we've already preprocessed
        $sql = 'INSERT ' . ($update ? 'IGNORE ' : '') . 'INTO os_entity_transaction (entity_id, cycle, transaction_id) VALUES';
        $params = array();
  
        foreach ($transactions as $transaction)
        {
          //skip the donation if there's already a record for it in os_entity_transaction
          if (array_key_exists($transaction['cycle'] . ':' . $transaction['row_id'], $old_hashes))
          {
            continue;
          }

          $sql .= ' (?, ?, ?),';
          $params = array_merge($params, array($entity['id'], $transaction['cycle'], $transaction['row_id']));
            
          if ($this->debugMode)
          {
            print("+ Found possible match with transaction " . $transaction['row_id'] . " (" . $transaction['cycle'] . ")\n");
          }
        }
        
        $stmt = $this->db->execute(substr($sql, 0, strlen($sql) - 1), $params);
      }
    }
    
    return $update && $beforeCount && (count($transactions) > $beforeCount);
  }
  
  static function getMatches(Array $entity, Array $cycles, $db, $rawDb, $useAliases=true)
  {
    $first_names = array($entity['name_first'], substr($entity['name_first'], 0, 1));
    if ($entity['name_nick']) { $first_names[] = $entity['name_nick']; }

    if ($useAliases) 
    {
      //use first names from aliases
      $sql = 'SELECT DISTINCT(name) FROM alias where entity_id = ?';
      $stmt = $db->execute($sql, array($entity['id']));
      $aliases = $stmt->fetchAll(PDO::FETCH_COLUMN);

      foreach ($aliases as $alias)
      {
        $parts = explode(" ", $alias);
  
        if (in_array($parts[0], PersonTable::$nameParsePrefixes))
        {
          if (count($parts) > 2)
          {
            if (trim($parts[1]) != $entity['name_first'])
            {
              $first_names[] = $parts[1];
            }
          }
        }
        else
        {
          if (count($parts) > 1)
          {
            if (trim($parts[0]) != $entity['name_first'])
            {
              $first_names[] = $parts[0];
            }
          }
        }
      }
    }

    $first_name_sql = "(" . implode(", ", array_fill(0, count($first_names), "?")) . ")";

    //look for matches based on name
    $where = 'donor_name_last = ? AND donor_name_first IN ' . $first_name_sql . ' ' . 
             'AND cycle IN (' . implode(',', $cycles) . ') ' . 
             'AND donor_id <> ?';
    $params = $first_names;
    array_unshift($params, $entity['name_last']);    
    $params[] = '         ';
    
    if ($entity['name_middle'])
    {
      if (strlen($entity['name_middle']) > 1)
      {
        $where .= ' AND (donor_name_middle IS NULL OR donor_name_middle = ? OR donor_name_middle = ?)';
        $params = array_merge($params, array($entity['name_middle'], substr($entity['name_middle'], 0, 1)));
      }
      else
      {
        $where .= ' AND (donor_name_middle IS NULL OR donor_name_middle = ? OR donor_name_middle LIKE ?)';
        $params = array_merge($params, array($entity['name_middle'], $entity['name_middle'] . '%'));
      }      
    }

    $sql = 'SELECT cycle, row_id FROM os_donation FORCE INDEX (last_first_idx) WHERE ' . $where;
    $stmt = $rawDb->execute($sql, $params);

    return $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);  
  }
  
  
  protected function logEntity(Array $entity, $cycles, $update=false, $updated=false)
  {
    if (count($cycles))
    {
      $sql = 'INSERT IGNORE INTO os_entity_preprocess (entity_id, cycle, processed_at) VALUES';
      $params = array();

      foreach ($cycles as $cycle)
      {
        $sql .= ' (?, ?, ?),';
        $params = array_merge($params, array($entity['id'], $cycle, date('Y-m-d H:i:s')));
      }
      
      $sql = substr($sql, 0, strlen($sql) - 1);

      if ($update && $updated)
      {
        $sql .= ' ON DUPLICATE KEY UPDATE updated_at = \'' . date('Y-m-d H:i:s') . '\'';      
      }

      $stmt = $this->db->execute($sql, $params);
    }
  }


  static function preprocessEntity($entity, Array $cycles=null, $useAliases=true)
  {
    if (!$cycles)
    {
      $cycles = array(2012, 2010, 2008, 2006, 2004, 2002, 2000, 1998, 1996, 1994, 1992, 1990);
    }


    $db = Doctrine_Manager::connection();
    $databaseManager = sfContext::getInstance()->getDatabaseManager();
    $rawDb = $databaseManager->getDatabase('raw');
    $rawDb = Doctrine_Manager::connection($rawDb->getParameter('dsn'));  


    $transactions = self::getMatches($entity, $cycles, $db, $rawDb, $useAliases);


    //log matches
    if (count($transactions))
    {   
      $sql = 'INSERT IGNORE INTO os_entity_transaction (entity_id, cycle, transaction_id) VALUES';
      $params = array();

      foreach ($transactions as $transaction)
      {
        $sql .= ' (?, ?, ?),';
        $params = array_merge($params, array($entity['id'], $transaction['cycle'], $transaction['row_id']));          
      }
      
      $stmt = $db->execute(substr($sql, 0, strlen($sql) - 1), $params);
    }
    

    //mark as preprocessed    
    $sql = 'INSERT IGNORE INTO os_entity_preprocess (entity_id, cycle, processed_at) VALUES';
    $params = array();

    foreach ($cycles as $cycle)
    {
      $sql .= ' (?, ?, ?),';
      $params = array_merge($params, array($entity['id'], $cycle, date('Y-m-d H:i:s')));
    }

    $stmt = $db->execute(substr($sql, 0, strlen($sql) - 1), $params);
  }


  protected function safeToRun()
  {
    $proc_id = getmypid();
    exec('ps aux -ww | grep symfony | grep :preprocess-matches | grep -v ' . $proc_id . ' | grep -v grep', $status_arr);

    foreach ($status_arr as $status)
    {
      //sometimes the shell startup command also appears, which is fine (script is still safe to run)
      if (preg_match('/sh\s+\-c/isu', $status) == 0)
      {
        return false;
      }
    }

    return true;    
  }
}