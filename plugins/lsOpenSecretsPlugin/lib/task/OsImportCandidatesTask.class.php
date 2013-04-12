<?php

class OsImportCandidatesTask extends sfBaseTask
{
  protected $db = null;
  protected $insertStmt = null;
  protected $selectStmt = null;

  protected function configure()
  {
    $this->namespace        = 'opensecrets';
    $this->name             = 'import-candidates';
    $this->briefDescription = 'Imports candidates from specified OpenSecrets cycle';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Maximum number of OpenSecrets rows to process', 1000);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('cycles', null, sfCommandOption::PARAMETER_REQUIRED, 'Funding cycles to import', '2012,2010,2008,2006,2004,2002,2000,1998,1996,1994,1992,1990');
  }

  protected function execute($arguments = array(), $options = array())
  {
    //set start time
    $time = microtime(true);

    //connect to raw database
    $this->init($arguments, $options);
    
    $count = 0;

    foreach ($this->cycles as $cycle)
    {
      if ($options['debug_mode'])
      {
        print("Processing OpenSecrets candidates from " . $cycle . " cycle...\n");
      }

      //open file
      $file = fopen(sfConfig::get('sf_root_dir') . '/data/opensecrets/cands' . substr($cycle, -2) . '.txt', 'r');

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

        if ($this->isCandidateDuplicate($data[1], $data[2], $data[0]))
        {
          if ($options['debug_mode'])
          {
            print("- Candidate " . $data[2] . " already exists; skipping...\n");
          }
          
          continue;
        }
  
        if (count($data) != 12)
        {
          if ($options['debug_mode'])
          {
            print("* Candidate " . $data[2] . " has " . count($data) . " fields; adjusting...\n");
          }

          $data = array_slice($data, 0, 2);
          for ($n = 0; $n < 10; $n++)
          {
            $data[] = null;
          }
        }
  
        $data = $this->parseCandidateName($data);
        $this->insertData($data);
        
        $count++;
        
        if ($options['debug_mode'])
        {
          print("Processed OpenSecrets " . $cycle . " candidate record " . $data[2] . "\n");
          flush();
        }
      }
    }


    $duration = microtime(true) - $time;
    print($count . " OpenSecrets candidate records processed in " . $duration . " s\n");

    
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
    $this->limit = intval($options['limit']);

    //create insert statement
    $valStr = str_repeat('?, ', 17);
    $valStr = substr($valStr, 0, -2);
    $insertSql = 'INSERT INTO os_candidate VALUES (' . $valStr . ')';
    $this->insertStmt = $this->db->prepare($insertSql);

    //create select statement
    $selectSql = 'SELECT COUNT(*) FROM os_candidate WHERE fec_id = ? AND candidate_id = ? AND cycle = ?';
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
  
  
  protected function isCandidateDuplicate($fecId, $candidateId, $cycle)
  {
    $this->selectStmt->execute(array($fecId, $candidateId, $cycle));

    return ($this->selectStmt->fetch(PDO::FETCH_COLUMN) > 0);
  }
  
  
  protected function parseCandidateName(Array $data)
  {
    $validSuffixes = array('sr', 'jr', 'ii', 'iii', 'iv', 'v', 'vi');
    $junkSuffixes = array('mr', 'mrs', 'ms', 'phd', 'jd', 'md', 'hon', 'dr');
    $lastParts = array('von', 'van', 'di', 'la', 'de', 'san');

    $first = null;
    $last = null;
    $middle = null;
    $suffix = null;
    $nick = null;

    $name = $data[3];
    
    //remove party, eg '(D)'
    $name = preg_replace('# \([^\)]+\)$#', '', $name);

    //separate nick
    if (preg_match('#(\(|\')(.+)(\)|\')#', $name, $match))
    {
      $name = preg_replace('#(\(|\')(.+)(\)|\')#', '', $name);
      $nick = $match[2];    
    }
    
    //remove commas
    $name = str_replace(',', '', $name);

    $parts = preg_split('#\s+#', $name);

    //remove junk suffixes
    foreach ($parts as $key => $value)
    {
      if (in_array(strtolower($value), $junkSuffixes))
      {
        unset($parts[$key]);
      }
      elseif (in_array(strtolower($value), $validSuffixes))
      {
        $suffix = $value;
        unset($parts[$key]);
      }
    }
    
    $parts = array_merge($parts);
    
    switch (count($parts))
    {
      case 2:
        $first = $parts[0];
        $last = $parts[1];
        break;
      case 3:
        $first = $parts[0];
        if (in_array(strtolower($parts[2]), $validSuffixes))
        {
          $suffix = $parts[2];
          $last = $parts[1];
        }
        else
        {
          $middle = $parts[1];
          $last = $parts[2];
        }
        break;
      case 4:
        $first = $parts[0];
        if (in_array(strtolower($parts[3]), $validSuffixes))
        {
          $suffix = $parts[3];
          $last = $parts[2];
          $middle = $parts[1];
        }
        elseif (in_array(strtolower($parts[2]), $lastParts))
        {
          $last = $parts[2] . ' ' . $parts[3];
          $middle = $parts[1];
        }
        else
        {
          $last = $parts[3];
          $middle = $parts[1] . ' ' . $parts[2];
        }
        break;
    }

    $middle = str_replace('.', '', $middle);
    
    return array_merge($data, array($last, $first, $middle, $suffix, $nick));
  }
  
  
  protected function emptyToNull($value)
  {
    return ($value === '') ? null : $value;
  }  
}