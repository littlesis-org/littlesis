<?php

class OsImportLobbyingBillsTask extends sfBaseTask
{
  protected $db = null;
  protected $insertStmt = null;
  protected $selectStmt = null;

  protected function configure()
  {
    $this->namespace        = 'opensecrets';
    $this->name             = 'import-lobbying-bills';
    $this->briefDescription = 'Imports all OpenSecrets lobbying bill records';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Maximum number of OpenSecrets lobbying bill records to process', 1000);
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
      print("Processing OpenSecrets lobbying bill records...\n");
    }

    //open file
    $file = fopen(sfConfig::get('sf_root_dir') . '/data/opensecrets/lob_bills.txt', 'r');
            
    //process rows
    while ($data = fgetcsv($file, 1000, ',', '|'))
    {
      if (count($data) != 4) { var_dump($data); die; }

      $this->insertData($data);
      
      $count++;
      
      if ($options['debug_mode'])
      {
        print("Processed OpenSecrets lobbying bill record " . $data[0] . "\n");
        flush();
      }
    }


    $duration = microtime(true) - $time;
    print($count . " OpenSecrets lobbying bill records processed in " . $duration . " s\n");

    
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
    $valStr = str_repeat('?, ', 4);
    $valStr = substr($valStr, 0, -2);
    $insertSql = 'INSERT INTO os_lobbying_bill VALUES (\'\', ' . $valStr . ')';
    $this->insertStmt = $this->db->prepare($insertSql);
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
  
  
  protected function emptyToNull($value)
  {
    return ($value === '') ? null : $value;
  }  
}