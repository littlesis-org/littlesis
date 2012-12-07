<?php

class OsImportCategoriesTask extends sfBaseTask
{
  protected $db = null;
  protected $insertStmt = null;
  protected $selectStmt = null;

  protected function configure()
  {
    $this->namespace        = 'opensecrets';
    $this->name             = 'import-categories';
    $this->briefDescription = 'Imports all OpenSecrets industry categories from specified CSV file';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('source_file', null, sfCommandOption::PARAMETER_REQUIRED, 'CSV category file', null);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->init($arguments, $options);
    
    $count = 0;

    if ($options['debug_mode'])
    {
      print("Importing OpenSecrets industry categories...\n");
    }

    //open file
    $file = fopen($options['source_file'], 'r');

    //process rows
    while ($data = fgetcsv($file, 1000, "\t"))
    {      
      //skip header line
      if ($data[0] == 'Catcode') { continue; }

      if (count($data) < 5) 
      { 
        if ($options['debug_mode'])
        {
          print("Skipping line " . ($count + 1) . ": only " . count($data) . " fields...\n");
        }

        $count++;      
        continue; 
      }

      $data = array_slice($data, 0, 6);      
      array_splice($data, 4, 1);
      $data = array_map('trim', $data);

      $this->insertData($data);
      
      $count++;
      
      if ($options['debug_mode'])
      {
        print("Processed OpenSecrets industry category " . $data[0] . "\n");
        flush();
      }
    }


    print("Imported " . $count . " OpenSecrets industry categories\n");

    
    //DONE
    LsCli::beep();
  }
  
  
  protected function init($arguments, $options)
  {
    //connect to DB
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      
    $this->db = Doctrine_Manager::connection();  

    //create insert statement
    $valStr = str_repeat('?, ', 5);
    $valStr = substr($valStr, 0, -2);
    $insertSql = 'INSERT INTO os_category (category_id, category_name, industry_id, industry_name, sector_name) VALUES (' . $valStr . ')';
    $this->insertStmt = $this->db->prepare($insertSql);
  }
  
  
  protected function insertData(Array $data)
  {
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
}