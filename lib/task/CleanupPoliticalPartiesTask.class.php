<?php

class CleanupPoliticalPartiesTask extends sfBaseTask
{
  protected $db = null;
  protected $rawDb = null;
  protected $insertStmt = null;
  protected $selectStmt = null;

  protected function configure()
  {
    $this->namespace        = 'cleanup';
    $this->name             = 'political-parties';
    $this->briefDescription = 'adds political parties for political candidates';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Maximum number to process', 1000);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Make changes to database?', false);
  }

  protected function execute($arguments = array(), $options = array())
  {
    //set start time
    $time = microtime(true);

    //connect to raw database
    $this->init($arguments, $options);
    
    //find political candidates who don't have political party
    $sql = 'SELECT e.id,e.name,pc.crp_id FROM entity e LEFT JOIN political_candidate pc ON pc.entity_id = e.id LEFT JOIN person p on p.entity_id = e.id WHERE p.party_id IS NULL AND pc.crp_id IS NOT NULL AND pc.crp_id <> "" and e.is_deleted = 0 LIMIT ' . $options['limit'];
            
    $stmt = $this->db->execute($sql);
    
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($rows as $row)
    {
      echo 'processing ' . $row['name'] . " ::\n";
      $sql = 'SELECT name,party,candidate_id FROM os_candidate WHERE candidate_id = ?';
      
      $stmt = $this->rawDb->execute($sql,array($row['crp_id']));
      $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $sql = 'select e.id from entity e limit 1';    
      $stmt = $this->db->execute($sql);
$stmt->fetchAll(PDO::FETCH_ASSOC);
      if (count($matches))
      {
        $party_id = $this->processParty($matches[0]['party']);
        echo "\t match found: " . $matches[0]['name'] . ', with party ' . $party_id . "/" . $matches[0]['party'] . "\n";
        
        if ($party_id && !$options['test_mode'])
        {
          $db = $this->databaseManager->getDatabase('main');
          $this->db = Doctrine_Manager::connection($db->getParameter('dsn'), 'main');
          $person = Doctrine::getTable('Person')->findOneByEntityId($row['id']);
	if (!$person) die;
          $person->party_id = $party_id;
          $person->save();
          echo "\t\t current political party saved as " . $person->party_id . "\n";
          
          $q = LsDoctrineQuery::create()
              ->from('Relationship r')
              ->where('r.entity1_id = ? and r.entity2_id = ? and r.category_id = ?', array($row['id'],$party_id,3));
          if (!$q->fetchOne())
          {
            $r = new Relationship;
            $r->entity1_id = $row['id'];
            $r->entity2_id = $party_id;
            $r->setCategory('Membership');
            $r->is_current = true;
            $r->save();
            echo "\t\t party membership saved\n";
          }

        }
      }
      else echo "\t no matches found\n";
      
    }
    
  }  
  
  protected function init($arguments, $options)
  {
    $this->startTime = microtime(true);

    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $this->databaseManager = new sfDatabaseManager($configuration);
    $this->databaseManager->initialize($configuration);
    $db = $this->databaseManager->getDatabase('main');
    $this->db = Doctrine_Manager::connection($db->getParameter('dsn'), 'main');
    $rawDb = $this->databaseManager->getDatabase('raw');
    $this->rawDb = Doctrine_Manager::connection($rawDb->getParameter('dsn'), 'raw');  
  }
  
  protected function processParty($abbrev)
  {
    $party_id = null;
    if ($abbrev == 'R')
    {
      $party_id = 12901;
    }
    else if ($abbrev == 'D')
    {
      $party_id = 12886;
    }
    return $party_id;
  }
}
