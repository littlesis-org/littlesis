<?php

class OsImportCommitteesTask extends sfBaseTask
{
  protected $db = null;
  protected $insertStmt = null;
  protected $selectStmt = null;

  protected function configure()
  {
    $this->namespace        = 'opensecrets';
    $this->name             = 'import-committees';
    $this->briefDescription = 'Imports committees from specified OpenSecrets cycle';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Maximum number of OpenSecrets rows to process', 1000);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('cycles', null, sfCommandOption::PARAMETER_REQUIRED, 'Funding cycle to import', '2010,2008,2006,2004,2002,2000,1998,1996,1994,1992,1990');
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
    
    $count = 0;

    foreach ($this->cycles as $cycle)
    {
      if ($options['debug_mode'])
      {
        print("Processing OpenSecrets committees from " . $cycle . " cycle...\n");
      }

      //open file
      $file = fopen(sfConfig::get('sf_root_dir') . '/data/opensecrets/cmtes' . substr($cycle, -2) . '.txt', 'r');
            
      //process rows
      while ($data = fgetcsv($file, 1000, ",", "|"))
      {
        if ($count >= $this->limit)
        {
          if ($options['debug_mode'])
          {
            print("*** Import limit reached; exiting...\n");
          }        

          break;
        }

        if ($this->isCommitteeDuplicate($data[0], $data[1]))
        {
          if ($options['debug_mode'])
          {
            print("- Committee " . $data[1] . " already exists for " . $data[0] . " cycle; skipping...\n");
          }
          
          continue;
        }

        if (count($data) != 14)
        {
          $data = array_slice($data, 0, 3);
          for ($n = 0; $n < 11; $n++)
          {
            $data[] = null;
          }
        }
  
        $this->insertData($data);
        
        $count++;
        
        if ($options['debug_mode'])
        {
          print("Processed OpenSecrets " . $cycle . " committee record " . $data[1] . "\n");
          flush();
        }
      }
    }


    $duration = microtime(true) - $time;
    print($count . " OpenSecrets committee records processed in " . $duration . " s\n");

    
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

    $this->cycles = explode(',', $options['cycles']);
    $this->limit = $options['limit'];

    //create insert statement
    $valStr = str_repeat('?, ', 14);
    $valStr = substr($valStr, 0, -2);
    $insertSql = 'INSERT INTO os_committee VALUES (' . $valStr . ')';
    $this->insertStmt = $this->db->prepare($insertSql);

    //create select statement
    $selectSql = 'SELECT COUNT(*) FROM os_committee WHERE cycle = ? AND committee_id = ?';
    $this->selectStmt = $this->db->prepare($selectSql);
  }
  
  
  protected function exec($sql, $params=array(), $stmt=null)
  {
    if (!$stmt)
    {
      $stmt = $this->db->prepare($sql);
    }
    
    $stmt->execute($params);
    
    return $stmt;

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


  protected function isCommitteeDuplicate($cycle, $committeeId)
  {
    $this->selectStmt->execute(array($cycle, $committeeId));
    
    return ($this->selectStmt->fetch(PDO::FETCH_COLUMN) > 0);
  }
  
  
  protected function emptyToNull($value)
  {
    return ($value === '') ? null : $value;
  }  
}