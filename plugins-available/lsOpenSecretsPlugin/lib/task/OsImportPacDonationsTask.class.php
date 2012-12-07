<?php

class OsImportPacDonationsTask extends sfBaseTask
{
  protected $db = null;
  protected $insertStmt = null;


  protected function configure()
  {
    $this->namespace        = 'opensecrets';
    $this->name             = 'import-pac-donations';
    $this->briefDescription = 'Imports PAC donations to candidates from specified OpenSecrets cycle';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Maximum number of OpenSecrets rows to process', 1000);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('cycle', null, sfCommandOption::PARAMETER_REQUIRED, 'Funding cycle to import', '2010');
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      


    //set start time
    $time = microtime(true);


    //connect to raw database
    $this->init($arguments, $options);
    
    
    //determine how many rows alrady processed
    $sql = 'SELECT COUNT(*) FROM os_pac_donation WHERE cycle = ?';
    $stmt = $this->db->execute($sql, array($options['cycle']));
    $count = $stmt->fetch(PDO::FETCH_COLUMN);
    
    
    //open file
    $file = fopen(sfConfig::get('sf_root_dir') . '/data/opensecrets/pacs' . substr($options['cycle'], -2) . '.csv', 'r');
    
    
    //skip to last line in file
    $line = 0;
    while ($line < $count)
    {
      fgetcsv($file, 1000);
      $line++;
    }


    //process remaining rows
    $limit = $line + $options['limit'];
    while ($line < $limit && $data = fgetcsv($file, 1000))
    {
      if (count($data) != 10)
      {
        die('Unepexted number of columns on line ' . $line . ': ' . count($data));
      }

      //convert date format
      if ($data[5])
      {
        $data[5] = date('Y-m-d', strtotime($data[8]));
      }
      
      $this->insertData($data);
      $line++;
      
      if ($options['debug_mode'])
      {
        print("Processed OpenSecrets " . $options['cycle'] . " PAC donation record " . $data[1] . "\n");
        flush();
      }
    }


    $duration = microtime(true) - $time;
    print($limit . " total OpenSecrets " . $options['cycle'] . " PAC donation records processed in " . $duration . " s\n");

    
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
    $valStr = str_repeat('?, ', 10);
    $valStr = substr($valStr, 0, -2);
    $insertSql = 'INSERT INTO os_pac_donation VALUES (' . $valStr . ')';
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
      throw new Exception("Data insert affected " . $rowCount . " (" . implode(',', $data) . ")");
    }
  }
  
  
  protected function emptyToNull($value)
  {
    return ($value === '') ? null : $value;
  }  
}