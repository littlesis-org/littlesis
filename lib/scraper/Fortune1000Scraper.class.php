<?php

class Fortune1000Scraper extends Scraper
{
	private $companies = array();
	private $year = 2008;
	private $list = null;
	
  public function setYear($year)
  {
    $this->year = $year;
  }	
  
  private function setList()
  {
    $name = 'Fortune 1000 (' . $this->year . ')';
    $list = Doctrine_Query::create()
          ->from('LsList L')
          ->where('L.name = ?', $name)
          ->fetchOne();
    
    //if thlis year's fortune list doesn't already exist, create it    
    if (!$list)
    {
      try
      {
        $this->db->beginTransaction();
        $list = new LsList;
        $list->name = $name;
        $list->description = "Fortune Magazine's list of the 1000 US companies with the largest published revenue figures.";
        $list->is_ranked = 1;
        $list->save();
        $this->list = $list;
        
        $ref = new Reference;
        $ref->object_model = 'LsList';
        $ref->object_id = $list->id;
        $ref->fields = 'name, description, is_ranked';
        $ref->source = 'http://money.cnn.com/magazines/fortune/fortune500/' . $this->year . '/full_list/';
        $ref->name = 'Fortune Magazine Online';
        $ref->save();
        
        if (!$this->testMode)
        {
          $this->db->commit();
        }
      }
       catch (Exception $e)
      {
				$this->db->rollback();		
        throw $e;
      }
    }
    else
    {
      $this->list = $list;
    }
  
  }
	
	public function execute()
	{
	   if (!$this->safeToRun('fortune1000'))
    {
      $this->printDebug('script already running');
      die;
    }
	  $this->setList();
		switch ($this->year) 
		{
  		case 2008:
  		  $this->getCompanyList2008();
  		  echo "list imported\n";
  	}

	  while ($company = current($this->companies))
	  {
      try {
				$this->db->beginTransaction();		
				$company['name'] = OrgTable::stripNamePunctuation($company['name']);
  	    $rank = $company['rank'];

        $existing = Doctrine_Query::create()
          ->from('Entity e')
          ->where('name = ?', $company['name']);
          
        if($existing->count() == 0)
        {
          switch ($this->year)
          {
            case 2008:          
              $corp = $this->getCompany2008($fortune_id =$company['fortune_id'], $name = $company['name'],
                                                                         $revenue = $company['revenue']);
          }
        }
        else
        {
          //echo "corp already exists\n";
          $corp = $existing->fetchOne();
        }
        if ($corp)
        {	    
          //two corps can have the same rank, so searches for duplicate entity_id and rank
    	    $rank_existing = Doctrine_Query::create()
            ->from('LsListEntity L')
            ->where('list_id = ? and rank = ? and entity_id = ?', array($this->list->id, $rank, $corp->id))
            ->count();
          if ($rank_existing == 0)
          {
            $listentity = new LsListEntity;
            $listentity->entity_id = $corp->id;
            $listentity->list_id = $this->list->id;
            $listentity->rank = $rank;
            $listentity->save(); 
            echo "$rank $corp->name (saved)\n";
          }
          else
          {
            echo "$rank $corp->name (already saved)\n";
          }
        }
        unset($corp);
        
        if (!$this->testMode)
        {
          $this->db->commit();
        }
	    }
    	catch (Exception $e)
    	{
				$this->db->rollback();		
        throw $e;
			}
			next($this->companies);
	  }
	}

	//Fortune changes their html every year, so separate functions necessary for each year
  private function getCompanyList2008()
  {
    $sections= array('index', '101_200', '201_300', '301_400', '401_500', '501_600', '601_700', '701_800', '801_900', '901_1000');
    $companies = array();
    foreach ($sections as $section)
    {
      $url = 'http://money.cnn.com/magazines/fortune/fortune500/2008/full_list/' . $section . '.html';
      
    	try
    	{
    	  if (!$this->browser->get($url)->responseIsError())
    		{ 		
    		  $nodes = $this->browser->getResponseDomCssSelector()->getNodes();
    		  $doc = new LsDomCssSelector($nodes);
  			  $text = $this->browser->getResponseText();
          
          $rows = $doc->matchAll('tr #tablerow')->getNodes();
          foreach($rows as $row) 
          { 
            $row = new LsDomCssSelector(array($row));
            $name_link = $row->matchSingle('a')->getNode();
            $name = trim($name_link->nodeValue);
            $link = $name_link->getAttribute('href');
            preg_match('/(\d+)\.html/',$link,$match);
            $id = trim($match[1]);         
            $cells = $row->matchAll('td')->getValues();
            $rank = trim($cells[0]);
            $revenue = trim($cells[2]);
            $companies[] = array('name' => $name, 'fortune_id' => $id, 'rank' => $rank, 'revenue' =>$revenue);
      		} 			  
    		}
    	}
    	catch (Exception $e)
    	{
				$this->db->rollback();		
				echo $e->getMessage() . "\n";
			}
    }
    
    $this->companies = $companies;  
  
  }
  
  
  private function getCompany2008($fortune_id, $name, $revenue)
  {
    
  	$url = 'http://money.cnn.com/magazines/fortune/fortune500/2008/snapshots/' . $fortune_id . '.html';

		if (!$this->browser->get($url)->responseIsError())
		{
			$doc = $this->browser->getResponseDomCssSelector();
			$text = $this->browser->getResponseText();

      $name = trim($name);
      $name = preg_replace('/\./','',$name);

 			$ticker = $doc->matchSingle('.getQuoteLink')->matchSingle('a')->getNode();
      
      $ticker = (is_object($ticker)) ? $ticker->textContent : null;

  		$unique_nodes = $doc->matchSingle('.snapUniqueData')->getNode()->childNodes;
      $website = 'http://' . trim($unique_nodes->item(21)->textContent);
      $telephone = trim($unique_nodes->item(17)->textContent);
      $address1 = trim($unique_nodes->item(12)->textContent);
      $address2 = trim($unique_nodes->item(14)->textContent);
      $address_raw = $address1 . ' ' . $address2;
      $industry = null;
      if (preg_match('/Industry\:\s*<.*?>(.*?)</isu',$text,$match))
      {
        $industry = trim($match[1]);
      }
      $corp = $this->importCompany($name, $ticker, $website, $address_raw, $telephone, $revenue, $url, $industry);
      return $corp;
  	}

	  else
		{
			//Error response (eg. 404, 500, etc)
  	  $log = fopen($this->logFile, 'a');
			fwrite($log, "Couldn't get " . $url . "\n");
			fclose($log);
		}
 
  }	
  
  //separate import method can be used for any year
  
  private function importCompany($name, $ticker, $website, $address_raw, $telephone, $revenue, $url, $industry)
  {
    
    $corp = new Entity;

    $corp->addExtension('Org');
    $corp->addExtension('Business');

    if ($ticker)
    {
      $corp->addExtension('PublicCompany');
      $corp->ticker = $ticker;
    }
    else
    {
      $corp->addExtension('PrivateCompany');
    }
    
    $corp->name = $name;
    $corp->revenue = LsNumber::formatDollarAmountAsNumber($revenue,1000000);
    $corp->website = $website;
    $modified = $corp->getAllModifiedFields();

    if ($address = $corp->addAddress($address_raw))
    {
      $addressModified = $address->getAllModifiedFields();
    }
    
    if ($telephone)
    {	
      $phone = $corp->addPhone($telephone);
      $phoneModified = $phone->getAllModifiedFields();
    }
    
    $corp->save();

    $corp->addReference($url, null, $modified, 'Fortune Magazine Online');

    if ($address)
    {
      $address->addReference($url, null, $addressModified, 'Fortune Magazine Online');
    }
    
    if ($phone)
    {
      $phone->addReference($url, null, $phoneModified, 'Fortune Magazine Online');
    }
    
    if ($industry)
    {
    
    }

    return $corp;
    
  }
  
}