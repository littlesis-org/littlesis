<?php

class OsMatchIndustryCodesTask extends LsTask
{
  protected $db = null;
  protected $rawDb = null;
  protected $insertStmt = null;
  protected $selectStmt = null;
  protected $limit = null;
  protected $expirationDate = null;
  public static $logKey = 'last_update';
  
  protected function configure()
  {
    $this->namespace        = 'opensecrets';
    $this->name             = 'match-industry-codes';
    $this->briefDescription = 'Links entities with OpenSecrets industry codes based on donation records';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of entities to processs', 1000);
    $this->addOption('types', null, sfCommandOption::PARAMETER_REQUIRED, 'Types of entity (person, org) to processs', 'Person,Org');
    $this->addOption('expiration_date', null, sfCommandOption::PARAMETER_REQUIRED, 'Match entities if no matches found since this time (eg, "-6 months")', null);
    $this->addOption('exact_name_override', null, sfCommandOption::PARAMETER_REQUIRED, 'Match org if exact name found in donation record', true);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $time = microtime(true);
    
    $this->init($arguments, $options);
    $count = 0;
    $this->printDebug("Matching OpenSecrets entities with OpenSecrets industry codes...");

    if (in_array('Person', $this->types))
    {
      $persons = $this->getUnmatchedPersons();

      foreach ($persons as $person)
      {
        if ($this->isMatched($person['id']))
        {
          continue;
        }
        
        $this->printDebug("\nProcessing " . $person['name'] . " [" . $person['id'] . "]...");
        $newCategories = OsPerson::updateCategories($person['id']);

        foreach ($newCategories as $categoryId)
        {
          $this->printDebug("+ added industry category: " . $this->getCategoryName($categoryId) . " [" . $categoryId . "]");
        }

        $count++;
      }      
    }

      
    if (in_array('Org', $this->types)) 
    {   
      $orgs = $this->getUnmatchedOrgs();

      foreach ($orgs as $org)
      {
        if ($this->isMatched($org['id'])) { continue; }
        if ($this->isSkippableEntity($org['id'])) { continue; }

        $this->printDebug("\nProcessing " . $org['name'] . " [" . $org['id'] . "]...");

        $newCategories = OsOrg::updateCategories($org['id'], $org['name'], $this->exactNameOverride);
        
        foreach ($newCategories as $categoryId)
        {
          $this->printDebug("+ added industry category: " . $this->getCategoryName($categoryId) . " [" . $categoryId . "]");
        }
                        
        $count++;
      }
    }


    print("\nMatched " . $count . " entities with OpenSecrets industry codes in " . (microtime(true) - $time) . " s\n");

    
    //DONE
    LsCli::beep();
  }
    
  protected function init($arguments, $options)
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    sfContext::createInstance($configuration, 'default');
    $this->db = LsDb::getDbConnection();
    $this->rawDb = LsDb::getDbConnection('raw');

    /*
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);
    $this->db = Doctrine_Manager::connection();
    $rawDb = $databaseManager->getDatabase('raw');
    $this->rawDb = Doctrine_Manager::connection($rawDb->getParameter('dsn'));  
    */

    $this->types = explode(',', $options['types']);
    $this->debugMode = $options['debug_mode'];
    $this->limit = $options['limit'];
    if ($options['expiration_date'])
    {
      $this->expirationDate = date('Y-m-d H:i:s', strtotime($options['expiration_date']));
    }
    $this->exactNameOverride = $options['exact_name_override'];

    //create insert statement
    $valStr = str_repeat('?, ', 5);
    $valStr = substr($valStr, 0, -2);
    $insertSql = 'INSERT INTO os_entity_category (entity_id, category_id, source, created_at, updated_at) VALUES (' . $valStr . ')';
    $this->insertStmt = $this->db->prepare($insertSql);

    //create lookup statement
    $selectSql = "SELECT category_name FROM os_category WHERE category_id = ?";
    $this->selectStmt = $this->db->prepare($selectSql);
  }
    
  protected function insertData(Array $data)
  {
    $data = array_merge($data, array(date('Y-m-d H:i:s'), date('Y-m-d H:i:s')));

    if (!$this->insertStmt->execute($data))
    {
      throw new Exception("Couldn't insert data: (" . implode(',', $data) . ")");
    }
    
    $rowCount = $this->insertStmt->rowCount();
    
    if ($rowCount != 1)
    {
      throw new Exception("Data insert affected " . $rowCount . " rows (" . implode(',', $data) . ")");
    }
  }

  protected function getCategoryName($categoryId)
  {
    $this->selectStmt->execute(array($categoryId));
    return $this->selectStmt->fetchColumn();
  }
  
  protected function getUnmatchedPersons()
  {
    $sql = "SELECT DISTINCT(e.id), e.name FROM entity e " . 
           "LEFT JOIN os_entity_transaction et ON (e.id = et.entity_id) " .
           "LEFT JOIN os_entity_category ec ON (e.id = ec.entity_id" . 
           ($this->expirationDate ? " AND ec.updated_at > ?" : "") .
           ") " .
           "WHERE et.id IS NOT NULL AND et.is_verified = 1 " .
           "AND ec.id IS NULL AND e.primary_ext = ? AND e.is_deleted = 0 " .
           "LIMIT " . $this->limit;
    $params = $this->expirationDate ? array($this->expirationDate, 'Person') : array('Person');
    $stmt = $this->db->execute($sql, $params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);  
  }  

  protected function getUnmatchedOrgs()
  {
    $sql = "SELECT DISTINCT(e.id), e.name FROM entity e " . 
           "LEFT JOIN os_entity_category ec ON (e.id = ec.entity_id" . 
           ($this->expirationDate ? " AND ec.updated_at > ?" : "") .
           ") " .
           "WHERE ec.id IS NULL AND e.primary_ext = ? AND e.is_deleted = 0 " .
           "LIMIT " . $this->limit;
    $params = $this->expirationDate ? array($this->expirationDate, 'Org') : array('Org');
    $stmt = $this->db->execute($sql, $params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);  
  }

  static function logUpdate($id)
  {
    $db = LsDb::getDbConnection();
    $now = LsDate::getCurrentDateTime();
    $sql = "INSERT INTO task_meta (task, namespace, predicate, value, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?) " .
           "ON DUPLICATE KEY UPDATE value = ?";
    $params = array(__CLASS__, $id, self::$logKey, $now, $now, $now, $now);

    return $stmt = $db->execute($sql, $params);
  }
  
  protected function isMatched($id)
  {
    $db = LsDb::getDbConnection();
    $sql = "SELECT * FROM task_meta WHERE task = ? AND namespace = ? AND predicate = ?";
    $params = array(get_class($this), $id, self::$logKey);
    
    if ($this->expirationDate)
    {
      $sql .= " AND value < ?";
      $params[] = $this->expirationDate;
    }
    
    $stmt = $db->execute($sql, $params);

    return count($stmt->fetchAll()) ? true : false;
  }
  
  protected function isSkippableEntity($id)
  {
    $db = LsDb::getDbConnection();
    $sql = "SELECT definition_id FROM extension_record WHERE entity_id = ?";
    $stmt = $db->execute($sql, array($id));
    $extensionIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $typesToSkip = array(6, 11, 18, 19, 20, 26);

    return count(array_intersect($extensionIds, $typesToSkip)) > 0;
  }
}