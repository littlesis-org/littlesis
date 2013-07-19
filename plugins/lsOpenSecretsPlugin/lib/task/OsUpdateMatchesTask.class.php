<?php

require_once(sfConfig::get('sf_root_dir') . '/lib/task/LsTask.class.php');

class OsUpdateMatchesTask extends LsTask
{
  protected
    $db = null,
    $rawDb = null,
    $browser = null,
    $debugMode = null,
    $startTime = null,
    $databaseManager = null,
    $cutoffDate = null,
    $donationMatches = null,
    $donationNonmatches = null;


  protected function configure()
  {
    $this->namespace        = 'opensecrets';
    $this->name             = 'update-matches';
    $this->briefDescription = 'Automates donation matching process for entities that have been donor-matched in the past, but for which the preprocess script has found additional matches';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of matches to process', 100);
    $this->addOption('cutoff_date', null, sfCommandOption::PARAMETER_REQUIRED, 'individuals matched after this date by a user wont be rematched', '2013-04-15');
    $this->addOption('start_id', null, sfCommandOption::PARAMETER_REQUIRED, 'entity id to start with', 1);
    $this->addOption('session', null, sfCommandOption::PARAMETER_REQUIRED, 'name of this session for scraper meta', null);
  }
  
  
  protected function execute($arguments = array(), $options = array())
  {
    if (!$this->safeToRun())
    {
      print("Script already running!\n");
      die;
    }
    
    $this->init($arguments, $options);
    $session = $options['session'];
    $start_id = $options['start_id'];
    
    if ($session)
    {
      $sql = 'SELECT * FROM scraper_meta WHERE scraper = ? and namespace = ? and predicate = ?';
      $stmt = $this->db->execute($sql, array('OsUpdate',$session,'last_scraped'));
      $metas = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if(count($metas))
      {
        $start_id = $metas[0]['value'];
      }
      else 
      {
        $meta = new ScraperMeta;
        $meta->scraper = 'OsUpdate';
        $meta->namespace = $session;
        $meta->predicate = 'last_scraped';
        $meta->value = $start_id-1;
        $meta->save();
      }
      
    }
    
    
    $entity_ids = $this->getEntities($options['limit'],$start_id);

    foreach($entity_ids as $entity_id)
    {
      //$this->printDebug("*******************************");
      //get person record
      $sql = 'SELECT * FROM person WHERE entity_id = ?';
      $stmt = $this->db->execute($sql, array($entity_id));
  
      if (!$donorPerson = $stmt->fetch(PDO::FETCH_ASSOC))
      {
        if ($this->debugMode)
        {
          print("* Can't find Person record for donor with entity_id " . $id . "; skipping...");
        }
        
        return;
      }
      
      $this->printDebug(PersonTable::getLegalName($donorPerson));
      
      $trans = $this->getTransactions($entity_id);
      $verified_donations = $this->getDonations($trans);
      $out=array();
      foreach ($verified_donations as $key => $subarr) 
      {
        foreach ($subarr as $subkey => $subvalue) 
        {
          $out[$subkey][$key] = $subvalue;
        }
      }
      $verified_fields = array_map(array_unique,$out);

      $trans = $this->getTransactions($entity_id,0,1);   
      $fields_to_check = array('donor_name','street','city','state','zip','employer_raw','org_raw','title_raw','gender','suffix');
      foreach($fields_to_check as $f)
      {
        $this->printDebug($f . ": " . implode(",",$verified_fields[$f])); 
      }
      
      
      $unverified_donations = $this->getDonations($trans);
      $this->donationMatches = array();
      $this->donationNonmatches = array();
      foreach($unverified_donations as $ud)
      {
        if ($this->namesAreCompatible($ud,$donorPerson))
        {
          $mat = $this->checkForMatch($ud,$verified_donations,$fields_to_check,$verified_fields);
          $ud['reason'] = $mat[1];
          if ($mat[0] == 1)
          {
            $this->donationMatches[] = $ud;
          }
          else
          {
            $this->donationNonmatches[] = $ud;
          }
        }
        
      }

      $fields_to_check[] = 'reason';
      $this->printDebug("\nSUCCESSES");
      foreach($this->donationMatches as $dm)
      { 
        //mark donation matches as verified
        $sql = 'UPDATE os_entity_transaction SET is_verified = 1, is_synced = (is_verified = is_processed), reviewed_at = ?, reviewed_by_user_id = ? WHERE entity_id = ? AND cycle = ? AND transaction_id = ?';
        $stmt = $this->db->execute($sql, array(date('Y-m-d H:i:s'), 1, $entity_id,$dm['cycle'],$dm['row_id']));
        
        
        $str = '';
        foreach($fields_to_check as $f)
        {
          $str .= $f . ": " . $dm[$f] . "; " ;
        }
        $this->printDebug("\t" . $str);
      }
      $this->printDebug("\nFAILURES");
      foreach($this->donationNonmatches as $dm)
      {
        //mark donation non-matches as unverified
        $sql = 'UPDATE os_entity_transaction SET is_verified = 0, is_synced = (is_verified = is_processed), reviewed_at = ?, reviewed_by_user_id = ? WHERE entity_id = ? AND cycle = ? AND transaction_id = ?';
        $stmt = $this->db->execute($sql, array(date('Y-m-d H:i:s'), 1, $entity_id,$dm['cycle'],$dm['row_id']));
        
        $str = '';
        foreach($fields_to_check as $f)
        {
          $str .= $f . ": " . $dm[$f] . "; " ;
        }
        $this->printDebug("\t" . $str);
      }
      
      $sql = 'UPDATE scraper_meta SET value = ? WHERE scraper = ? and namespace = ? and predicate = ?';
      $stmt = $this->db->execute($sql, array($entity_id,'OsUpdate',$session,'last_scraped'));
      
      
      $this->printDebug("*******************************");
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
    $this->cutoffDate = $options['cutoffDate'];
    //this avoids a context error when clearing the cache
    sfContext::createInstance($configuration);

    $this->debugMode = $options['debug_mode'];
    $this->browser = new sfWebBrowser;
    $this->cutoffDate = $options['cutoff_date'];
  }
  
  protected function checkForMatch($ud, $verified_donations,$fields_to_check,$verified_fields)
  {
    
    $address_score = $this->isAddressCompatible($ud,$verified_donations);

    $employer_score = $this->isEmployerCompatible($ud,$verified_fields);

    $gender_score = $this->isGenderCompatible($ud,$verified_fields);
    $age_score = $this->isAgeCompatible($ud,$verified_fields);
    
    $uncommon_name = $this->isUncommonName($ud);
    
    $uncommon_zip = $this->isUncommonZip($ud);
    
    $scores = "\taddress score: $address_score; employer_score: $employer_score; uncommon name: $uncommon_name; uncommon zip: $uncommon_zip; age score: $age_score; gender score: $gender_score";
    $ret = array(0,0);
    if($gender_score == 0)
    {
      $ret = array(0,$scores);
    }
    else if($age_score == 0)
    { 
      $ret = array(0,$scores);
    }
    else if($employer_score > 1 && $address_score > 2)
    {
      $ret = array(1,$scores);
    }
    else if($address_score == 6 && $uncommon_zip > 0)
    {
      $ret = array(1,$scores);
    }
    else if($address_score > 2 && $uncommon_name > 9)
    {
      $ret = array(1,$scores);
    }
    else if($employer_score > 1 && $uncommon_name > 5)
    {
      $ret = array(1,$scores);
    }
    else if($address_score > 3 && $employer_score > 0)
    {
      $ret = array(1,$scores);
    }
    else if($address_score > 0 && $employer_score > 2 && $uncommon_name > 1)
    {
      $ret = array(1,$scores);
    }
    else 
    {
      $ret = array(0,$scores);
    }
    return $ret;
  }
  
  
  public function namesAreCompatible($donorPerson, $donation)
  {
    //try last names
    if (trim(strtolower($donorPerson['name_last'])) != trim(strtolower($donation['donor_name_last'])))
    {
      return false;
    }
    
    //if last names match, it's decided by middle names
    return PersonTable::middleNamesAreCompatible($donorPerson['name_middle'], $donation['donor_name_middle']);
  } 
  
  protected function isUncommonZip($ud)
  {
    $zip = strtolower(trim($ud['zip']));
    if(strlen($zip) > 0 && $zip != "null")
    {
      $sql = 'SELECT * FROM os_zip WHERE zip = ?';
      $stmt = $this->rawDb->execute($sql, array($ud['zip']));
      $zr = $stmt->fetch(PDO::FETCH_ASSOC);
      if($zr['ct'] > 5000) return 0;
      if($zr['ct'] > 1000) return 1;
      else return 2;
    }
    return 0;
  }
  
  protected function isUncommonName($ud)
  {
    $score = 0;
    
    $sql = 'SELECT * FROM os_donor_name_last WHERE donor_name_last = ?';
    $stmt = $this->rawDb->execute($sql, array($ud['donor_name_last']));
    $last_name = $stmt->fetch(PDO::FETCH_ASSOC);
    if($last_name['unique_first_name_ct'] < 5) $score+= 10;
    else if($last_name['unique_first_name_ct'] < 50) $score += 4;
    
    $sql = 'SELECT * FROM os_donor_name_first WHERE donor_name_first = ?';
    $stmt = $this->rawDb->execute($sql, array($ud['donor_name_first']));
    $first_name = $stmt->fetch(PDO::FETCH_ASSOC);
    if($first_name['unique_last_name_ct'] < 5) $score+= 10;
    else if($first_name['unique_last_name_ct'] < 50) $score += 4;
    
    if($last_name['unique_first_name_ct'] > 500) $score -= 6;
    if($first_name['unique_first_name_ct'] > 500) $score -= 4;
    
    return $score;
  }
  
  //checks to see if the employer field matches up with previous matches
  //strong match = 2; weak match = 1; no match = 0
  
  //ADD A title check?
  protected function isEmployerCompatible($donation, $verified_fields)
  {
    $score = 0;

    foreach($verified_fields as &$vf)
    {
      $vf = array_map(strtolower,$vf);
    }
    unset($vf);

    //checking both the (discontinued as of 2012) employer_raw and (current) org_raw and title_raw field
    
    $empl_fields = array('employer_raw','org_raw','title_raw');
    
    foreach($empl_fields as $ef)
    {
      $empl = trim(strtolower($donation[$ef]));
      if(strlen($empl) > 1)
      {                      
        if  (in_array(strtoupper($empl), PluginOsDonationTable::$commonOrgs) || in_array($empl,PluginOsDonationTable::$commonTitleWords) || in_array($empl,PluginOsDonationTable::$commonOrgWords))
        {
          $common = 1;
        }
        else $common = 0;
        
        foreach($empl_fields as $ef2)
        {
          if(in_array($empl,$verified_fields[$ef2]))
          {
            if($common && $score < 1) $score = 1;
            else if ($ef2 == 'title_raw')
            {
              if ($score < 2) $score = 2;
            }
            else return 4;
          }
                      
          else if (stristr($verified_fields[$ef2],$empl) || stristr($empl,$verified_fields[$ef2]))
          {
            if ($common) 
            {
              if ($score < 1) $score = 1;
            }
            else if ($score < 2) $score = 2;
          }

          
        }
        
        //build array of uncommon employer field words 
        //eg: "Goldman Sachs Group" -> array("Goldman","Sachs")
        $empl_words = preg_split("/\b/is",$empl);
        $unusual_words = array();
        $other_words = array();
        foreach($empl_words as $ew)
        {
          if(!in_array($ew,PluginOsDonationTable::$commonOrgWords) && !in_array($ew,PluginOsDonationTable::$commonTitleWords) && strlen($ew) > 2)
          {
            $unusual_words[] = $ew;
          }
          else if(strlen($ew) > 2) $other_words[] = $ew;
        }
        foreach($empl_fields as $ef2)
        {
          //if no exact match, check unusual words against previous matches
          $vf = implode(" ",$verified_fields[$ef2]);
          foreach($unusual_words as $uw)
          {
            $matched = preg_match("/\b$uw\b/is",$vf,$match);
            if ($matched)
            {
              //$this->printDebug("\tEMPLOYER MATCH FOUND THROUGH UNUSUAL WORD ($uw)\t" . $empl . "\t" . $vf);                  
              if ($common)
              {
                if($score < 1) $score = 1;
              }
              if($score < 3) $score = 3;
            }
          }
          if ($score < 1)
          {
            foreach($other_words as $ow)
            {
              $matched = preg_match("/\b$ow\b/is",$vf,$match);
              if ($matched)
              {
                //$this->printDebug("\tEMPLOYER MATCH FOUND THROUGH OTHER WORD ($uw)\t" . $empl . "\t" . $vf);       
                if($score < 1) $score = 1;
              }
            }
          }
        }
      }
    }
    
    return $score;
  }
   
  
  //checks to see if addresses are compatible
  //exact street address match = 6
  //near street address match = 5 (not used right now)
  //zip match = 4
  //city, state = 3
  //first 3 digits of zip = 2
  //state = 1
  protected function isAddressCompatible($donation,$verified_donations)
  {
    $score = 0;
    $street = strtolower(trim($donation['street']));
    $city = strtolower(trim($donation['city']));
    $zip = strtolower(trim($donation['zip']));
    $state = strtolower(trim($donation['state']));
    
    foreach($verified_donations as $vd)
    {
      $vd = array_map(trim,$vd);
      $vd = array_map(strtolower,$vd);
      if(strlen($street) > 3 && $street == $vd['street'] && $zip == $vd['zip'])
      {
        $score = 6;
        return $score;
      }
      else if(strlen($zip) > 3 && $zip == $vd['zip'])
      {
        if ($score < 4) $score = 4;
      }
      else if (strlen($state) > 0 && $state == $vd['state'])
      {
        if($city == $vd['city'])
        {
          if ($score < 3) $score = 3;
        }
        else if (substr($zip,0,3) == substr($vd['zip'],0,3))
        {
          if ($score < 2) $score = 2;
        }
        else if ($score < 1) $score = 1;
      }
      
    }
    return $score;
  }
  
  protected function isGenderCompatible($donation,$verified_fields)
  {
    if(in_array($donation['gender'],array('M','F')) && count(array_intersect(array('M','F'),$verified_fields['gender'])))
    {
      if(!in_array($donation['gender'],$verified_fields['gender'])) return 0;
      else return 1;
    }
    if(preg_match('/ mr?s$/is',$donation['donor_name'],$match))
    {
      if(in_array('M',$verified_fields['gender']) && !in_array('F',$verified_fields['gender']))
      {
        return 0;
      }
      $matched = 0;
      foreach($verified_fields['donor_name'] as $dn)
      {
        $matched = preg_match("/" . $match . "$/is",$dn,$matches);
        if($matched) return 1;
      }
      return 0;
    }
    return 1;
  }
  
  protected function isAgeCompatible($donation,$verified_fields)
  {
    $suffixes = array('II','III','IV','VI','V','SR','JR');
    if(!$donation['donor_name_suffix'])
    {
      if(preg_match('/ (' . implode('|',$suffixes) . ')$/is',$donation['donor_name'], $match))
      {
        $donation['donor_name_suffix'] = $match[1];
      }
    }
    //check to see if the current record has a suffix
    if(in_array($donation['donor_name_suffix'],$suffixes))
    {
      //if current record suffix matches previously matched suffixes, return 2 'Jr' / array('Jr','II')
      if(in_array($donation['donor_name_suffix'], $verified_fields['donor_name_suffix']))
      {
        return 2;
      }
      //if current record suffix does not match previously matched suffixes (and there are previously matched suffixes) return 0
      //'Jr',array('Sr')
      else if (count(array_intersect($suffixes,$verified_fields['donor_name_suffix'])))
      {
        return 0;
      }
      //else return 1 for uncertainty
      //'Jr',array(null)
      else return 1;
    }
    //if the current record has no suffix, checks to make sure previously verified donations do not always have a suffix; if they do, return 0; if they often have a suffix, but not always, return a 1
    else if(count(array_intersect($verified_fields['donor_name_suffix'],$suffixes)))
    { 
      //null,array('Jr')
      if(!in_array($donation['donor_name_suffix'],$verified_fields['donor_name_suffix']))
      {
        return 0;
      }
      //null,array('Jr',null)
      else return 1;
    }
    return 2;
  }  

  
//1. loop through entities that have already been matched by a human user prior to the last data import


  protected function getEntities($limit,$start_id)
  {
    //get entities first so limit can apply to number of entities
    $sql = 'SELECT DISTINCT et.entity_id FROM os_entity_transaction et ' . 
           'LEFT JOIN entity e ON (e.id = et.entity_id) ' . 
           'LEFT JOIN os_entity_preprocess ep ON (ep.entity_id = e.id) ' .
           'WHERE et.is_verified = 1 AND e.is_deleted = 0 AND et.reviewed_by_user_id <> 1 AND ((et.reviewed_at < ep.processed_at AND ep.updated_at is null) OR (et.reviewed_at < ep.updated_at)) AND e.id >= ? ' . 
           'LIMIT ' . $limit ;
    $stmt = $this->db->execute($sql,array($start_id));
    $entityIds = $stmt->fetchAll(PDO::FETCH_COLUMN);  
    return $entityIds;
  
  }
  
  protected function getTransactions($entity_id, $is_verified=1,$reviewed_at_null=0)
  {  
    $sql = 'SELECT * FROM os_entity_transaction WHERE entity_id = ? and is_verified=' . $is_verified;
    if ($reviewed_at_null)
    {
      $sql.=" AND reviewed_at is NULL";
    }
    $stmt = $this->db->execute($sql, array($entity_id));
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $trans = array();
    foreach($matches as $match)
    {
      $trans[] = $match['cycle'] . ':' . $match['transaction_id'];
    }
    return $trans;
  }    
  
  protected function getDonations(Array $trans)
  {
    if (!count($trans))
    {
      return array();
    }

    $sql = 'SELECT * FROM os_donation FORCE INDEX(PRIMARY) WHERE (' . OsDonationTable::generateKeyClause('cycle', 'row_id', $trans) . ') AND recipient_id IS NOT NULL AND transaction_type <> ?';

    if (!$stmt = $this->rawDb->execute($sql, array('22y')))
    {
      throw new Exception("Couldn't get donations for " . implode(', ', $trans));
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
    
  protected function safeToRun()
  {
    $proc_id = getmypid();
    exec('ps aux -ww | grep symfony | grep :process-matches | grep -v ' . $proc_id . ' | grep -v grep', $status_arr);

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
  

  /*protected function findCommonWordsEmpl()
  {
    $words = array();
    $i = 0;
    while($i < 10)
    {
      $sql = 'SELECT title_raw from os_donation where cycle = ? limit 100000';
      $stmt = $this->rawDb->execute($sql,array('2012'));
      $arr = $stmt->fetchAll(PDO::FETCH_COLUMN);
      echo 'ok';
      foreach($arr as $a)
      {
        $str_arr = preg_split("/\b/",$a);
        foreach($str_arr as $str)
        {
          if(isset($words[$str])) $words[$str] ++;
          else $words[$str] = 1;
        }
      }
      $i++;
    }
    asort($words);
    var_dump($words);
    arsort($words);
    $j = 0;
    foreach($words as $wk => $wv)
    {
      if($j > 300) break;
      echo '"' . strtolower($wk) . '",';
      $j++;
    }
    

  }*/
}
