<?php

class PublicCompanyScraper extends Scraper
{
  protected $entity;
  protected $filing_date;
  protected $recent_filing_date;
  protected $filing_name;
  protected $filing_url = null;
  protected $years = array('2009','2010');
  protected $ownership_start_year = '2008';
  protected $limit = 10;
  protected $start_id = 1;
  protected $search_depth = 50;
  protected $log_namespace = 'log';
  protected $filings = array();
  protected $is_already_scraped = false;
  protected $old_board_rel_ids = array();
  protected $old_exec_rel_ids = array();
  protected $old_board_entity_ids = array();
  protected $old_exec_entity_ids = array();
  protected $annual_filing_types = array(
    's-' => 'Registration Statement',
    'def 14a' => 'Proxy Statement'  
  );
  protected $repeat_mode = false;
  protected $empty = false;
  protected $list_id = null;

  
  public function setStartId($id)
  {
    $this->start_id = $id;
  }
  
  public function setCompanyByTicker($ticker)
  {
    $company = Doctrine::getTable('PublicCompany')->findOneByTicker($ticker);

    if ($company)
    {
      $this->entity = Doctrine::getTable('Entity')->find($company->entity_id);
    }
  }
  
  public function setLimit($limit)
  {
    $this->limit = $limit;
  }
  
  public function setSearchDepth($search_depth)
  {
    $this->search_depth = $search_depth;
  }
  
  public function setYears($years)
  {
    $this->years = $years;
  }

  public function setRepeatMode($bool)
  {
    $this->repeat_mode = $bool;
  }

  public function setListId($listId)
  {
    $this->list_id = $listId;
  }
  
  public function execute()
  {
    $companies = $this->getCompanies();

    foreach ($companies as $company)
    {
      $this->printDebug("\n\n=== SCRAPING " . $company->name . " ===");

      $this->entity = $company;
      $this->empty = false;
      $this->importRoster();
      
      if (!$this->is_already_scraped)
        $this->logCompany($company, $this->empty);
    }
      
  } 

  public function getCompanies()
  {
    if ($this->list_id)
    {
      $q = LsDoctrineQuery::create()
        ->from('Entity e')
        ->leftJoin('e.PublicCompany pc')
        ->leftJoin('e.LsListEntity le')
        ->where('le.list_id = ?', $this->list_id)
        ->andWhere('e.primary_ext = ?', 'Org')
        ->andWhere('(pc.ticker IS NOT NULL OR pc.sec_cik IS NOT NULL)')  //need ticker or CIK to get directors
        ->orderBy('le.rank ASC')
        ->limit($this->limit);

      if ($this->start_id)
      {
        $q->addWhere('le.rank >= ?', $this->start_id);
      }
    }
    else
    {
      $start_id = $this->entity ? $this->entity->id : $this->start_id;
  
      $q = LsDoctrineQuery::create()
        ->from('Entity e')
        ->leftJoin('e.PublicCompany pc')
        ->where('e.id >= ?', $start_id)
        ->andWhere('e.primary_ext = ?', 'Org')
        ->andWhere('(pc.ticker IS NOT NULL OR pc.sec_cik IS NOT NULL)')  //need ticker or CIK to get directors
        ->limit($this->limit);
    }
      
    return $companies = $q->execute();  
  }
  
  public function importRoster($include_execs = true, $include_board = true)
  {
    //we need a company CIK to get data from the SEC
    if ((($this->entity->sec_cik == NULL) || ($this->entity->sec_cik == '')) && $this->entity->ticker)
    {
      $this->printDebug("Fetching CIK for company with ticker " . $this->entity->ticker . "...");
      $this->entity->getCik();
    }

    if (!$this->entity->sec_cik)
    {
      $this->printDebug("Can't scrape public company: no company CIK!\n");
      $this->empty = true;
      return;
    }
    
    
    //make sure we didn't already scrape this company
    if ($this->is_already_scraped = $this->isAlreadyScraped($this->entity))
    {
      $this->printDebug("Already scraped " . $this->entity->name . "; skipping...\n");
      return;
    }



    //get existing director and executive entity & relationship IDs for later use
    $sql = 'SELECT r.id, r.entity1_id FROM relationship r LEFT JOIN position p ON (p.relationship_id = r.id) ' .
           'WHERE r.entity2_id = ? AND r.category_id = ? AND p.is_board = 1';
    $stmt = $this->db->execute($sql, array($this->entity->id, RelationshipTable::POSITION_CATEGORY));
    $this->old_board_rel_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $stmt = $this->db->execute($sql, array($this->entity->id, RelationshipTable::POSITION_CATEGORY));
    $this->old_board_entity_ids = array_unique($stmt->fetchAll(PDO::FETCH_COLUMN, 1));

    $sql = 'SELECT r.id, r.entity1_id FROM relationship r LEFT JOIN position p ON (p.relationship_id = r.id) ' .
           'WHERE r.entity2_id = ? AND r.category_id = ? AND p.is_executive = 1';
    $stmt = $this->db->execute($sql, array($this->entity->id, RelationshipTable::POSITION_CATEGORY));
    $this->old_exec_rel_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $stmt = $this->db->execute($sql, array($this->entity->id, RelationshipTable::POSITION_CATEGORY));
    $this->old_exec_entity_ids = array_unique($stmt->fetchAll(PDO::FETCH_COLUMN, 1));



    //compile roster of company directors and executives using recent Form 4s
    $form4_urls = $this->getForm4Urls();
    $roster = array();
    $unique_ciks = array();

    foreach ($form4_urls as $url_arr)
    {
      if ($result = $this->getForm4Data($url_arr))
      {
        if (!in_array($result['personCik'], $unique_ciks))
        {
          $roster[] = $result;
          $unique_ciks[] = $result['personCik'];
          $this->printDebug("Added " . $result['parsedName'] . " to roster");        
        }
      }
    }

    $this->printDebug("Fetched roster with " . count($roster) . " names for " . $this->entity->name . " (" . $this->entity->id . ")");

    if (!count($roster))
    {
      $this->printDebug("No roster found; aborting company scrape...");
      $this->empty = true;
      return;    
    }


        
    //search company info for roster names
    //try both S- registration statements and proxies for given years
    $this->getFilings();    

    if (!count($this->filings))
    {
      $this->printDebug("No annual filings found; aborting company scrape...");
      $this->empty = true;
      return;
    }

    $this->printDebug("Cross-checking roster names using annual filings:");
    
    foreach ($this->filings as $filing)
    {
      $this->printDebug($filing['url'] . " (" . $filing['date'] . ")");
    }
    
    $current_board_ids = array();



    
    foreach ($roster as $r)
    {
      $this->printDebug("Cross-checking " . $r['parsedName'] . "...");

      if ($r['primaryExt'] == 'Org')
      {
        $this->printDebug("Organization; skipping...");
        continue;
      }

      $matched = false;
      $this->filing_date = null;
      $this->filing_name = null;
      $this->filing_url = null;
      
      foreach ($this->filings as $filing)
      {
        if (preg_match_all($r['regexName'], $filing['doc']->text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE))
        {
          $matched = true;
          $this->filing_date = $filing['date'];
          $this->filing_name = $filing['name'];
          $this->filing_url = $filing['url'];
          break;
        }        
      }


      //if name found in filing text, or if form 4 is more recent than the filing date, consider it current
      //if not: consider not current if board, consider it unknown otherwise
      if ($r['isDirector'] == '1')
      {
        $current = $matched || strtotime($r['date']) > strtotime($this->recent_filing_date);
      }
      else
      {
        if ($matched)
        {
          $current = 1;
        }
        elseif (strtotime($r['date']) > strtotime('1 year ago'))
        {
          $current = 1;
        }
        else
        {
          $current = null;
        }
      }


              
      if (($r['isDirector'] == '1' && $include_board) || ($include_execs && $r['officerTitle'] != '') || ($r['isTenPercentOwner'] == '1'))
      {
        //look for existing entity by CIK
        $p = EntityTable::getByExtensionQuery('BusinessPerson')
          ->addWhere('businessperson.sec_cik = ?', $r['personCik'])
          ->fetchOne(); 
          
        if (!$p)
        {
          //check for entity with same first & last names and a position in this company
          $matches = LsDoctrineQuery::create()
            ->select('e.*')
            ->from('Entity e')
            ->leftJoin('e.Person p')
            ->leftJoin('e.Relationship r ON (e.id = r.entity1_id)')
            ->where('p.name_last = ?', $r['person']['name_last'])
            ->andWhere('p.name_first = ?', $r['person']['name_first'])
            ->andWhere('e.primary_ext = ?', 'Person')
            ->andWhere('r.entity2_id = ?', $this->entity->id)
            ->andWhere('r.category_id = 1')
            ->execute();

          if (count($matches) == 1)
          {
            $p = $matches[0];
            $p->addExtension('BusinessPerson');
            $p->sec_cik = $r['personCik'];    
            $p->save();

            $this->printDebug("Found existing person with same name in same company: " . $p['name'] . " (" . $p['id'] . ")");
          }
          else
          {
            $p = $this->importPerson($r);
          }

        }
        else
        {
          $this->printDebug("Found existing person with same SEC CIK: " . $p['name']);          
        }
          
        if ($p)
        {
          //save entity ID for comparison with existing entities
          if ($current)
            $current_board_ids[] = $p->id;

          //add address to person
          //$this->importAddress($r['address'], $p, $r);

          if ($r['isDirector'] == '1' && $include_board)
          {
            $this->importBoardRelationship($p->id, $r, $r['officerTitle'], $current);              
          }

          if ($r['officerTitle'] != '' && $include_execs)
          {
            $descriptions = self::parseDescriptionStr($r['officerTitle'], $this->entity);

            foreach ($descriptions as $d)
            {
              //don't create executive positions with board titles
              if ($r['isDirector'] != '1' || !LsArray::inArrayNoCase($d, PositionTable::$boardTitles))
              {
                $this->importExecutiveRelationship($p->id, $r, $d, $current);
              }
            }
          }


          if ($r['isTenPercentOwner'])
          {
            //make sure there isn't already one
            $count = LsDoctrineQuery::create()
              ->from('Relationship r')
              ->where('r.category_id = ?', RelationshipTable::OWNERSHIP_CATEGORY)
              ->andWhere('r.entity1_id = ? AND r.entity2_id = ?', array($p->id, $this->entity->id))
              ->count();

            if (!$count)
            {
              $rel = new Relationship;
              $rel->setCategory('Ownership');
              $rel->entity1_id = $p->id;
              $rel->entity2_id = $this->entity->id;
              $rel->is_current = (strtotime($r['date']) > strtotime('1 year ago')) ? true : null;
              $rel->description1 = 'major shareholder';

              //Form 3s let us set a start date
              if ($r['formName'] == 'Form 3' && $r['date'])
              {
                //filing date could be innacurate, so only indicate month
                $date = LsDate::formatFromText($r['date']);
                $rel->start_date = preg_replace('/-\d\d$/', '-00', $date);
              }

              $rel->save();
  
              //save source
              $rel->addReference(
                $r['xmlUrl'], 
                null, 
                null, 
                $this->entity->name . ' ' . $r['formName'], 
                null, 
                $r['date']
              );
        
              $this->printDebug("+ Ownership relationship created: " . $rel->id);
            }
          }
        }          
      }
      else
      {
        $this->printDebug("Not a board, executive, or ownership position; skipping...");
      }
    }


    if (count($this->old_board_rel_ids))
    {
      //update old board relationships
      $old_board_rels = LsDoctrineQuery::create()
        ->from('Relationship r')
        ->leftJoin('r.Entity1 e1')
        ->leftJoin('e1.Person p')
        ->whereIn('r.id', $this->old_board_rel_ids)
        ->execute();
  
      foreach ($old_board_rels as $rel)
      {
        //only update if old board relationship is current but board member isn't on current roster
        if ($rel->is_board && $rel->is_current && !$rel->is_executive && !in_array($rel->entity1_id, $current_board_ids))
        {
          //maybe the board member has no recent Form 4s, so check the recent filings
          $matched = false;
          
          foreach ($this->filings as $filing)
          {
            if (preg_match_all($rel->Entity1->Person->getNameRegex(), $filing['doc']->text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE))
            {
              $matched = true;
              break;
            }        
          }
          
          if (!$matched)
          {
            $this->printDebug("~ Previously existing board relationship no longer current: " . $rel);
            $rel->is_current = false;
            $rel->save();
          }
        }   
        
        /* 
          executive relationships can't have their is_current field updated because
          not all executives from Form 4s appear on the annual filings!
        */
      }   
    }
    
    
    //add all filings as references for the company
    foreach ($this->filings as $filing)
    {
      //save source
      $this->entity->addReference(
        $filing['url'], 
        null, 
        null, 
        $filing['name'], 
        null, 
        $filing['date']
      );   
    }
    $url = "http://www.sec.gov/cgi-bin/browse-edgar?action=getcompany&CIK=" . $this->entity->sec_cik . "&dateb=&owner=exclude&count=40";
    $this->entity->addReference($url,null,null,'SEC EDGAR filings',null,null);
  }
  
  public function isAlreadyScraped($company)
  {
    //repeat mode overides log
    if ($this->repeat_mode)
      return false;

    //check scraper_meta for scraping log for this company and year
    $year = max($this->years);
    $sql = 'SELECT COUNT(*) FROM scraper_meta WHERE scraper = ? AND namespace IN (?, ?) AND predicate = ? AND value = ?';
    $stmt = $this->db->execute($sql, array(
      get_class($this),
      $this->log_namespace,
      'nodata',
      $company->id,
      $year
    ));

    return $stmt->fetch(PDO::FETCH_COLUMN);
  }

  public function logCompany($company, $empty=false)
  {
    $year = max($this->years);
    $namespace = $empty ? 'nodata' : $this->log_namespace;

    //check for existing log entry
    $q = LsDoctrineQuery::create()
      ->from('ScraperMeta s')
      ->where('s.scraper = ?', get_class($this))
      ->andWhere('s.namespace = ?', $namespace)
      ->andWhere('s.predicate = ?', $company->id)
      ->andWhere('s.value = ?', $year);
      
    if ($q->count())
      return;   

    $meta = new ScraperMeta;
    $meta->scraper = get_class($this);
    $meta->namespace = $namespace;
    $meta->predicate = $company->id;
    $meta->value = $year;
    $meta->save();

    $this->printDebug("Logged complete scrape of " . $this->entity->name);
  }
    
 /*
  public function getProxyUrls($years)
  {
    $url = "http://www.sec.gov/cgi-bin/browse-edgar?action=getcompany&CIK=" . $this->entity->sec_cik . "&type=def+14a&dateb=&owner=exclude&count=40";
    $years = implode('|',$years);
    $proxy_urls = array();
    if (!$this->browser->get($url)->responseIsError())
    {
      $re = '/href."(\/Archives\/edgar\/data\/\d*\/\d*)\/[^"]*?" id..documentsbutton.>.*?td>(' . $years . ')./isu';
      $text = $this->browser->getResponseText();  		    
  		$matched = preg_match_all($re,$text,$matches, PREG_SET_ORDER);
  		if ($matched > 0)
  		{
  		  foreach($matches as $match)
  		  {
  		    $proxy_urls[] = array('url' => 'http://sec.gov' . $match[1] . '/ddef14a.htm', 'year' => $match[2]);

  		  }
      } 
    }
    return $proxy_urls;
  }
  */
  
  public function getForm4Data($url_arr)
  {
    $url = $url_arr['xmlUrl'];

    if (!$this->browser->get($url)->responseIsError())
    {
      $xml = $this->browser->getResponseXml();
      $results = $this->parseForm4($xml);

      //make sure this is an entity with ownership in our given entity
      if (strpos($results['personCik'], trim($this->entity->sec_cik)) !== false || stristr($results['corpCik'], trim($this->entity->sec_cik)) === false)
      {
        return null;
      }
      else
      {
        $results['xmlUrl'] = $url;
        $results['htmlUrl'] = $url_arr['htmlUrl'];
        return $results;
      }
    }
  }
  
  /*
    full text EDGAR search for form 4s for $corp_cik
    hits up to $lim search index pages (10 results on each page)
    only adds urls of form 4s to results array for people whose cik identifiers have not already been found
  `returns array of form 4 urls
  */
  
  public function getForm4Urls()
  {
    //to check out the page, an example url: http://searchwww.sec.gov/EDGARFSClient/jsp/EDGAR_Query_Result.jsp?queryString=&queryForm=Form4&isAdv=1&queryCik=876437&numResults=100#topAnchor
    
    $types = array('Form3', 'Form4');
    $form4_urls = array();
    $unique_ciks = array();
    
    foreach ($types as $type)
    { 
      $search_pages = 0;
      $next_page = true;
      $url = 'http://searchwww.sec.gov/EDGARFSClient/jsp/EDGAR_MainAccess.jsp?search_text=*&sort=Date&formType=' . $type . '&isAdv=true&stemming=true&numResults=100&queryCik=' . $this->entity->sec_cik . '&numResults=10&fromDate=01/01/' . $this->ownership_start_year . '&toDate=01/01/2015';

      $this->printDebug("Form 3/4 search url: " . $url);
    
      //loop through search results
      while ($next_page == true && $search_pages < $this->search_depth)
      {
        if (!$this->browser->get($url)->responseIsError())
        {
          $selector = $this->browser->getResponseDomCssSelector();
          $text = $this->browser->getResponseText();
          $rows = $selector->matchAll('tr')->getNodes();
  
          for ($i=0; $i < count($rows); $i++)
          {
            $row = new sfDomCssSelector(array($rows[$i]));
            $links = $row->matchAll('a')->getNodes();
            $person_cik = null;
  
            foreach ($links as $link)
            {
              if ($link->getAttribute('name') == 'cikSearch')
              {
                $cik = trim($link->textContent);
  
                //break if it's the same as corp's cik
                if ($cik != $this->entity->sec_cik) 
                {
                  $person_cik = $cik;
                  break;
                }
              }
            }
            
            if (!$person_cik) 
              continue;
           
            if (!in_array($person_cik, $unique_ciks))
            {
              $prev_row = new sfDomCssSelector($rows[$i-1]);
  
              if (!is_object($href = $prev_row->matchSingle('a')->getNode())) 
                continue;
  
              $href = $href->getAttribute('href');
              
              //if not an xml doc, grab the parent filing underneath the listing
              if (!stripos($href,'.xml'))
              {
                $next_row = new sfDomCssSelector($rows[$i+2]);
  
                if (!is_object($href = $next_row->matchSingle('a')->getNode())) 
                  continue;
                  
                if (trim($href->getAttribute('title')) == 'Parent Filing')
                  $href = $href->getAttribute('href');
                else 
                  continue;
              }
              
              if (preg_match("/\'([^\']+)\'/", $href, $matches))
              {
                $unique_ciks[] = $person_cik;
                $u = str_replace('xslF345/', '', $matches[1]);
  
                if ($u != $matches[1])
                {
                  $form4_urls[] = array('xmlUrl' => $u, 'htmlUrl' => $matches[1]);
                }
              }
            }         
          }
          
          $next_page = false;  
          
          $links = $selector->matchAll('.clsBlueBg')->getNodes();
          
          foreach ($links as $link)
          {
            if ($link->getAttribute('title') == "Next Page")
            {
              $url = "http://searchwww.sec.gov" . $link->getAttribute('href');
              $next_page = true;
            }
          }  
        }
        
        $search_pages++;
      }
    }

    return $form4_urls;
  }
  
  public function parseForm4($xml)
  {
    $results = array();

    //form type
    $type = trim($xml->documentType);    
    $results['formName'] = ($type == '3') ? 'Form 3' : 'Form 4';

		//person & org
		$results['corpCik'] = trim($xml->issuer->issuerCik);
		$results['personCik'] = trim($xml->reportingOwner->reportingOwnerId->rptOwnerCik);
		$results['personName'] = trim($xml->reportingOwner->reportingOwnerId->rptOwnerName);
		$results['signatureName'] = trim($xml->ownerSignature->signatureName);

		//sometimes /CT/ (or /NY, /VA, etc) appears at the end of the person's name
		$results['personName'] = preg_replace('/\/\p{L}+\/?$/','',$results['personName']);

    //if Inc, LLC, or Trust appears in owner name, it's an org
    if (preg_match('#(^| )(inc|llc|llp|trust|corp|group|holdings|company|limited|ltd|fund)($| |\.|,|/)#i', $results['personName']))
    {
      $results['primaryExt'] = 'Org';
      $org = new Entity;
      $org->addExtension('Org');
      $org->name = $results['personName'];
      $results['org'] = $org;
      $results['parsedName'] = $results['personName'];
    }
    else    
    {
      $results['primaryExt'] = 'Person';
      $arr = $this->parseForm4Name($results['personName'], $results['signatureName']);
      $results['person'] = $arr[0];
      $results['parsedName'] = $arr[1];
      $results['regexName'] = $arr[2];
    }
		

		//$results['corpName'] = 
		//$results['corpCik'] = 
		//$results['corpSymbol'] = 
		
		//date
		$results['date'] = trim($xml->ownerSignature->signatureDate);
		
	  //address
		$address = array();
		$address_raw = (array) $xml->reportingOwner->reportingOwnerAddress;
		$address['street1'] = trim($address_raw['rptOwnerStreet1']);
		$address['street2'] = trim($address_raw['rptOwnerStreet2']);
		$address['city'] = trim($address_raw['rptOwnerCity']);
		$address['state'] = trim($address_raw['rptOwnerState']);
		$address['postal'] = trim($address_raw['rptOwnerZipCode']);
		$results['address'] = $address;


		//position
		$results['isDirector'] = trim($xml->reportingOwner->reportingOwnerRelationship->isDirector);

		if (strtolower($results['isDirector']) == 'true')
		{
		  $results['isDirector'] = '1';
		}
		else if (strtolower($results['isDirector']) == 'false')
		{
		  $results['isDirector'] = '0';
		}

		$results['officerTitle'] = trim($xml->reportingOwner->reportingOwnerRelationship->officerTitle);
		$results['otherText'] = trim($xml->reportingOwner->reportingOwnerRelationship->otherText);


    //ownership
		$results['isTenPercentOwner'] = trim($xml->reportingOwner->reportingOwnerRelationship->isTenPercentOwner);

		if (strtolower($results['isTenPercentOwner']) == 'true')
		{
		  $results['isTenPercentOwner'] = '1';
		}
		else if (strtolower($results['isTenPercentOwner']) == 'false')
		{
		  $results['isTenPercentOwner'] = '0';
		}
		
    
    //financial info - just a placeholder
    //$results['shares_post'] = trim($xml->nonDerivativeTable->nonDerivativeTransaction->postTransactionAmounts->sharesOwnedFollowingTransaction->value);
    return $results;
  }

  
  public function parseForm4Name($name, $signatureName=null)
  {
    $offset = 0;
    $matched = preg_match('/^O \p{L}/',$name,$matches);
    if ($matched)
    {
      $name = "O'" . substr($name,2);
    }

    //use signatureName to determine what order the name is in
    $flatName = false;

    if ($signatureName)
    {
      $nameParts = explode(' ', strtolower($name));
      $signatureNameParts = explode(' ', strtolower($signatureName));
      
      if ($nameParts[0] == $signatureNameParts[0])
      {
        $flatName = true;
      }
    }
    
    if ($flatName)
    {
      $person = PersonTable::parseFlatName($name);
    }
    else
    {
      $re = '/^(de|du|von|van|di|du|st|del|da)\s+((la|de|der)\s+)?/isu';
      $matched = preg_match($re,$name,$matches);

      if ($matched)
      {
        $offset = strlen($matches[0]);
      }

      $split = strpos($name," ",$offset);
      $last = substr($name,0,$split);
      $rest = substr($name,$split);
      $name = $last . "," . $rest;
      $person = PersonTable::parseCommaName($name);
    }
    
    $name = $person->getFullName();
    $regex = $person->getNameRegex();
    
    return array($person, $name, $regex);
  } 
  
  
  public function getFilings()
  {
    $this->recent_filing_date = '';
    $this->filings = array();

    foreach ($this->annual_filing_types as $type => $name)
    {
      $type = urlencode($type);
      $url = "http://www.sec.gov/cgi-bin/browse-edgar?action=getcompany&CIK=" . $this->entity->sec_cik . "&type=" . $type . "&dateb=&owner=exclude&count=40";
      
      $this->browser->get($url);
      $this->printDebug('ANNUAL FILING SEARCH URL: ' . $url);

      $text = $this->browser->getResponseText();
      $re = '/<tr>\s*<td nowrap..nowrap.>([^<]*?)<.td>\s*<td[^>]*?><a.href..(.*?)".*?>.*?<.a><.td>\s*<td[^>]*?>.*?<.td>\s*<td>((' . implode('|', $this->years) . ').*?)</is';
      preg_match_all($re, $text, $matches, PREG_SET_ORDER);
      if (count($matches))
      {
        $this->printDebug(count($matches) . " possible annual filings found...");

        foreach ($matches as $match)
        {
          $date = $match[3];
          
          if (strtotime($this->years[0] . '-01-01') > strtotime($date))
            continue;
  
          $url = 'http://sec.gov' . $match[2];
  
          if ($this->browser->get($url)->responseIsError())
            continue;
  
          $text = $this->browser->getResponseText();
          $size = ($type == 's-') ? 5 : 6;        
          $re = '/scope..row.>1<.td>\s*<td.*?>(.*?)<.td>.*?<a\s*href..(.*?)">.*?>(\d{' . $size . ',})<.td>\s*<.tr>/is';
  
          if (preg_match_all($re, $text, $matches2, PREG_SET_ORDER))
          {
            $url = 'http://sec.gov' . $matches2[0][2];
            
            if ($text = $this->browser->get($url)->getResponseText())
            {
              $this->filings[] = array(
                'url' => $url,
                'name' => $name,
                'date' => $date,
                'doc' => new LsSecDoc($text)
              );

              $this->recent_filing_date = (strtotime($date) > strtotime($this->recent_filing_date)) ? 
                $date : $this->recent_filing_date;                      
    
              $this->printDebug("Found recent filing: " . $url);
              
              break;
            }              
          }
        }
      }
    }     
  }
  
  
  //gets and sets most recent filing of requested type (def-14a, s-4, etc)
  
  public function getFilingUrl($doc_types)
  { 
    $years = implode('|', $this->years);
    $this->filing_date = null;
    $this->filing_url = null;
    $this->filing_name = null;

    foreach ($doc_types as $type)
    {
      $type = urlencode($type);
      $url = "http://www.sec.gov/cgi-bin/browse-edgar?action=getcompany&CIK=" . $this->entity->sec_cik . "&type=" . $type . "&dateb=&owner=exclude&count=40";
      $re = '/<tr>\s*<td nowrap..nowrap.>([^<]*?)<.td>\s*<td[^>]*?><a.href..(.*?)".*?>.*?<.a><.td>\s*<td[^>]*?>.*?<.td>\s*<td>((' . $years . ').*?)</is';
      
      $this->browser->get($url);
      $this->printDebug('SEARCH URL: ' . $url);

      $text = $this->browser->getResponseText();
      $matched = preg_match_all($re, $text, $matches, PREG_SET_ORDER);

      if ($matched)
      {
        $date = $matches[0][3];

        $url = 'http://sec.gov' . $matches[0][2];

        if (!$this->browser->get($url)->responseIsError())
        {
          $re = '/scope..row.>1<.td>\s*<td.*?>(.*?)<.td>.*?<a\s*href..(.*?)">.*?>(\d{6,})<.td>\s*<.tr>/is';
          $text = $this->browser->getResponseText();
          $matched = preg_match_all($re, $text, $matches, PREG_SET_ORDER); 

          if ($matched)
          {
            if (!$this->filing_date || strtotime($date) > strtotime($this->filing_date))
            {
              $this->filing_date = $date;
              $this->filing_url = 'http://sec.gov' . $matches[0][2];
              $this->filing_name = $matches[0][1];
              
              $this->printDebug("Found recent filing: " . $this->filing_url);
            }
          } 
        }
      }
    }
    
    return $this->filing_url;
  }
  
  
  /*
    imports person data
  */
  
  private function importPerson($person_arr)
  {
    $p = $person_arr['person'];
    $p->addExtension('BusinessPerson');
    $p->sec_cik = $person_arr['personCik'];    
    $p->save();
    
    //save source info
    $p->addReference(
      $person_arr['xmlUrl'], 
      null, 
      null,
      $this->entity->name . ' ' . $person_arr['formName'],  
      null, 
      $person_arr['date']
    );
    
    if (isset($this->filing_url) && isset($this->filing_name))
    {
      $p->addReference(
        $this->filing_url, 
        null, 
        null,
        $this->entity->name . " " . $this->filing_name, 
        null,
        $this->filing_date
      );
    }
    
    $this->printDebug("+ Created new person with SEC CIK " . $person_arr['personCik'] . ": " . $person_arr['parsedName']);

    return $p;
  }
  
  private function importAddress($address_arr, $person, $person_arr)
  {
    $a = new Address;
    $a->street1 = LsLanguage::nameize($address_arr['street1']);
    $a->street2 = LsLanguage::nameize($address_arr['street2']);
    $a->city = $address_arr['city'];
    $a->Category = Doctrine::getTable('AddressCategory')->findOneByName('Mailing');

    if ($state = AddressStateTable::retrieveByText($address_arr['state']))
    {
      $a->State = $state;
    }
    else
    {
      return;
    }

    $a->postal = $address_arr['postal'];
    
    if ($person->addAddress($a))
    {
      $person->save();
      $a->addReference(
        $person_arr['xmlUrl'], 
        null, 
        null, 
        $this->entity->name . ' ' . $person_arr['formName'],  
        null, 
        $person_arr['date']
      );
    }
  }


  private function importBoardRelationship($entityId, $person_arr, $title=null, $current=null)
  {
    if (!in_array($entityId, $this->old_board_entity_ids))
    {
      $rel = new Relationship;
      $rel->entity1_id = $entityId;
      $rel->entity2_id = $this->entity->id;
      $rel->setCategory('Position');
      $rel->is_current = $current;
      $rel->is_board = 1;
      $rel->is_executive = 0;

      //Form 3s let us set a start date
      if ($person_arr['formName'] == 'Form 3' && $person_arr['date'])
      {
        //filing date could be innacurate, so only use month
        $date = LsDate::formatFromText($person_arr['date']);
        $rel->start_date = preg_replace('/-\d\d$/', '-00', $date);
      }

      //keep title if it's a board title
      if (LsArray::inArrayNoCase($title, PositionTable::$boardTitles))
      {
        $rel->description1 = $title;
      }
      
      $rel->save();

      //save sources
      $rel->addReference(
        $person_arr['xmlUrl'], 
        null,
        null, 
        $this->entity->name . ' ' . $person_arr['formName'], 
        null, 
        $person_arr['date']
      );
      
      if (isset($this->filing_url) && isset($this->filing_name))
      {      
        $rel->addReference(
          $this->filing_url, 
          null, 
          null, 
          $this->entity->name . " " . $this->filing_name, 
          null, 
          $this->filing_date
        );
      }

      $this->printDebug("+ Relationship created: " . $rel->id . " (Board Member)");      
    }
  }


  private function importExecutiveRelationship($entityId, $person_arr, $title=null, $current=null)
  {
    if (!in_array($entityId, $this->old_exec_entity_ids))
    {
      $rel = new Relationship;
      $rel->entity1_id = $entityId;
      $rel->entity2_id = $this->entity->id;
      $rel->setCategory('Position');
      $rel->is_current = $current;
      $rel->is_board = 0;
      $rel->is_executive = 1;
      $rel->description1 = $title;

      //Form 3s let us set a start date
      if ($person_arr['formName'] == 'Form 3' && $person_arr['date'])
      {
        //filing date could be innacurate, so only use month
        $date = LsDate::formatFromText($person_arr['date']);
        $rel->start_date = preg_replace('/-\d\d$/', '-00', $date);
      }

      $rel->save();

      //save sources
      $rel->addReference(
        $person_arr['xmlUrl'], 
        null,
        null, 
        $this->entity->name . ' ' . $person_arr['formName'], 
        null, 
        $person_arr['date']
      );
      
      if (isset($this->filing_url) && isset($this->filing_name))
      {      
        $rel->addReference(
          $this->filing_url, 
          null, 
          null, 
          $this->entity->name . " " . $this->filing_name, 
          null, 
          $this->filing_date
        );
      }

      $this->printDebug("+ Relationship created: " . $rel->id . " (" . $rel->description1 . ")");      
    }
  }
  

  /*
    given person id, corp id, position (as in 'Director'), and url, creates a relationship
    assumes relationship in 'Position' category
  */
  
  private function importRelationship($person, $position=null, $current=null, $is_board, $person_arr)
  {
    $q = LsDoctrineQuery::create()
      ->from('Relationship r')
      ->leftJoin('r.Position p')
      ->where('r.entity1_id = ?', $person->id)
      ->addWhere('r.entity2_id = ?', $this->entity->id)
      ->addWhere('r.category_id = ?', RelationshipTable::POSITION_CATEGORY)
      ->orderBy('r.id ASC');
          
    if ($is_board)
    {
      $q->addWhere('p.is_board = 1');
    }
    else
    {
      $q->addWhere('p.is_executive = 1');

      //$q->addWhere('r.description1 = ?', $position)
      //  ->addWhere('p.id IS NOT NULL');
    }
    
    $count = $q->count();
    
    if ($count == 0)
    {
      //if relationship doesn't already exist, create it
      $rel = new Relationship;
      $rel->entity1_id = $person->id;
      $rel->entity2_id = $this->entity->id;
      $rel->setCategory('Position');
      $rel->is_current = $current;

      if ($is_board)
      {  
        $rel->is_board = 1;
        $rel->is_executive = ($position != 'Chairman' && $position != 'Director') ? 1 : 0;
        $rel->description1 = null;  //better not to set description if is_board = 1
      }
      else
      {
        $rel->description1 = $position;
        $rel->is_executive = 1;
        $rel->is_board = 0;
      }

      $rel->save();

      //save sources
      $rel->addReference(
        $person_arr['xmlUrl'], 
        null,
        null, 
        $this->entity->name . ' ' . $person_arr['formName'], 
        null, 
        $person_arr['date']
      );
      
      if (isset($this->filing_url) && isset($this->filing_name))
      {      
        $rel->addReference(
          $this->filing_url, 
          null, 
          null, 
          $this->entity->name . " " . $this->filing_name, 
          null, 
          $this->filing_date
        );
      }

      $this->printDebug("+ Relationship created: " . $rel->id . " (" . ($rel->is_board ? "Board Member" : $rel->description1) . ")");
    }    
    elseif ($count == 1)
    {
      //this part might be redundant because importRoster() updates expired board memberships at the end?

      if ($is_board)
      {
        //if one relationship exists, expire if necessary
        $rel = $q->fetchOne();
        
        if ($current == false && $rel->is_current)
        {
          $rel->is_current = false;
          $rel->save();
          
          $this->printDebug("Existing relationship no longer current: " . $rel);
        }
      }
    }    
    else
    {
      //if mmultiple existing relationships found, do nothing
      /*
      $rel = $q->fetchOne();

      if ($is_board)
      {
        //for board relationship...
        //if existing rel was last updated by a bot, we update it
        
        if (($current != $rel->is_current) && ($rel->last_user_id < 4))
        {
          $rel->is_current = $current;
          $rel->addReference($this->filing_url, null, $fields= array('is_current'), $this->entity->name . " " . $this->filing_name, null, $this->filing_date);
          $rel->save();
          $this->printDebug("Board relationship updated: " . $rel);
        }
      }
      else if ($current && !$rel->is_current && ($rel->last_user_id < 4))
      {
        $rel->is_current = $current;
        $rel->addReference($this->filing_url, null, $fields= array('is_current'), $this->entity->name . " " . $this->filing_name, null, $this->filing_date);
        $rel->save();
        $this->printDebug("Exec relationship updated: " . $rel);
      }
      */
    }
  }
  

   
  /*
     returns a person with a new name based upon the names in the two person objects passed to the function
     formPerson is given precedence; for the new Person to take values from proxyPerson, the values
     must be compatible with those in formPerson due to proxy name matching's messiness
  */
  
  private function mergePeople($proxyPerson,$formPerson)
  {
    $formPerson->name_first;
    $formPerson->name_last;
    $person = new Entity;
    $person->addExtension('Person');
    $person->name_first = $formPerson->name_first;
    $person->name_middle = $formPerson->name_middle;
    $person->name_last = $formPerson->name_last;
    $person->name_nick = $formPerson->name_nick;
    $person->name_prefix = $formPerson->name_prefix;
    $person->name_suffix = $formPerson->name_suffix;
    
    $compatible = false;
  
    if (!$proxyPerson->name_first or $proxyPerson->name_first == '')
    {
      return $person;
    }
            
    //check first name compatibility before doing anything
    
    if (stripos($proxyPerson->name_first,$formPerson->name_first) === 0 ||
                    stripos($formPerson->name_first,$proxyPerson->name_first) === 0)
    {
      
      if ($formPerson->name_middle == null || $formPerson->name_middle == '')
      {
        $compatible = true;
        $person->name_middle = $proxyPerson->name_middle;
        if (strlen($proxyPerson->name_first) > strlen($formPerson->name_first))
        {
          $person->name_first = $proxyPerson->name_first;
        }
      }
      
      //unclear whether this is a good idea
      else if (!$proxyPerson->name_middle || $proxyPerson->name_middle == '')
      {
        $compatible = true;
        if (strlen($proxyPerson->name_first) > strlen($formPerson->name_first))
        {
          $person->name_first = $proxyPerson->name_first;
        }
      }
      
      //check middle name compatibility if both exist
      else if (stripos($proxyPerson->name_middle,$formPerson->name_middle) === 0 ||
                      stripos($formPerson->name_middle,$proxyPerson->name_middle) === 0)
      {
        $compatible = true;
        if (strlen($proxyPerson->name_middle) > strlen($formPerson->name_middle))
        {
          $person->name_middle = $proxyPerson->name_middle;
        }
        if (strlen($proxyPerson->name_first) > strlen($formPerson->name_first))
        {
          $person->name_first = $proxyPerson->name_first;
        }
      }
      
    }
    
    //if names have proven compatible, then check generational suffixes (Jr, Sr etc)
    if ($compatible == true)
    {
      $form_suffixes = explode(' ', $formPerson->name_suffix);
      $form_generationals = array_intersect($form_suffixes,LsLanguage::$generationalSuffixes);
      $proxy_suffixes = explode(' ', $proxyPerson->name_suffix);
      $common_generationals = array_intersect($form_generationals,$proxy_suffixes); 
      //if there are no generationals in the form 4 name, go ahead and grab prefixes, suffixes, etc from proxy name
      if (count($form_generationals) == 0 || $form_generationals = $common_generationals)
      {        
        if (strlen($proxyPerson->name_nick) > strlen($formPerson->name_nick))
        {
          $person->name_nick = $proxyPerson->name_nick;
        }
        if (strlen($proxyPerson->name_prefix) > strlen($formPerson->name_prefix))
        {
          $person->name_prefix = $proxyPerson->name_prefix;
        }
        if (strlen($proxyPerson->name_suffix) > strlen($formPerson->name_suffix))
        {
          $person->name_suffix = $proxyPerson->name_suffix;
        }
      }            
    }   
    return $person; 
  }
  
  static function parseDescriptionStr($str, $entity=null)
  {
  	$descriptions = array();
  	$remains = array();
  	
  	//cleanup text to be parsed
  	$str = trim($str);
    $str = preg_replace('/(?<!=\s)\.(?!=\s)/', '', $str);
  	$str = str_replace('.', ' ', $str);
  	$str = preg_replace('/\s{2,}/', ' ', $str);
    $str = preg_replace('/\s+,(?=\s)/', ',', $str);
      	
  	/*
  	if ($entity)
  	{
      $name_re = LsString::escapeStringForRegex($entity->name);
      $str = preg_replace('/\b' . $name_re . '\b/isu', '', $str);
      
      if ($entity->ticker)
      {
        $tick_re = LsString::escapeStringForRegex($entity->ticker);
        $str = preg_replace('/\b' . $tick_re . '\b/isu', '', $str);
      }
    }
  	*/
  	
  	
  	//don't parse if there's more than one separator
    $num = 0;
    $patterns = array('/\s&\s/', '/,/', '/;/', '/\band\b/i');

    foreach ($patterns as $pattern)
    {
      if (preg_match($pattern, $str))
      {
        $num++;
      }
    }
  	
  	if ($num > 1)
  	{
  	  return array($str);
  	}
  	
  	//split by commas
  	$parts = preg_split('/,|;|\band\b|\s&\s/', $str, -1, PREG_SPLIT_NO_EMPTY);

  	foreach ($parts as $part)
  	{
  		$part = trim($part);
  		$part = preg_replace('/\s{2,}/', ' ', $part);
  
  		//abbreviation replacements
  		$part = preg_replace('/( |^)(\w) (\w) (\w)( |$)/', '\2\3\4', $part);
  		$part = preg_replace('/(Interim|Acting|Incoming) /i', '', $part);
  		$part = preg_replace('/Sr /i', 'Senior ', $part);
  		$part = preg_replace('/Chf /i', 'Chief ', $part);
  		$part = preg_replace('/( |^)V( |$)/i', ' Vice ', $part);
  		$part = preg_replace('/( |^)VP( |$)/i', ' Vice President ', $part);
  		$part = preg_replace('/( |^)VC( |$)/i', ' Vice Chairman ', $part);
      $part = preg_replace('/( |^)Chr( |$)/i', ' Chairman ', $part);
      $part = preg_replace('/( |^)Ofcr( |$)/i', ' Officer ', $part);
  		$part = preg_replace('/( |^)Vice P( |$)/i', ' Vice President ', $part);
  		$part = preg_replace('/( |^)(Ex|Exec)( |$)/i', ' Executive ', $part);
  		$part = preg_replace('/( |^)EVP( |$)/i', ' Executive Vice President ', $part);
  		$part = preg_replace('/( |^)(Off|Offic|Offcr)( |$)/i', ' Officer ', $part);
  		$part = str_replace('Gen ', 'General ', $part);
  		$part = preg_replace('/( |^)(Op|Oper) /', ' Operating ', $part);
  		$part = preg_replace('/( |^)(Bd|Brd)( |$)/i', ' Board ', $part);
  		$part = preg_replace('/of Board/i', ' of the Board', $part);
  		$part = preg_replace('/( |^)COB( |$)/i', ' Chairman of the Board ', $part);
  		$part = preg_replace('/( |^)(Pres|Prs|Presid|Prsdt|Prsdnt)( |$)/i', ' President ', $part);
  		$part = preg_replace('/( |^)Admin( |$)/i', ' Administrative ', $part);
      $part = preg_replace('/( |^)Info( |$)/i', ' Information ', $part);
  		$part = preg_replace('/\bComm\b/i', 'Committee', $part);
  		$part = preg_replace('/\bInc\b/i', '', $part);
  		$part = preg_replace('/( |-|^)(Ch|Chm|Chmn|Chrm|Chrmn|Chair|Chairmain|Chariman)( |$)/i', '\1Chairman ', $part);
  		$part = preg_replace('/(Sec|Secr|Secy|Secretar|Secreta)( |$)/i', 'Secretary ', $part);
  		$part = str_replace('Vice-', 'Vice ', $part);
  		$part = preg_replace('/( |^)Non /i', ' Non-', $part);
  		$part = preg_replace('/\bCompl\b/i','Compliance',$part);
  		$part = str_ireplace('of Advisory', 'of the Advisory', $part);
  		$part = preg_replace('/Advisory (Panel|Council)/i', 'Advisory Board', $part);
  		$part = str_ireplace('Independent ', '', $part);
  		$part = str_ireplace('Lead ', '', $part);
  		$part = str_ireplace('Corporate ', '', $part);
  		$part = str_ireplace('Outside ', '', $part);
  		$part = str_ireplace('Non-interested', '', $part);
  		$part = str_ireplace('Interested', '', $part);
  		$part = str_replace('Main ', '', $part);
  		$part = str_ireplace('Presiding ', '', $part);
  		$part = str_ireplace('Founding ', '', $part);
      $part = str_ireplace('Acctg', 'Accounting', $part);
  		$part = str_ireplace('Chairperson', 'Chairman', $part);
  		$part = str_ireplace('Chairwoman', 'Chairman', $part);
  		$part = str_ireplace("Gen'l",'General',$part);
  		$part = trim($part);
  		$part = preg_replace('/\s{2,}/', ' ', $part);
      $position = array('description' => null, 'note' => array());
      
      if (LsArray::inArrayNoCase($part, PositionTable::$businessPositions))
      {  		  
        $descriptions[] = $part;
      }     
  	}
  	
  	if (!count($descriptions))
  	{
  	  $descriptions[] = $str;
  	}

  	return $descriptions;  
  }

}