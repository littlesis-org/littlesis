<?php

class OsImportLobbyingTask extends sfBaseTask
{
  protected $db = null;
  protected $insertStmt = null;
  protected $selectStmt = null;

  protected function configure()
  {
    $this->namespace        = 'opensecrets';
    $this->name             = 'import-lobbying';
    $this->briefDescription = 'Imports all OpenSecrets lobbying records';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Maximum number of OpenSecrets lobbying records to process', 1000);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
  }

  protected function execute($arguments = array(), $options = array())
  {
    //set start time
    $time = microtime(true);

    //connect to raw database
    $this->init($arguments, $options);
    
    $count = 0;

    if ($options['debug_mode'])
    {
      print("Processing OpenSecrets lobbying records...\n");
    }

    //open file
    $file = fopen(sfConfig::get('sf_root_dir') . '/data/opensecrets/lob_lobbying.txt', 'r');
            
    //process rows
    while ($data = fgetcsv($file, 1000, ',', '|'))
    {
      if ($this->isLobbyingDuplicate($data[0]))
      {
        if ($options['debug_mode'])
        {
          print("- Lobbying record " . $data[0] . " already exists; skipping...\n");
        }
        
        continue;
      }

      $this->insertData($data);
      
      $count++;
      
      if ($options['debug_mode'])
      {
        print("Processed OpenSecrets lobbying record " . $data[0] . "\n");
        flush();
      }
    }


    $duration = microtime(true) - $time;
    print($count . " OpenSecrets lobbying records processed in " . $duration . " s\n");

    
    //DONE
    LsCli::beep();
  }
  
  
  protected function init($arguments, $options)
  {
    //connect to DB
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      
    $rawDb = $databaseManager->getDatabase('raw');
    $this->db = Doctrine_Manager::connection($rawDb->getParameter('dsn'));  

    //create insert statement
    $valStr = str_repeat('?, ', 19);
    $valStr = substr($valStr, 0, -2);
    $insertSql = 'INSERT INTO os_lobbying VALUES (' . $valStr . ')';
    $this->insertStmt = $this->db->prepare($insertSql);

    //create select statement
    $selectSql = 'SELECT COUNT(*) FROM os_lobbying WHERE uniq_id = ?';
    $this->selectStmt = $this->db->prepare($selectSql);
  }
  
  
  protected function insertData(Array $data)
  {
    $data = array_map(array($this, 'emptyToNull'), $data);

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
  
  
  protected function isLobbyingDuplicate($uniqId)
  {
    $this->selectStmt->execute(array($uniqId));
    
    return ($this->selectStmt->fetch(PDO::FETCH_COLUMN) > 0);
  }  
  
  
  protected function emptyToNull($value)
  {
    return ($value === '') ? null : $value;
  }  
}