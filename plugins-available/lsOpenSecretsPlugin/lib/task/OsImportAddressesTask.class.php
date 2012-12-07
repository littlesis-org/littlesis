<?php

class OsImportAddressesTask extends sfTask
{
  protected
    $db = null,
    $rawDb = null,
    $debugMode = null,
    $startTime = null,
    $entityId = null,
    $listId = null,
    $skipSearched = null,
    $ct = 0,
    $databaseManager = null,
    $after_cycle = null;


  protected function configure()
  {
    $this->namespace        = 'opensecrets';
    $this->name             = 'import-addresses';
    $this->briefDescription = 'Creates addresses based on an entity\'s matched OS donations';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('entity_ids', null, sfCommandOption::PARAMETER_REQUIRED, 'Entities to fetch addresses for', null);
    $this->addOption('list_id', null, sfCommandOption::PARAMETER_REQUIRED, 'List to fetch addresses for', null);
    $this->addOption('category_id', null, sfCommandOption::PARAMETER_REQUIRED, 'category of relationships', null);
    $this->addOption('order', null, sfCommandOption::PARAMETER_REQUIRED, 'order/place of given entities in relationships', 2);
    $this->addOption('is_current', null, sfCommandOption::PARAMETER_REQUIRED, 'is the relationship current', null);
    $this->addOption('last_searched_at', null, sfCommandOption::PARAMETER_REQUIRED, 'if the entitys address has been searched/imported after this date, the task will do nothing', '2009');
    $this->addOption('skip_searched', null, sfCommandOption::PARAMETER_REQUIRED, 'skip entities that have been searched, but where no matches have been found to import', 1);
    $this->addOption('force_all', null, sfCommandOption::PARAMETER_REQUIRED, 'match addresses for all entities regardless of whether they have been searched', 0);
    $this->addOption('continuous', null, sfCommandOption::PARAMETER_REQUIRED, 'run continuously through db?', 0);
    $this->addOption('session_name', null, sfCommandOption::PARAMETER_REQUIRED, 'name of this scraping session', null);
    $this->addOption('after_cycle', null, sfCommandOption::PARAMETER_REQUIRED, 'check records after this year', '2006');
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'limit of entities to process', 50);
  }


  protected function execute($arguments = array(), $options = array())
  {
    $this->init($arguments, $options);

    $proc_id = getmypid();
    exec('ps aux -ww | grep symfony | grep :' . $task . ' | grep -v ' . $proc_id . ' | grep -v grep', $status_arr);

    foreach($status_arr as $status)
    {
      //sometimes the shell startup command also appears, which is fine (script is still safe to run)
      if(preg_match('/sh\s+\-c/isu',$status) == 0)
      {
        $this->printDebug('script is already running');
        return false;
      }
    }
    
    $ids = array();

    if ($options['entity_ids'])
    {
      $ids = explode(",",$options['entity_ids']);
    }
    if ($options['list_id'])
    {
      $sql = 'SELECT DISTINCT entity_id FROM ls_list_entity WHERE list_id = ' . $options['list_id'] . ' AND is_deleted = 0';
      $se = 'entity_id';
    }
    // GET RELATED ENTITIES FOR ADDRESS SEARCHES
    else if ($options['category_id'] && count($ids))
    {
      if ($options['order'] == 1)
      {
        $se = 'entity2_id';
        $we = 'entity1_id';
      }
      else 
      {
        $se = 'entity1_id';
        $we = 'entity2_id';
        
      }
      $sql = 'SELECT DISTINCT ' . $se . ' FROM relationship WHERE category_id = ' . $options['category_id'] . ' and ' . $we . ' in (' . implode(',', $ids) . ') AND is_deleted = 0';
      if ($options['is_current'])
      {
        $sql .= ' AND (is_current = 1 OR is_current IS NULL) AND end_date IS NULL';
      }
      
    }
    if ($this->sessionName && isset($sql))
    {
      $last_processed_meta = LsDoctrineQuery::create()
        ->from('ScraperMeta')
        ->where('scraper = ? and namespace = ? and predicate = ?', array('OsImportAddresses', 'last_processed',$this->sessionName))
        ->fetchOne();

      if (!$last_processed_meta)
      {
        $last_processed_meta = new ScraperMeta;
        $last_processed_meta->scraper = 'OsImportAddresses';
        $last_processed_meta->namespace = 'last_processed';
        $last_processed_meta->predicate = $this->sessionName;
        $last_processed_meta->value = 0;
        $last_processed_meta->save();
      }
      $sql .= ' AND ' . $se . ' > ' . $last_processed_meta->value . ' ORDER BY ' . $se . ' asc LIMIT ' . $this->limit;
    }
    if (isset($sql)) 
    {
      $stmt = $this->db->execute($sql);
      $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    $this->printDebug(count($ids) . " entities submitted...");

    //import addresses
    foreach ($ids as $id)
    {
      $predicate = null;
      if ($this->skipSearched == false)
      {
        $predicate = 'matched'; 
      }
      if(!$this->forceAll && $this->hasMeta($id,$options['last_searched_at'],$predicate))
      {
        $this->printDebug('skipping entity ' . $id . ', searched/imported recently...');
        continue;
      }
      $this->importAddresses($id);
      if (isset($last_processed_meta))
      {
        $last_processed_meta->value = $id;
        $last_processed_meta->save();
      }
    }
    
    $this->printDebug("\nImport script executed in " . (microtime(true) - $this->startTime) . " s and " . $this->ct . " addresses geocoded");
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

    //this avoids a context error when clearing the cache
    sfContext::createInstance($configuration);

    $this->debugMode = $options['debug_mode'];
    $this->entityId = $options['entity_id'];
    $this->listId = $options['list_id'];
    $this->skipSearched = $options['skip_searched'];
    $this->forceAll = $options['force_all'];
    $this->sessionName = $options['session_name'];
    $this->continuous = $options['continuous'];
    $this->limit = $options['limit'];
    $this->after_cycle = $options['after_cycle'];
  }


  protected function printDebug($str, $override=false)
  {
    if ($this->debugMode || $override)
    {
      echo $str . "\n";
    }    
  }
  
  
  public function importAddresses($id)
  {
    $entity = Doctrine::getTable('Entity')->find($id);
    if (!$entity)
    {
      return false;
    }
    $this->printDebug("\nImporting addresses for entity " . $id . ": " . $entity->name);

    $addresses = array();

    //get all the transactions
    $sql = 'SELECT cycle, transaction_id FROM os_entity_transaction WHERE entity_id = ? AND is_verified = 1 AND cycle > ?';
    $stmt = $this->db->execute($sql, array($id, $this->after_cycle));
    $trans = $stmt->fetchAll(PDO::FETCH_ASSOC); 

    $this->printDebug("Found " . count($trans) . " transactions...");
    $unique_address_parts = array();
    $unique_addresses = array();
    foreach ($trans as $tran)
    {
      $sql = 'SELECT street, city, state, zip, fec_id, date FROM os_donation WHERE cycle = ? AND row_id = ? ' . 
             'AND street IS NOT NULL AND zip IS NOT NULL AND state IS NOT NULL GROUP BY street, city, state, zip';
      $stmt = $this->rawDb->execute($sql, array($tran['cycle'], $tran['transaction_id']));

      if (!$address = $stmt->fetch(PDO::FETCH_ASSOC))
      {
        $this->printDebug("Couldn't find complete address for donation " . $tran['cycle'] . "-" . $tran['transaction_id'] . "; skipping...");
        continue;
      }
      
      $addr2 = array_map(function($a) { return strtolower($a); }, array_slice($address,0,4));
      if (preg_match("/\d+ +(.*?) +/is",$addr2['street'],$str))
      {
        $addr2['street'] = $str[0];
      }
      if (!in_array($addr2,$unique_addresses2))
      { 
        $unique_addresses2[] = $addr2;
        $unique_addresses[] = $address;
      }
    }
  
   
    foreach ($unique_addresses as $address)
    {
      //get state id
      $sql = 'SELECT id FROM address_state WHERE abbreviation = ?';
      $stmt = $this->db->execute($sql, array($address['state']));

      if (!$stateId = $stmt->fetch(PDO::FETCH_COLUMN))
      {
        $this->printDebug("Couldn't parse address: " . $str . "; skipping...");      
        continue;
      }
      
      $str = $address['street'] . ', ' . $address['city'] . ', ' . $address['state'] . ' ' . $address['zip'];
      $a = AddressTable::parseV3($str);
      $this->ct ++;
      $a->entity_id = $id;
      
      //only save if zips match 
      if ($a->postal && trim($a->postal) != '' && $a->postal != $address['zip']) 
      {
        $this->printDebug("Zips don't match, " . $a->postal . " / " . $address['zip'] . ", $str ; skipping...");      
        continue;
      }
      
      //only save if longitude and latitude and street and state are set
      if ($a->longitude && $a->latitude && $a->street1 && $a->state_id)
      {
        //make sure it's not a duplicate
        $sql = 'SELECT COUNT(id) FROM address WHERE entity_id = ? AND ((longitude = ? AND latitude = ?) OR street1 = ?) AND is_deleted = 0';
        $stmt = $this->db->execute($sql, array($id, $a->longitude, $a->latitude, $a->street1));
        
        if ($stmt->fetch(PDO::FETCH_COLUMN))
        {
          $this->printDebug("Duplicate address: " . $a->getOneLiner() . "; skipping...");
          continue;
        }
        else
        {
          $a->save();

          $this->printDebug("+ Imported address: " . $a->getOneLiner());

          if ($address['fec_id'])
          {
            $ref = new Reference;
            $ref->object_model = 'Address';
            $ref->object_id = $a->id;
            $ref->source = 'http://images.nictusa.com/cgi-bin/fecimg/?' . $address['fec_id'];
            $ref->name = 'FEC Filing ' . $address['fec_id'];

            if ($address['date'])
            {
              $ref->publication_date = $address['date'];
            }
            
            $ref->save();
  
            $this->printDebug("  (with reference)");
          }
        }
      }
      else
      { 
        $this->printDebug("\tCouldn't parse address: " . $str . "; skipping...(" . $a->longitude ." " .  $a->latitude ." " . $a->street1  ." " .  $a->state_id . ")");      
        continue;
      }
    }
    if (count($trans))
    {
      $this->saveMeta($id, 'matched');
    }
    else $this->saveMeta($id, 'no matches');
  }
  
  public function saveMeta($id, $predicate)
  {
    if ($meta = $this->hasMeta($id))
    {
      $meta->predicate = $predicate;
      $meta->save();
    }
    else
    {
      $meta = new ScraperMeta;
      $meta->scraper = 'OsImportAddresses';
      $meta->namespace = $id;
      $meta->predicate = $predicate;
      $meta->value = 1;
      $meta->save();
    }
    return $meta;
  }
  
  public function hasMeta($id,$last_searched_at = '2007',$predicate = null)
  {
    $q = LsDoctrineQuery::create()
      ->from('ScraperMeta')
      ->where('scraper = ? and namespace = ? and updated_at > ?',array('OsImportAddresses',$id, $last_searched_at));
    if ($predicate)
    {
      $q->addWhere('predicate = ?', $predicate);
    }
    $meta = $q->fetchOne();
    return $meta;
  }
  
  public function readline($prompt="", $possible = array('y','n','b'), $lim = 5)
  {
    $response = '';
    $ct = 0;
    while (!in_array($response,$possible) && $ct < $lim)
    {
      print $prompt;
      $out = "";
      $key = "";
      $key = fgetc(STDIN);        //read from standard input (keyboard)
      while ($key!="\n")        //if the newline character has not yet arrived read another
      {
        $out.= $key;
        $key = fread(STDIN, 1);
      }
      $response = $out;
      $ct++;
    }
    return $response;
  }

}