<?php

class OsImportDonationsTask extends sfBaseTask
{
  protected $db = null;
  protected $insertStmt = null;


  protected function configure()
  {
    $this->namespace        = 'opensecrets';
    $this->name             = 'import-donations';
    $this->briefDescription = 'Imports donations from specified OpenSecrets cycle';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Maximum number of OpenSecrets rows to process', 1000);
    $this->addOption('offset', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of lines of input data to skip before processing', 0);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('cycle', null, sfCommandOption::PARAMETER_REQUIRED, 'Funding cycle to import', '2010');
    $this->addOption('check_dups', null, sfCommandOption::PARAMETER_REQUIRED, 'Check for duplicates before inserting new donations', false);
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
        
    //open file
    $file = fopen(sfConfig::get('sf_root_dir') . '/data/opensecrets/indivs' . substr($options['cycle'], -2) . '.txt', 'r');
    
    //set counters
    $line = 0;
    $this->added = 0;        
    $offset = $options['offset'];
    $limit = $offset + $options['limit'];

/*    
    //determine how many rows already processed
    $sql = 'SELECT COUNT(*) FROM os_donation WHERE cycle = ?';
    $stmt = $this->db->execute($sql, array($options['cycle']));
    $count = $stmt->fetch(PDO::FETCH_COLUMN);
*/

    //skip to offset line
    while ($line < $offset)
    {
      $data = fgetcsv($file, 1000);
      $line++;
    }

    //process remaining rows
    while ($line < $limit && $data = fgetcsv($file, 1000, ",", "|"))
    {
      $line++;

      if ($this->checkDups)
      {
        if ($this->isDonationDuplicate($data[0], $data[1]))
        {
          if ($options['debug_mode'])
          {
            print("[line " . $line . "]  " . $data[0] . " donation record " . $data[1] . " already exists! Skipping...\n");
          }
  
          continue;
        }
        else
        {
          if ($options['debug_mode'])
          {
            print("[line " . $line . "]  " . $data[0] . " donation record " . $data[1] . " is NEW! Adding...\n");
          }
  
          $this->added++;
        }
      }

      if (count($data) != 24)
      {
        $data = array_slice($data, 0, 2);
        for ($n = 0; $n < 22; $n++)
        {
          $data[] = null;
        }
      }

      //convert date format
      if ($data[8])
      {
        $data[8] = date('Y-m-d', strtotime($data[8]));
      }
      
      $data = array_merge($data, self::parseDonorName($data));
      $this->insertData($data);
      
      if ($options['debug_mode'])
      {
        print("Processed OpenSecrets " . $options['cycle'] . " donation record " . $data[1] . "\n");
        flush();
      }
    }


    $duration = microtime(true) - $time;
    print($this->added . " total OpenSecrets " . $options['cycle'] . " donation records added in " . $duration . " s\n");

    
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

    $this->checkDups = (bool) $options['check_dups'];

    //create insert statement
    $valStr = str_repeat('?, ', 29);
    $valStr = substr($valStr, 0, -2);
    $insertSql = 'INSERT INTO os_donation VALUES (' . $valStr . ')';
    $this->insertStmt = $this->db->prepare($insertSql);
    $insertIgnoreSql = 'INSERT IGNORE INTO os_donation VALUES (' . $valStr . ')';
    $this->insertIgnoreStmt = $this->db->prepare($insertIgnoreSql);

    $selectSql = 'SELECT cycle, row_id FROM os_donation WHERE cycle = ? AND row_id = ?';
    $this->selectStmt = $this->db->prepare($selectSql);
  }
  

  protected function isDonationDuplicate($cycle, $rowId)
  {
    $this->selectStmt->execute(array($cycle, $rowId));
    
    return ($this->selectStmt->fetch(PDO::FETCH_COLUMN) > 0);
  }

  
  protected function insertData(Array $data)
  {
    $data = array_map(array($this, 'emptyToNull'), $data);

    if ($this->checkDups)
    {
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
    else
    {
      $this->insertIgnoreStmt->execute($data);

      $rowCount = $this->insertIgnoreStmt->rowCount();

      if ($rowCount == 1)
      {
        $this->added++;
      }
    }
  }
  
  
  static function parseDonorName(Array $data)
  {
    $junk = $strip = array('MR', 'MRS', 'MS', 'PHD', 'JD', 'MD', 'HON', 'DR', 'HONORABLE', 'PASTOR', 'MPH', 'ESQ', 'ESQUIRE', 'FACHE', 'CHE', 'RN', 'MPA', 'DDS', 'ESQQ');
    $suffixes = array('JR', 'II', 'SR', 'III', 'IV', 'VI');
    $last = $first = $suffix = $middle = $nick = null;

    if ($name = strtoupper($data[3]))
    {
      $parts = explode(',', $name);

      if (count($parts) == 1)
      {
        $name = PersonTable::parseFlatName($parts[0], null, $returnArray=true);        
        $last = $name['name_last'];
        $first = $name['name_first'];
        $middle = $name['name_middle'];
        $nick = $name['name_nick'];
        $suffix = $name['name_suffix'];        
      }
      elseif (count($parts) == 2)
      {
        $last = trim($parts[0]);
        $first = trim($parts[1]);
        $suffix = null;
      }
      elseif (count($parts) == 3)
      {
        $last = trim($parts[0]);
        $first = trim($parts[2]);
        $suffix = trim($parts[1]);

        //if first is empty, the suffix is the real first
        if (!$first)
        {
          $first = $suffix;
          $suffix = null;
        }

        //if suffix is longer than accepted suffixes, or first is a suffix, switch the suffix and first
        if (strlen($suffix) > 4 || in_array($first, $suffixes))
        {
          $tmpFirst = $first;
          $tmpSuffix = $suffix;
          $first = $tmpSuffix;
          $suffix = $tmpFirst;
        }
      }
        
      //if first begins with MRS, remove it
      if (strtoupper(substr($first, 0, 4)) == 'MRS ')
      {
        $first = trim(substr($first, 4));
      }

      //grab nickname from parentheses
      if (preg_match('/\(([^\)]+)\)/', $first, $nickFound))
      {
        $nick = trim(str_replace("'", '', $nickFound[1]));
        $first = trim(preg_replace('/\([^\)]+\)/', '', $first));
        $first = preg_replace('/\s{2,}/', ' ', $first);
      }

      //if suffix isn't acceptable, unset it
      if (!in_array($suffix, $suffixes))
      {
        $suffix = null;
      }
        
      //split first into parts      
      $firstParts = preg_split('/\s+/', $first);

      if (count($firstParts) == 2)
      {
        $first = $firstParts[0];
        $other = $firstParts[1];

        //if no existing suffix and other is a suffix, set it
        if (!$suffix && in_array($other, $suffixes))
        {
          $suffix = $other;
        }
        
        //if other isn't a suffix or junk, it's a middle
        if (!in_array($other, array_merge($junk, $suffixes)))
        {
          $middle = $other;
        }
      }
      elseif (count($firstParts) > 2)
      {
        $first = $firstParts[0];
        $other1 = $firstParts[1];
        $other2 = $firstParts[2];

        //if no existing suffix and other1 is a suffix, set it
        if (!$suffix && in_array($other1, $suffixes))
        {
          $suffix = $other1;
        }

        //if other1 not junk or as suffix, it's a middle
        if (!in_array($other1, array_merge($junk, $suffixes)))
        {
          $middle = $other1;
        }
        
        //if no existing suffix and other2 is a suffix, set it
        if (!$suffix && in_array($other2, $suffixes))
        {
          $suffix = $other2;
        }        
      }
    }
    
    return array($last, $first, $middle, $suffix, $nick);
  }
  
  
  protected function emptyToNull($value)
  {
    return ($value === '') ? null : $value;
  }  
}