<?php

class LobbyingScraper extends Scraper
{
  protected $_dir;
  protected $_mode;
  protected $_org_ids = array();
  protected $_filing_id = null;
  protected $_count = 0;
  protected $_limit =3000;
  protected $_continuous = false;
  protected $_time = null;
  protected $_override = true;
  protected $_skip = array('Target', 'DuPont', 'Nationwide', 'US Bancorp', 'Gap', 'Southern', 'Progressive', 'Cummins', 'Williams', 'Kellogg', 'Knight', 'Huntsman', 'Lennar', 'ITT', 'First American Corp', 'Crown Holdings', 'Ball', 'Global Partners', 'Ross Stores', 'Western Digital', 'Hershey', 'Universal Health Services', 'El Paso', 'Harris', 'CA', 'Michaels Stores', 'Popular', 'Universal', 'Coach', 'Regis', 'Cabot', 'Phoenix', 'Pall', 'Andrew', 'West', 'Commerce Group', 'Pool', 'Donaldson', 'Meredith');

  public static $filing_url = 'http://soprweb.senate.gov/index.cfm?event=getFilingDetails&filingID=';
  
  public function setMode($mode)
  {
    $this->_mode = $mode;
  }
  
  public function setLimit($lim)
  {
    $this->_limit = $lim;
  }
   
  public function setContinuous($continuous)
  {
    $this->_continuous = $continuous;
  }
    
  public function setOrgIds($limit = 100, $start_id = null)
  {
    $q = LsDoctrineQuery::create()
      ->select('o.entity_id')
      ->from('Org o')
      ->limit($limit);  
    if ($this->_continuous && $this->hasMeta('first_round','last_processed'))
    {
      $q->addWhere('o.entity_id > ?', $this->getMeta('first_round','last_processed'));
    }
    else if ($start_id)
    {
      $q->addWhere('o.entity_id >= ?', $start_id);
    }
    $q->addWhere('o.entity_id < ?', 1006);
    $org_ids = $q->execute(array(), Doctrine::HYDRATE_NONE);
    foreach($org_ids as $org_id) 
    {
      $this->_org_ids[] = $org_id[0];
    }   
  }
  
  public function setFilingId($id)
  {
    $this->_filing_id = $id;
  }

  public function execute()
  {
    $this->stopTimer();
    $this->_time = $this->timer->getElapsedTime();
    $this->beginTimer();
    $this->_dir = sfConfig::get('sf_root_dir') . '/data/ldaFiles/';
    $this->printDebug('Mode: ' . $this->_mode);
	   if (!$this->safeToRun('lobbying'))
    {
      $this->printDebug('script already running');
      die;
    }
    if ($this->_mode == 'import')
    {
      $this->importFileInfo();
      $this->getRawFiles();
      $lobby_imports = LsDoctrineQuery::create()
                  ->from('LdaImport l')
                  ->where('l.done = ?', 0)
                  ->execute();
      foreach($lobby_imports as $lobby_import)
      {
        $this->importLdaData($lobby_import);
      }
    }
    else if ($this->_mode == 'mine')
    {
      foreach ($this->_org_ids as $id)
      {
        $org = Doctrine::getTable('Entity')->find($id);
        if (in_array($org->name, $this->_skip))
        {
           $this->saveMeta($org->id, 'is_complete', true);
           $this->saveMeta($org->id, 'skipped', true);
           continue;
        }
        $this->findOrgInfo($org);
        $this->saveMeta($org->id, 'is_complete', true);
        $this->saveMeta('first_round','last_processed',$org->id);
      }
    }
    else if ($this->_mode == 'check_filing')
    {
      if ($this->_filing_id)
      {
        if (preg_match('/\p{L}/isu',$this->_filing_id))
        {
          $filing = Doctrine::getTable('LdaFiling')->findByFederalFilingId($this->_filing_id);
        }
        else
        {
          $filing = Doctrine::getTable('LdaFiling')->find($this->_filing_id);
        }        
        $this->checkFiling($filing);
      }
      else
      {
        $q = Doctrine_Query::create()
                    ->from('LdaFiling l')
                    ->orderBy('RAND()')
                    ->limit(10)
                    ->execute();
        foreach($q as $filing)
        {
          $this->checkFiling($filing);
        }
      }
    }
    else if ($this->_mode == 'test')
    {
      $lda_govts = Doctrine::getTable('LdaGovt')->findAll();
      foreach($lda_govts as $lda_govt)
      {
        $arr = $this->prepGovtName($lda_govt->name);
        $this->printDebug($lda_govt->name . ' : ' . $arr[0]);
      }
    
    }
  }
  
  private function findOrgInfo($org)
  {
    $this->printDebug('***');
    $this->printDebug($org->name);
    if ($this->hasMeta($org->id, 'is_complete') && $this->getMeta($org->id, 'is_complete') && !$this->_override)
    {
      $this->printDebug("Already fetched lobbying data for Entity " . $org->id . "; skipping...");
      return;
    }
    $name = OrgTable::removeSuffixes($org->name, $exclude = array('Bancorp'));
    //$name = preg_replace('/(\p{Ll})(\p{Lu})/e','"$1 $2"', $name);
    $this->printDebug($name);
    $terms = preg_split('/[\s\.\-]+/isu',$name,-1,PREG_SPLIT_NO_EMPTY);
    $q = LsDoctrineQuery::create()
                ->from('LdaClient c');
    foreach($terms as $term)
    {
      $q->addWhere('name like ?', '%' . $term . '%');
    }
    $clients = $q->execute();
    $client_names = array();
    $client_ids = array();
    
    foreach($clients as $client)
    {
      $matched = true;
      $start = LsString::escapeStringForRegex($terms[0]);
      if (preg_match('/^' . $start . '\b/isu',$client->name) == 0 && preg_match('/(\(for\s+|on\s+behalf\s+of\s+)' . $start . '\b/isu',$client->name) == 0)
      {
        $matched = false;
      }
      $name = $client->name;
      
      if (stristr($name,'pilots') && stristr($name,'ass'))
      {
        $matched = false;
      }

      foreach($terms as $term)
      {
        $term = LsString::escapeStringForRegex($term);
        $new = preg_replace('/((^|\s)|\b)' . $term . '(\b|(\s|$))/isu',' ',$name,1);
        if ($new == $name)
        {
          $matched = false;
        }
        $name = $new;
      }
      $name = trim(OrgTable::removeSuffixes($name));
      if ($matched && strlen($name) > 0 && count(LsString::split($name)) > 2)
      {
        //$this->printDebug($name . ' HAS TOO MANY WRONG WORDS*******************************************');
        //sleep(1);
      }
      if ($matched == true)
      {
        //$this->printDebug('Found matching client: ' . $client->name);
        $client_ids[] = $client->id;
        $client_names[] = $client->name;
      }
      else
      {
        //$this->printDebug('Not a match: ' . $client->name . "\n");
      }
    }
    $client_names = array_unique($client_names);
    if (count($terms) > 1 || count($client_names) < 30)
    {
      /*foreach($client_names as $client_name)
      {
        $e = EntityTable::findByAlias($client_name,$context = 'lda_client');
        if (!$e || $e->id != $org->id)
        {
          $alias = new Alias;
          $alias->name = $client_name;
          $alias->Entity = $org;
          $alias->context = 'lda_client';
          $alias->save();
        }
      }*/
      foreach ($client_ids as $client_id)
      {
        $lda_filings = Doctrine::getTable('LdaFiling')->findByClientId($client_id);
        foreach ($lda_filings as $lda_filing)
        {
          $lf = Doctrine::getTable('LobbyFiling')->findOneByFederalFilingId($lda_filing->federal_filing_id);
          if (!$lf)
          {
            $this->printDebug($lda_filing->id);
            $this->printDebug(number_format(memory_get_usage()));
            $this->importFiling($org,$lda_filing);
          }
          else $this->printDebug('Previously imported: ' . $lda_filing->federal_filing_id . "\n");
        }
      }
    }
    else
    {
      //$this->printDebug('TOO MANY NAMES**************************');
    }
//    $fh = fopen('lobbying_client_names.csv','a');
//    $w = $org->name . "\t" . $org->id . "\t" . implode("\n\t\t", $client_names) . "\n\n";
//    fwrite($fh, $w);
//    fclose($fh);
    $this->printDebug(count($client_names));
    $this->printDebug(implode(', ', $client_names));
  }


  private function importFiling ($org,$lda_filing)
  {
    try
    {

      $this->printTimeSince();
      $this->printDebug('Starting import...');
      
      $excerpt = array();
      //$time = microtime(1);
      $this->db->beginTransaction();

      $date = null;    
      $excerpt['Federal Filing Id'] = $lda_filing->federal_filing_id;
      $excerpt['Year'] = $lda_filing->year;
      $excerpt['Type'] = $lda_filing->LdaType->description;
      if (preg_match('/^[^T]*/su',$lda_filing->received,$match))
      {
        $date = $match[0];
        $date = str_replace('/','-',$date);
      }

      $lda_registrant = Doctrine::getTable('LdaRegistrant')->find($lda_filing->registrant_id);
      $excerpt['Registrant'] = $lda_registrant->name;
      
      if ($lda_filing->client_id)
      {
        $lda_client = Doctrine::getTable('LdaClient')->find($lda_filing->client_id);
        $excerpt['Client'] = $lda_client->name;
      }
      else 
      {
        $this->db->rollback();
        return null;
      }
      $lobbying_entity = null;
      
      //DETERMINE (& CREATE) LOBBYING ENTITY

      //$this->printTimeSince();
      //$this->printDebug('determine/create...');
      if (strtolower(OrgTable::stripNamePunctuation($lda_client->name)) == strtolower(OrgTable::stripNamePunctuation($lda_registrant->name)))
      {
        $lobbying_entity = $org;
        $client_entity = null;
        if (!$lobbying_entity->lda_registrant_id)
        {
          $lobbying_entity->lda_registrant_id = $lda_registrant->federal_registrant_id;
          $lobbying_entity->save();
          $lobbying_entity->addReference(self::$filing_url . $lda_filing->federal_filing_id, null, $lobbying_entity->getAllModifiedFields(), 'LDA Filing',null,$date, false);
        }
        else if ($lobbying_entity->lda_registrant_id != $lda_registrant->federal_registrant_id)
        {
          $this->printDebug("LDA registrant ids did not match up for $lobbying_entity->name and $lda_registrant->name even though names matched $lda_client->name\n");
          $this->db->rollback();
          return null;
        }
        $this->printDebug($lobbying_entity->name . ' noted (same as client ' . $lda_client->name . ')');
      }
      else
      {
        $client_entity = $org;
        if ($lda_client->description)
        {
          $description = trim($lda_client->description);
          if ($description != '' && preg_match('/[\/\-]\d+[\/\-]/isu',$description) == 0)
          {
            if (strlen($description) < 200)
            {
              if (!$org->blurb || $org->blurb == '')
              {
                $org->blurb = $description;
              }
            }
            else if (!$org->summary || $org->summary == '')
            {
              $org->summary = $description;
            }
          }
        }
        $org->save();
        $this->printDebug($lda_client->name . ' is distinct from ' . $lda_registrant->name);
      }
      $lda_lobbyists = $lda_filing->LdaLobbyists;
      $excerpt['Lobbyists'] = array();
      foreach($lda_lobbyists as $lda_lobbyist)
      {
        $excerpt['Lobbyists'][] = $lda_lobbyist->name;
      }
      $excerpt['Lobbyists'] = implode('; ', $excerpt['Lobbyists']);
      if (!$lobbying_entity)
      {
        $lobbyist_name = null;
        if (count($lda_lobbyists))
        {
          $lobbyist_parts = explode(',',$lda_lobbyists[0]->name);
          if (count($lobbyist_parts) > 1)
          {
            $lobbyist_last = trim($lobbyist_parts[0]);
            $arr = LsString::split($lobbyist_parts[1]);
            $lens = array_map('strlen', $arr);
            arsort($lens);
            $keys = array_keys($lens);
            $lobbyist_longest = $arr[$keys[0]];
            $lobbyist_name = trim($lobbyist_parts[1]) . ' ' . trim($lobbyist_parts[0]);
            $existing_lobbyist_registrant = null;
          }
          else
          {
            $lobbyist_name = preg_replace('/^(Mr|MR|MS|Dr|DR|MRS|Mrs|Ms)\b\.?/su','',$lda_lobbyists[0]->name);
            $arr = LsString::split(trim($lobbyist_name));
            $arr = LsArray::strlenSort($arr);
            $lobbyist_last = array_pop($arr);
            if (count($arr))
            {
              $lobbyist_longest = array_shift(LsArray::strlenSort($arr));
            }
            else $lobbyist_longest = '';
          }
        }          
        //check to see if registrant and lobbyist are same
        if (count($lda_lobbyists) == 1 && (strtoupper($lda_lobbyists[0]->name) == strtoupper($lda_registrant->name) || ($lobbyist_last 
              && stripos($lda_registrant->name,$lobbyist_last) == (strlen($lda_registrant->name) - strlen($lobbyist_last))
              && stristr($lda_registrant->name,$lobbyist_longest))))
        {
          $existing_lobbyist_registrant = EntityTable::getByExtensionQuery('Lobbyist')
                          ->addWhere('lobbyist.lda_registrant_id = ?', $lda_registrant->federal_registrant_id)
                          ->execute()
                          ->getFirst();
                         
          if ($existing_lobbyist_registrant)
          {
            $lobbying_entity = $existing_lobbyist_registrant;
            $this->printDebug('Existing lobbyist is lobbying entity: ' . $lobbying_entity->name);
          }
          else
          {
            $lobbyist = $this->prepLobbyistName($lda_lobbyists[0]->name);
            if ($lobbyist)
            {
              $lobbyist->lda_registrant_id = $lda_registrant->federal_registrant_id;
              $lobbyist->save();
              $lobbyist->addReference(self::$filing_url . $lda_filing->federal_filing_id, null, $lobbyist->getAllModifiedFields(), 'LDA Filing',null,$date, false);
              $this->printDebug('New lobbyist/lobbying entity saved: ' . $lobbyist->name);
              $lobbying_entity = $lobbyist;
            }        
          }
        }  
        else if ($existing_firm = EntityTable::getByExtensionQuery('Org')->addWhere('org.lda_registrant_id = ? ', $lda_registrant->federal_registrant_id)->execute()->getFirst())
        {
          $modified = array();
          $lobbying_entity = $existing_firm;    
          if ($lda_registrant->description)
          {
            $description = trim($lda_registrant->description);
            if ($description != '' && preg_match('/[\/\-]\d+[\/\-]/isu',$description) == 0)
            {
              if (strlen($description) < 200)
              {
                if (!$existing_firm->blurb || $existing_firm->blurb == '')
                {
                  $existing_firm->blurb = $description;
                  $modified[] = 'blurb';
                }
              }
              else if (!$existing_firm->summary || $existing_firm->summary == '')
              {
                $existing_firm->summary = $description;
                $modified[] = 'summary';
              }
            }
          }
          if ($lda_registrant->address && $lda_registrant->address != '' && count($existing_firm->Address) == 0)
          {  
            if ($address = $existing_firm->addAddress($lda_registrant->address))
            {
              $existing_firm->save();
              $address->addReference(self::$filing_url . $lda_filing->federal_filing_id, null, $address->getAllModifiedFields(), 'LDA Filing',null,$date, false);
            }
          }
          $existing_firm->save();
          if (count($modified))
          {
            $existing_firm->addReference(self::$filing_url . $lda_filing->federal_filing_id, null, $modified, 'LDA Filing',null,$date, false);
          }
          $this->printDebug('Existing firm is lobbying entity: ' . $lobbying_entity->name);
        }
        else
        {
          $firm = new Entity;
          $firm->addExtension('Org');
          $firm->addExtension('Business');
          $firm->addExtension('LobbyingFirm');
          $firm->name =LsLanguage::titleize(OrgTable::stripNamePunctuation($lda_registrant->name),true);
          $firm->lda_registrant_id = $lda_registrant->federal_registrant_id;
          if ($lda_registrant->description)
          {
            $description = trim($lda_registrant->description);
            if ($description != '' && preg_match('/[\/\-]\d+[\/\-]/isu',$description) == 0)
            {
              if (strlen($description) < 200)
              {
                $firm->blurb = $description;
              }
              else
              {
                $firm->summary = $description;
              }
            }
          }
          if ($lda_registrant->address && $lda_registrant->address != '')
          {  
            if ($address = $firm->addAddress($lda_registrant->address))
            {
              $firm->save();
              $address->addReference(self::$filing_url . $lda_filing->federal_filing_id, null, $address->getAllModifiedFields(), 'LDA Filing',null,$date,false);
            }
          }
          $firm->save();
          $this->printDebug('New lobbying firm/lobbying entity saved: ' . $firm->name);
          $firm->addReference(self::$filing_url . $lda_filing->federal_filing_id, null, $firm->getAllModifiedFields(), 'LDA Filing',null,$date,false);
          $lobbying_entity = $firm;
        }
      }   
      
      //PREP GOVT ENTITIES 
      //$this->printTimeSince();
      //$this->printDebug('gov entities...');

      $lda_govts = $lda_filing->LdaGovts;
      //$this->printDebug('count of lda govs is ***** ' . count($lda_govts));
      $govt_entities = array();
      $excerpt['Government Bodies'] = array();

      foreach($lda_govts as $lda_govt)
      {
        $excerpt['Government Bodies'][] = $lda_govt->name;
        $name_arr = $this->prepGovtName($lda_govt->name);
        if (!$name_arr) continue;

        if ($govt_entity = EntityTable::findByAlias($lda_govt->name,$context = 'lda_government_body'))
        {
          $govt_entities[] = $govt_entity;
          //$this->printDebug('Existing govt entity: ' . $govt_entity->name);
        }
        else if ($govt_entity = EntityTable::getByExtensionQuery(array('Org','GovernmentBody'))->addWhere('name = ?',array($name_arr[0]))->fetchOne())
        {
          $govt_entities[] = $govt_entity;
          $alias = new Alias;
          $alias->context = 'lda_government_body'; 
          $alias->name = $lda_govt->name;
          $alias->entity_id = $govt_entity->id;
          $alias->save();
        }
        else
        {

          $govt_entity = new Entity;
          $govt_entity->addExtension('Org');
          $govt_entity->addExtension('GovernmentBody');
          $govt_entity->name = $name_arr[0];
          $govt_entity->name_nick = $name_arr[1];
          $govt_entity->is_federal = 1;
          $govt_entity->save();
          $alias = new Alias;
          $alias->context = 'lda_government_body'; 
          $alias->name = $lda_govt->name;
          $alias->entity_id = $govt_entity->id;
          $alias->save();

          $govt_entity->addReference(self::$filing_url . $lda_filing->federal_filing_id, $excerpt, $govt_entity->getAllModifiedFields(), 'LDA Filing',null,$date,false);
          $govt_entities[] = $govt_entity;
        }


      }
      $excerpt['Government Bodies'] = implode('; ', $excerpt['Government Bodies']);
      $excerpt_str = '';
      foreach ($excerpt as $k => $v)
      {
        $excerpt_str .= $k . ": ";
        $excerpt_str .= $v . "\n";
      }
      $excerpt = trim($excerpt_str);
      $this->printDebug($excerpt);

      $relationships = array();
      
      $lobbying_entity_extensions = $lobbying_entity->getExtensions();

      //CREATE LOBBYIST POSITION RELATIONSHIPS
      //$this->printTimeSince();
      //$this->printDebug('lobbyist positions...');

      $category = Doctrine::getTable('RelationshipCategory')->findOneByName('Position');
      if (!in_array('Lobbyist',$lobbying_entity_extensions))
      {     
        $firm_lobbyists = array();
        if ($lobbying_entity->exists())
        {
          
          $q = LsDoctrineQuery::create()
           ->from('Entity e')
           ->leftJoin('e.Relationship r ON (r.entity1_id = e.id)')
           ->where('r.entity2_id = ? AND r.category_id = ?', array($lobbying_entity->id, RelationshipTable::POSITION_CATEGORY));
          
          $firm_lobbyists = $q->execute();

        }
        
        $lobbyists = array();
        
        foreach($lda_lobbyists as $lda_lobbyist)
        {
          $lobbyist = $this->prepLobbyistName($lda_lobbyist->name);
          
          if (!$lobbyist)
          {
            continue;
          }
          $existing_lobbyist = null;
          foreach($firm_lobbyists as $fl)
          {

            if (PersonTable::areNameCompatible($fl,$lobbyist))
            {
              $existing_lobbyist = $fl;
              break;
            }            
          }
          //echo "before lobb save or rel save: ";
          //$this->printTimeSince();
          if (!$existing_lobbyist)
          {
            $lobbyist->save();
            $lobbyist->addReference(self::$filing_url . $lda_filing->federal_filing_id, $excerpt, $lobbyist->getAllModifiedFields(), 'LDA Filing',null,$date,false);
            //$this->printDebug('New lobbyist saved: ' . $lobbyist->name);
            $r = new Relationship;
            $r->Entity1 = $lobbyist;
            $r->Entity2 = $lobbying_entity;
            $r->setCategory('Position');
            $r->description1 = 'Lobbyist';
            $r->is_employee = 1;
            $r->save();
            $r->addReference(self::$filing_url . $lda_filing->federal_filing_id, $excerpt, $lobbyist->getAllModifiedFields(), 'LDA Filing',null,$date,false);
            //$this->printDebug('New position relationship saved: ' . $lobbying_entity->name . ' and ' . $lobbyist->name);
            $lobbyists[] = $lobbyist;
          }
          else 
          {
            //$this->printDebug('Lobbyist exists: ' . $lobbyist->name . ' is same as ' . $existing_lobbyist->name);
            $lobbyists[] = $existing_lobbyist;
          }
        }
      }      

      //PREP ISSUES
      //$this->printTimeSince();
      //$this->printDebug('issues...');
      
      $issues = array();
      $lda_issues = Doctrine_Query::create()
              ->from('LdaFilingIssue f')
              ->leftJoin('f.LdaIssue i')
              ->where('f.filing_id = ?', $lda_filing->id)
              ->execute();
              
      foreach($lda_issues as $lda_issue)
      {
        $name = LsLanguage::nameize($lda_issue->LdaIssue->name);
        if (!$issue = Doctrine::getTable('LobbyIssue')->findOneByName($name))
        {
          $issue = new LobbyIssue;
          $issue->name = $name;
          $issue->save();
          //$this->printDebug('Lobbying issue saved: ' . $issue->name);
        } 
        $issues[] = array($issue,$lda_issue->specific_issue);
      }
            
      //CREATE LOBBY FILING
      //$this->printTimeSince();  
      //$this->printDebug('creating lobby filing:');
      $lobby_filing = new LobbyFiling;
      $lobby_filing->year = $lda_filing->year;
      $lobby_filing->amount = $lda_filing->amount;
      $lobby_filing->federal_filing_id= $lda_filing->federal_filing_id;
      $period = $lda_filing->LdaPeriod->description;
      $lobby_filing->start_date = $date;
      if ($paren = strpos($period,'('))
      {
        $lobby_filing->period = trim(substr($period, 0, $paren));
      }
      else
      {
        $lobby_filing->period = 'Undetermined';
      }
      $lobby_filing->report_type = LsLanguage::nameize($lda_filing->LdaType->description);

      
      foreach ($issues as $issue)
      {
        $filing_issue = new LobbyFilingLobbyIssue;
        $filing_issue->Issue = $issue[0];
        $filing_issue->Filing = $lobby_filing;
        $filing_issue->specific_issue = $issue[1];
        $filing_issue->save();
      }

      if (in_array('Lobbyist',$lobbying_entity_extensions))
      {
        $lobby_filing->Lobbyist[] = $lobbying_entity;
        //$this->printDebug('Lobbying entity lobbyist added to lobbying relationship: ' . $lobbying_entity->name);
      }      
      else
      {
        foreach($lobbyists as $lobbyist)
        {
          $lobby_filing->Lobbyist[] = $lobbyist;
        }
      }

      //var_dump($lobby_filing->toArray());
      $lobby_filing->save();

      //CREATE TRANSACTION RELATIONSHIP, IF ANY      
      //$this->printTimeSince();  
      //$this->printDebug('starting transaction relationships:');
      $transaction = null; 

      if ($client_entity != null)
      {

        $transaction = RelationshipTable::getByCategoryQuery('Transaction')
                ->addWhere('r.entity1_id = ?', $client_entity->id)
                ->addWhere('r.entity2_id = ?', $lobbying_entity->id)
                ->addWhere('transaction.is_lobbying = ?', 1)
                ->fetchOne();
                
        if ($transaction)
        {
          $transaction->updateDateRange($date, true);   
          if ($lda_filing->amount && $lda_filing->amount != '')
          {
            if ((!$transaction->amount || $transaction->amount == ''))
            {
              $transaction->amount = $lda_filing->amount;
            }
            else
            {
              $transaction->amount += $lda_filing->amount;
            }
          }
          $transaction->filings++;
          $transaction->save();
          $transaction->addReference(self::$filing_url . $lda_filing->federal_filing_id, $excerpt, $transaction->getAllModifiedFields(), 'LDA Filing',null,$date,false);

        }
        else
        {       
          $transaction = new Relationship;
          $transaction->Entity1 = $client_entity;
          $transaction->Entity2 = $lobbying_entity;				
      		$transaction->setCategory('Transaction');
      		$transaction->description1 = 'Lobbying Client';
      		$transaction->is_lobbying = 1;
      		$transaction->filings=1;
      		$transaction->updateDateRange($date, true);   
          if (in_array('Person',$lobbying_entity_extensions))
          {  
            $transaction->description2 = 'Hired Lobbyist';
          }   
          else
          {
      		  $transaction->description2 = 'Lobbying Firm';
      		}   
      		if ($lda_filing->amount && $lda_filing->amount != '')   		
          {
            $transaction->amount = $lda_filing->amount;
          }
      	  $transaction->save();
      	  $transaction->addReference(self::$filing_url . $lda_filing->federal_filing_id, $excerpt, $transaction->getAllModifiedFields(), 'LDA Filing',null,$date,false);
      	  //$this->printDebug('New lobbying transaction saved between client ' . $client_entity->name . ' and lobbying firm ' . $lobbying_entity->name);
      	}
      	$relationships[] = $transaction;
      }
           
      //CREATE LOBBYING RELATIONSHIP     
      //$this->printTimeSince();  
      //$this->printDebug('starting lobbying relationships:');
          
      foreach($govt_entities as $govt_entity)
      { 
        $lobbying_relationship = RelationshipTable::getByCategoryQuery('Lobbying')
                ->addWhere('r.entity1_id = ?', $lobbying_entity->id)
                ->addWhere('r.entity2_id = ?', $govt_entity->id)
                ->fetchOne();
            
         
        if ($lobbying_relationship)
        {
          $lobbying_relationship->updateDateRange($date);

          $lobbying_relationship->filings++;         
          $lobbying_relationship->save();
        }   
        else
        {
          $lobbying_relationship = new Relationship;
          $lobbying_relationship->Entity1 = $lobbying_entity;
          $lobbying_relationship->Entity2 = $govt_entity;
          $lobbying_relationship->setCategory('Lobbying');
          if ($transaction)
          {
            $lobbying_relationship->description1 = 'Lobbying (for client)';
          }
          else
          {
            $lobbying_relationship->description1 = 'Direct Lobbying';
          }
          $lobbying_relationship->description2 = $lobbying_relationship->description1;
          $lobbying_relationship->updateDateRange($date,true);
          $lobbying_relationship->filings = 1;          
          $lobbying_relationship->save();
          $lobbying_relationship->addReference(self::$filing_url . $lda_filing->federal_filing_id, $excerpt, $lobbying_relationship->getAllModifiedFields(), 'LDA Filing',null,$date,false);
        }
        $relationships[] = $lobbying_relationship;       
      }
      
      foreach($relationships as $relationship)
      {
        $lobby_filing->Relationship[] = $relationship;
      }
      $lobby_filing->save();
      //$this->printTimeSince();          
      $this->printDebug("Import Completed\n");
      $this->db->commit();
    }
    catch (Exception $e)
    {
      $this->db->rollback();
      throw $e;
    }
    
  }
  
  private function getRawFiles()
  {
    $url = 'http://www.senate.gov/legislative/Public_Disclosure/database_download.htm';
    $this->browser->get($url);
    $text = $this->browser->getResponseText();
    $re = '/(http\:\/\/soprweb\.senate\.gov\/downloads\/(((19|20)\d\d)_(\d)\.zip))">.*?((\d+)\/(\d+)\/(\d+))/is';
    //</td><td align="center">6/17/2008</td>
    preg_match_all($re,$text,$matches,PREG_SET_ORDER);
    foreach ($matches as $match)
    {
      $date = new LsDate($match[6]);
      $file = sfConfig::get('sf_root_dir') . '/data/ldaFiles/' . $match[2];
      if (file_exists($file))
      {
        $date2 = new LsDate(date('Y-m-d',filemtime($file)));
        if (LsDate::compare($date,$date2) < 1)
        {
          continue;
        }
      }

      $this->browser->get($match[1]);

      $saved = file_put_contents($file, $this->browser->getResponseText());

      if ($saved !== FALSE) 
      {
        $this->printDebug('saved');
        $zip = zip_open($file);
        if (is_resource($zip)) 
        {
          $this->printDebug('unzipped');
          while ($zip_entry = zip_read($zip)) 
          {
            $n = basename(zip_entry_name($zip_entry));
            $li = Doctrine::getTable('LdaImport')->findOneByFilename($n);
            if ($li && $li->filesize == zip_entry_filesize($zip_entry)) 
            {
              continue;
            }
            $fp = fopen($this->_dir . $n, 'w');
   
            if (zip_entry_open($zip, $zip_entry, "r")) 
            {
              $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
              zip_entry_close($zip_entry);
              fwrite($fp, $buf);
              fclose($fp);
              $li = new LdaImport;
              $li->year = $match[2];
              $li->quarter = $match[5];
              $li->filename = $n;
              $li->filesize = filesize($this->_dir . $n);
              $li->offset = 0;
              $li->save();
            }
          }
          zip_close($zip);
        } 
        else 
        {
          $this->printDebug('zip failure');
        }
      } 
      else 
      {
        continue;
      }
    }
  }
  
  private function prepGovtName($str)
  {
    $str = trim($str);
    if ($str == 'HOUSE OF REPRESENTATIVES')
    {
      return array('US House of Representatives',null);
    }
    else if ($str == 'SENATE')
    {
      return array('US Senate', null);
    }
    else if ($str == 'NONE' || $str == 'UNDETERMINED' || $str == '')
    {
      return null;
    }
    else if (preg_match('/(Navy|Army|Air\sForce)\,\s+Dept\s+of/',$str,$match))
    {
      $str = str_replace($match[0],'US ' . $match[1],$str);
    }
    preg_match('/\(([^\)]+)\)?/s',$str,$match);
    $abb = null;
    if (count($match))
    {
      $str = trim(str_replace($match[0],'',$str));
      $abb = $match[1];
    }
    if ($abb == 'Corps of Engineers') 
    {
      return array('US Army Corps of Engineers',null);
    }  
    else if ($abb == 'Other')
    {
      $abb = null;
    }  
    $str = str_replace(array('Natl','Dept','.'),array('National','Department',''),$str);
    $parts = explode(',',$str);
    if (count($parts) > 1)
    {
      $str = trim(array_pop($parts));
      $str .= ' ' . implode('; ',$parts);
    }
    $str = LsLanguage::titleize(OrgTable::stripNamePunctuation($str));
    return array($str, $abb);
  }
  
  public function prepLobbyistName($str)
  {
    //get rid of extra spaces and stuff in parens
    $str = trim(preg_replace(array('/\([^\)]*\)?/s','/\s+/s'),array('',' '),$str));
    $name_parts = explode(',',$str);
    //no comma, no parsable name (for now)
    if (count($name_parts) < 2) return null;
    
    $name_last = trim(array_shift($name_parts));
    $name_rest = trim(implode(' ',$name_parts));
    
    /*$person = new Entity;
    $person->addExtension('Person');
    $person->addExtension('Lobbyist');
    $person->name_last = trim(array_shift($name_parts));
    $name_rest = trim(implode(' ',$name_parts));*/
    $name_nick = null;
    if (preg_match('/["\'](.*?)["\']/isu',$name_rest,$match,PREG_OFFSET_CAPTURE) == 1)
    {
      $name_nick = $match[1][0];
      $name_rest = str_replace($match[0][0],'',$name_rest);
    }
    $name_suffix = null;
    $suffixes = PersonTable::$nameParseSuffixes;
		while ($suffix = current($suffixes))
		{
			if ($name_rest != ($new = preg_replace('/ ' . $suffix . '$/i', '', $name_rest)))
			{
				$name_suffix = $suffix . ' ' . $name_suffix;
				$name_rest = trim($new);
				reset($suffixes);
				continue;
			}
			next($suffixes);
		}
		$name_suffix = $name_suffix ? trim($name_suffix) : null;
    $person = PersonTable::parseFlatName($name_rest . ' ' . $name_last, $name_last);
    if ($name_nick)
    {
      $person->name_nick = LsLanguage::nameize($name_nick);
    }
    if ($name_suffix)
    {
      $person->name_suffix = $name_suffix;
    }
    $person->addExtension('Lobbyist'); 
    $person->name_last = trim($person->name_last);
    if (!$person->name_last || $person->name_last == '')
    {
      return null;
    }
    return $person;
  }
  
  private function importFileInfo()
  {
    if ($handle = opendir($this->_dir)) {
      while ($file = readdir($handle)) 
      {
        if (stristr($file,'xml')) 
        {
          if (!Doctrine::getTable('LdaImport')->findOneByFilename($file))
          {
            if (preg_match('/^(\d\d\d\d)_(\d)/',$file,$match))
            {
              try
              {
                $this->db->beginTransaction();
                $li = new LdaImport;
                $li->year = $match[1];
                if ($match[2] > 0 && $match[2] < 5)
                {
                  $li->quarter = $match[2];
                }
                $li->filename = $file;
                $li->filesize = filesize($this->_dir . $file);
                $li->offset = 0;
                $li->save();
                $this->printDebug($li->filename . ' imported');
                $this->db->commit();
              }
              catch (Exception $e)
              {
                $this->db->rollback();
                throw $e;
              }
            }
          }
        }
      }
      closedir($handle);
    }
  }
  
  private function importLdaData($lobby_import)
  {
    $path = $this->_dir . $lobby_import->filename;
    
    $raw = file_get_contents($path);
    
    $xml = new SimpleXMLElement($raw);
    $filings = $xml->Filing;
    $limit = count($filings);
    $this->printDebug('importing data from ' .$lobby_import->filename . ' (record ' . $lobby_import->offset . ' of ' . $limit . ')');
    for ($n = (int) $lobby_import->offset; $n < $limit; $n++)
    {  
      $this->_count = $this->_count + 1;
      if ($this->_count > $this->_limit) 
      {
        die;
      }  
      try
      {
        $this->db->beginTransaction();
        $lobby_import->offset = $n;
        if ($n == $limit-1)
        {
          $lobby_import->done = 1;
        }
        $lobby_import->save();

        if (!isset($filings[$n]))
        {
          echo 'ok';
          var_dump($filings[$n-1]);
          var_dump($filings[$n+1]);
          $this->printDebug('not set'. $n);
          $this->db->commit();
          continue;
        }
        
        $filing = $filings[$n];
        if (!isset($filing->Registrant))
        {
          $this->db->commit();
          continue;
        }
        //var_dump($filing);
        $f = new LdaFiling;
        $f->federal_filing_id = $filing['ID'];
        $f->year = $filing['Year'];
        $f->amount = $filing['Amount'];
        $f->received = $filing['Received'];
        $f->import_id = $lobby_import->id;
        $f->offset = $n;
        //check for duplicate
        if (Doctrine::getTable('LdaFiling')->findOneByFederalFilingId($f->federal_filing_id))
        {
          $this->db->commit();
          continue;
        }
        //set registrant
        if (!$r = Doctrine::getTable('LdaRegistrant')->findOneByFederalRegistrantId($filing->Registrant['RegistrantID']))
        {

          $r = new LdaRegistrant;
          $r->name = LsString::spacesToSpace($filing->Registrant['RegistrantName']);
          $r->federal_registrant_id = $filing->Registrant['RegistrantID'];
          $r->address = $filing->Registrant['Address'];
          $r->description = LsString::spacesToSpace($filing->Registrant['GeneralDescription']);
          $r->country = $filing->Registrant['RegistrantCountry'];
          $r->save();
        }
        
        $f->registrant_id = $r->id;
        
        //set client
        if ($filing->Client)
        {
          if (!$c = LsQuery::getByModelAndFieldsQuery('LdaClient',array('registrant_id' =>$r->id,'federal_client_id' =>$filing->Client['ClientID']))->execute()->getFirst())
          {
            $c = new LdaClient;
            $c->name = LsString::spacesToSpace($filing->Client['ClientName']);
            $c->federal_client_id = $filing->Client['ClientID'];
            $c->registrant_id = $r->id;
            $c->contact_name = LsString::spacesToSpace($filing->Client['ContactFullname']);
            $c->description = LsString::spacesToSpace($filing->Client['GeneralDescription']);
            $c->country = $filing->Client['ClientCountry'];
            $c->state = $filing->Client['ClientState'];
            $c->save();
          }
          
          $f->client_id = $c->id;
        }         

        //set filing type
        if ($type = (string) $filing['Type'])
        {
          //look for existing type
          if (!$t = Doctrine::getTable('LdaType')->findOneByDescription($type))
          {
            $t = new LdaType;
            $t->description = $type;
            $t->save();
          }
          
          $f->type_id = $t->id;
          unset($t);
        }
        
        if ($period = (string) $filing['Period'])
        {
          //look for existing period
          if (!$p = Doctrine::getTable('LdaPeriod')->findOneByDescription($period))
          {
            $p = new LdaPeriod;
            $p->description = $period;
            $p->save();
          }
          $f->period_id = $p->id;
        }
      
        $f->save();

        //add lobbyists  
        if ($filing->Lobbyists)
        {
          foreach ($filing->Lobbyists->Lobbyist as $lobbyist)
          {
            $name = (string) $lobbyist['LobbyistName'];
            if (!$l = LsQuery::getByModelAndFieldsQuery('LdaLobbyist',array('registrant_id' =>$r->id,'name' =>$name))->execute()->getFirst())
            {
              $l = new LdaLobbyist;
              $l->name = $name;
              $l->registrant_id = $r->id;
              $l->status = $lobbyist['LobbyistStatus'];
              $l->indicator = $lobbyist['LobbyisteIndicator'];
              $l->official_position = $lobbyist['OfficialPosition'];
              $l->save();
            }
            
            $fl = new LdaFilingLobbyist;
            $fl->filing_id = $f->id; 
            $fl->lobbyist_id = $l->id;
            $fl->save();
            
            unset($fl);
            unset($l);
          }
        }

        //add govt entities
        
        if ($filing->GovernmentEntities)
        {
          foreach ($filing->GovernmentEntities->GovernmentEntity as $govt)
          {
            $govt = trim($govt['GovEntityName']);
            
            if (!$g = Doctrine::getTable('LdaGovt')->findOneByName($govt))
            {
              $g = new LdaGovt;
              $g->name = $govt;
              $g->save();
            }
            
            $fg = new LdaFilingGovt;
            $fg->filing_id = $f->id;
            $fg->govt_id = $g->id;
      
            $fg->save();
            unset($fg);
            unset($g);
          }
        }

        //add issues
        
        if ($filing->Issues)
        {
          foreach ($filing->Issues->Issue as $issue)
          {
            $code = (string) $issue['Code'];
      
            if (!$i = Doctrine::getTable('LdaIssue')->findOneByName($code))
            {
              $i = new LdaIssue;
              $i->name = $code;
              $i->save();
            }
            
            $fi = new LdaFilingIssue;
            $fi->filing_id = $f->id;
            $fi->issue_id = $i->id;
            $fi->specific_issue = $issue['SpecificIssue'];
          
            $fi->save();
            
            unset($fi);
            unset($i);
          }
        }

        $this->printDebug($f->federal_filing_id);

        //check for duplicate again
        if (Doctrine::getTable('LdaFiling')->findOneByFederalFilingId($f->federal_filing_id))
        {
          $this->db->rollback();
          continue;
        }
        $this->db->commit();
      }
      catch (Exception $e)
      {
        $this->db->rollback();
        throw $e;
      }

      unset($f);
      unset($r);
      unset($c);
      unset($filing);
    }

    unset($xml);
    unset($raw);
    unset($filings);
  }
  
  public function checkFiling($filing)
  {
    $lobby_import = Doctrine::getTable('LdaImport')->find($filing->import_id);
    $path = $this->_dir . $lobby_import->filename;
    $raw = file_get_contents($path);
    $xml = new SimpleXMLElement($raw);
    $filings = $xml->Filing;
    $n = (int) $filing->offset;
    var_dump($filings[$n]);  
  }
  
  private function printTimeSince()
  {
    $this->stopTimer();
    $now = $this->timer->getElapsedTime();
    $diff = $now - $this->_time;
    $this->printDebug('****************************************time since last check: ' . $diff);
    $this->_time = $now; 
    $this->beginTimer();
  }
  
}