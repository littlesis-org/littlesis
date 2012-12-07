<?php

class FedSpendingScraper extends Scraper{
  
  public static $baseFilingUrl = 'http://www.fedspending.org/fpds/fpds.php?database=fpds&detail=3&record_id=';
  public static $baseContractorUrl = 'http://www.fedspending.org/fpds/fpds.php?reptype=r&detail=2&sortby=f&datype=T&reptype=r&database=fpds&record_num=f500&parent_id=';

  protected $fedSpendingSearchUrl = 'http://www.fedspending.org/fpds/fpds.php';
  protected $years = array('2008','2007');
  protected $refreshDays = 30;
  protected $fedspendingURL = array( 'contracts' => 'http://www.fedspending.org/fpds/fpds.php',
                                     'grants' => 'http://www.fedspending.org/faads/faads.php', );
  
  static protected $commonFedSpendingBusinessVocabulary = array('Services', 'Partnership', 'Product', '"', 'l l c', '&amp;', 'Corp.', 'Const', 'Company', '/', 'Coprorate', '/snecma Jv', 'Co.');
  protected $_limit = 100;
  protected $_count = 0;
  protected $_org_limit = 10;
  protected $_round = 'fortune_04_06';
  
  public function setRound($round)
  {
    $this->_round = $round;  
  }
  
  public function setFilingLimit($lim)
  {
    $this->_filing_limit = $lim;
  }
  
  public function setOrgLimit($lim)
  {
    $this->_org_limit = $lim;  
  }
  
  public function execute(){
    
    if (!$this->safeToRun('fedspending'))
    {
      $this->printDebug('script already running');
      die;
    }
    
    $orgs = $this->getBusinessQuery()->execute();
    
    if($orgs->count()){
      //loop through orgs
      foreach ($orgs as $count=> $org)
      {     
           
        $this->printDebug("\n***** Searching Organization: " . $org->getName() . " *****");
        $this->printDebug("Memory used: " . LsNumber::makeBytesReadable(memory_get_usage()) );
        $this->printDebug("Now: ". date('l jS \of F Y h:i:s A') );
        /*if ($this->hasMeta($org->id, 'refresh_time') && time() < (int)$this->getMeta($org->id, 'refresh_time') )
        {
          $this->printDebug("Refresh time: " . date('l jS \of F Y h:i:s A', (int)$this->getMeta($org->id, 'refresh_time') ) );
          $this->printDebug($org->name . " already scraped; skipping");
          //$this->db->rollback();
          //continue;
        }*/
        
        $this->getFedSpendingData($org);

        if ($this->testMode) 
        { 
          continue; 
        }    
        if ($this->_count >= $this->_filing_limit)
        {
          $this->printDebug('filing limit reached');
          die;
        }  
        $refresh_days = time() + ($this->refreshDays * 24 * 60 * 60);
        $this->saveMeta($this->_round, 'last_processed',$org->id);       
        $this->printDebug( $org->name . ": OK");
                  

      }
    }
    else{
      $this->printDebug( "No businesses found on database" );        
    }
  }
  
  
  function setLimit($limit){
    $this->limit = $limit;
  }
  
  
  function setYear($years){
    if(strlen($years) ){
      $this->years = explode(",", $years);
    }
  }
  
  
  function getFedSpendingData($org)
  {  
    $this->printDebug("Search Query: " . $org->name);  
    $company = $this->findCompany($org);
    
    if($company)
    {      
      try
      {
        $this->db->beginTransaction();
        $company->save();
        $this->db->commit();
      }
      catch (Exception $e)
      {
        $this->db->rollback();    
        throw $e;
      } 
      
      $this->printDebug("Getting transaction list");      
      $this->getTransactions($company);    
    }
    else
    {
      $this->printDebug("Company not found");  
    }
  }  
  
  private function findCompany($org)
  {
    $search_company_name = str_ireplace( LsLanguage::$punctuations, '', trim(implode(' ', array_udiff(explode(' ', ucwords(strtolower($org->name))), array_merge( self::$commonFedSpendingBusinessVocabulary, LsLanguage::$business, LsLanguage::$businessAbbreviations), 'strcasecmp'))));

    if (!$this->browser->get($this->fedSpendingSearchUrl, array('company_name' => $search_company_name, 'datype' => 'T', 'detail' => '0'))->responseIsError())
    {
      $response_url = $this->browser->getUrlInfo();
      $text = $this->browser->getResponseText();
      $this->printDebug("Search Result: http://" .  $response_url['host'] . $response_url['path']  . "?". $response_url['query']  );      
    }
    else{
      $this->printDebug("Couldn't get " . $this->fedSpendingSearchUrl );      
      return false;
    }
        
    preg_match_all('/parent_id=(\d+).+">(.+)<\/a>/', $text, $companies);

    
    foreach($companies[1] as $key=> $company_id){

      $result_company_name = html_entity_decode(str_ireplace( LsLanguage::$punctuations, '', trim(implode(' ', array_udiff(explode(' ', strtolower($companies[2][$key])), array_merge( self::$commonFedSpendingBusinessVocabulary, LsLanguage::$business, LsLanguage::$businessAbbreviations), 'strcasecmp')))));
      
      $org_name = str_ireplace( LsLanguage::$punctuations, '', trim(implode(' ', array_udiff(explode(' ', strtolower($org->name)), array_merge( self::$commonFedSpendingBusinessVocabulary, LsLanguage::$business, LsLanguage::$businessAbbreviations), 'strcasecmp'))));
      
      $this->printDebug("Matching: ". $org_name . " = " . $result_company_name);
    
      
      if( preg_match("/^$org_name/i" , $result_company_name) ){
        
        if($company = EntityTable::getByExtensionQuery('Org')->addWhere('org.fedspending_id= ? ', $company_id)->fetchOne())
        {
          $this->printDebug($result_company_name . " confirmed by ID (". $company_id . ")");
          $org->fedspending_id = $company_id;
          return $org;
        }
        
        else if($company = EntityTable::getByExtensionQuery('Org')->addWhere('LOWER(org.name) LIKE ?', '%'.strtolower($result_company_name)."%")->fetchOne())
        {
          $this->printDebug($result_company_name . " (". $company_id .")". " matches ". $org->name);
          $org->fedspending_id = $company_id;
          return $org;
        }
        
        else if($company = EntityTable::getByExtensionQuery('Org')->addWhere("LOWER(REPLACE( org.name, '-' , '')) LIKE ?", '%'. strtolower($result_company_name)."%")->fetchOne())
        {
          $this->printDebug($result_company_name . " (". $company_id .")". " matches ". $org->name);
          $org->fedspending_id = $company_id;
          return $org;
        }
        
      }      
    }
    return false;
  }
  
  
  private function getTransactions($org)
  {      
    $years = (array)$this->years;
    $text = null;
    foreach($years as $key => $year){
      
      if ($this->hasMeta($org->id, $year) && $this->getMeta($org->id, $year) )
      {
        $this->printDebug("Already scraped this year. Skipping..."); 
        continue;
      }
      
      if (!$this->browser->get($this->fedSpendingSearchUrl, array('fiscal_year' =>  $year, 'detail'=> '2' , 'datype' => 'T', 'parent_id' => $org->fedspending_id))->responseIsError())
      {
        $response_url = $this->browser->getUrlInfo();
        $this->printDebug("Year $year http://" .  $response_url['host'] . $response_url['path']  . "?". $response_url['query']  );            
        $text = $this->browser->getResponseText();
      }
      else
      {
        $this->printDebug("Couldn't get " . $this->fedSpendingSearchUrl );
        return false;
      }
      
      preg_match_all('/record_id=(\d+).+">(.+)<\/a>/', $text, $matched_ids);
      //print_r($matched_ids);
      foreach($matched_ids[1] as $key=> $fedspending_id){
        //$this->printDebug("Fedspending ID " . $fedspending_id );
        if ($this->_count >= $this->_filing_limit)
        {
          $this->printDebug('filings limit reached');
          return;
        }
        try
        {
          $this->db->beginTransaction();
          if (FedspendingFilingTable::getByFedspendingId($fedspending_id))
          {
            $this->printDebug("Filing #$fedspending_id exists");
            $this->db->rollback();
          }
          else
          {
            $get_details = $this->getTransactionDetails($org, $fedspending_id);
            if ($get_details === 'under_1000')
            {
              $this->db->rollback();
              break;
            }             
            else if ($get_details)
            {
              $this->_count++;
              $this->db->commit();
            }
            else
            {
              $this->db->rollback();
            }
          }
        }
        catch (Exception $e)
        {
          $this->db->rollback();    
          throw $e;
        } 
      }
      try
      {
        $this->db->beginTransaction();
        $this->saveMeta($org->id, $year, true);
        $this->db->commit();
      }
      catch (Exception $e)
      {
        $this->db->rollback();
        throw $e;
      }
    }

  }
    
  private function getTransactionDetails($org, $fedspending_id)
  {

    
    if (!$this->browser->get($this->fedSpendingSearchUrl, array('record_id' => $fedspending_id, 'datype' => 'X', 'detail' => '4'))->responseIsError())
    {
      $response_url = $this->browser->getUrlInfo();
      $response_url_str = "http://" .  $response_url['host'] . $response_url['path']  . "?". $response_url['query'];
      $this->printDebug("Transaction #$fedspending_id http://" .  $response_url['host'] . $response_url['path']  . "?". $response_url['query']  );      
      $text = $this->browser->getResponseText();
       $this->browser->setResponseText(iconv('ISO-8859-1', 'UTF-8', $this->browser->getResponseText()));
      $xml = $this->browser->getResponseXml();
    }
    else
    {
      $this->printDebug("Couldn't get " . $this->fedSpendingSearchUrl );
      return false;
    }
    
    
    $obligated_amount = $xml->xpath('/fedspendingSearchResults/data/record/amounts/obligatedAmount');
    $maj_agency_cat = $xml->xpath('/fedspendingSearchResults/data/record/purchaser_information/maj_agency_cat');    
    $contracting_agency = $xml->xpath('/fedspendingSearchResults/data/record/purchaser_information/contractingOfficeAgencyID');    
    $psc_cat = $xml->xpath('/fedspendingSearchResults/data/record/product_or_service_information/psc_cat');    
    $signed_date = $xml->xpath('/fedspendingSearchResults/data/record/contract_information/signedDate');    
    $descriptionOfContractRequirement = $xml->xpath('/fedspendingSearchResults/data/record/contract_information/descriptionOfContractRequirement');    

    $current_completion_date = $xml->xpath('/fedspendingSearchResults/data/record/contract_information/currentCompletionDate');    
    $state_code = $xml->xpath('/fedspendingSearchResults/data/record/principal_place_of_performance/stateCode');    


    $place_of_performance_congressional_district = $xml->xpath('/fedspendingSearchResults/data/record/principal_place_of_performance/placeOfPerformanceCongressionalDistrict');    

    
    foreach($obligated_amount as $key=> $dont_use)
    {      
      $gov_name = $this->cleanGovtBodyName($maj_agency_cat[$key]);
      $gov_agency = $this->getGovernmentBodyEntity($gov_name,$maj_agency_cat[$key]);
      
      if($gov_agency)
      {
        //$this->printDebug("Found existing Government Agency" . $gov_agency->name);                
      }
      else{
        $gov_name = $this->cleanGovtBodyName($maj_agency_cat[$key]);
        $this->printDebug($gov_name);
        $gov_agency = $this->addGovernmentBodyEntity($gov_name, $maj_agency_cat[$key]);
        $this->printDebug("Creating new Government Agency: ". $gov_agency->name);        
        $gov_agency->addReference(self::$baseContractorUrl . $org->fedspending_id, null, array('name','parent_id'), 'FedSpending.org');
      }
      
      if(!$gov_agency)
      {
        $this->printDebug("Error creating Government Agency");
        return false;
      }

      $sub_agency_name = $this->cleanGovtBodyName($contracting_agency[$key]);
      
      $sub_agency = $this->getGovernmentBodyEntity($sub_agency_name, $contracting_agency[$key]);
            
      if ($sub_agency_name == $gov_agency->name)
      {
        $sub_agency = $gov_agency;
      }

      
      if (!$sub_agency)
      {
        $sub_agency = $this->addGovernmentBodyEntity($sub_agency_name,$contracting_agency[$key],$gov_agency->id);
        $this->printDebug("Creating new sub-agency: " . $sub_agency->name);
        $sub_agency->addReference(self::$baseContractorUrl . $org->fedspending_id, null, array('name','parent_id'), 'FedSpending.org');

      }
      
      if(!$sub_agency)
      {
        $this->printDebug("Error creating Government Agency");
        return false;
      }
      
      
      try
      {
          
        $district = null;

        $state = explode(': ', $state_code[$key]);
        $federal_district = explode(': ', $place_of_performance_congressional_district[$key]);
        $state = $state[0];
        $federal_district = $federal_district[0];
                
        
        $filing = new FedspendingFiling;
        $filing->goods = $descriptionOfContractRequirement[$key];
        $filing->amount = abs($obligated_amount[$key]);
        
        if ($filing->amount < 1000)
        {
          $this->printDebug('amount under $1000, rolling back');
          return 'under_1000';        
        }

        //$this->printDebug("state: " . $state . " and district: " . $federal_district);
        if( $district = PoliticalDistrictTable::getFederalDistrict($state, $federal_district) )
        {   
          //$this->printDebug("found " . $district->id);
          $filing->District = $district;
        }
        elseif ( trim($state) && trim($federal_district) )
        {
          try {
            $district = PoliticalDistrictTable::addFederalDistrict($state, $federal_district);
            $this->printDebug("Adding District ".$state." #" . $district->id);
            $filing->District = $district;
          }
          catch (Exception $e){
            throw $e;
          }
        }
        
        $filing->fedspending_id = $fedspending_id;      
        $filing->start_date = LsDate::formatFromText($signed_date[$key]);
        $filing->end_date = LsDate::formatFromText($current_completion_date[$key]);
        
        $relationship = null;
        if($relationship = $org->getRelationshipsWithQuery( $sub_agency, RelationshipTable::TRANSACTION_CATEGORY)->fetchOne() )
        {          
          $relationship->addFedspendingFiling($filing);
        }
        else
        {
          $relationship = new Relationship;
          $relationship->Entity1 = $org;
          $relationship->Entity2 = $sub_agency;        
          $relationship->setCategory('Transaction');
          $relationship->description1 = 'Contractor';
          $relationship->description2 = 'Client';
          $relationship->save();
          $relationship->addReference(self::$baseContractorUrl . $org->fedspending_id, null, array('start_date', 'end_date', 'amount', 'goods'), 'FedSpending.org');
          $relationship->addFedspendingFiling($filing);
        }
        $filing->save();
        
        return true;
      }
      catch(Exception $e)
      {
        throw $e;
      }
    }
  }
  

  private function getGovernmentBodyEntity($name, $fedspending_name)
  {

    $alias = LsQuery::getByModelAndFieldsQuery('Alias',array('context' => 'fedspending_government_body','name' => $fedspending_name))->fetchOne();
    if ($alias)
    {
      return $alias->Entity;
    }
    else
    {
       $gov = EntityTable::getByExtensionQuery('GovernmentBody')->addWhere('e.name = ?', $name)->fetchOne();
       if ($gov)
       {
         $alias = new Alias;
         $alias->context = 'fedspending_government_body'; 
         $alias->name = $fedspending_name;
         $alias->entity_id = $gov->id;
         $alias->save();
         return $gov;
       }
       else
       {
         return null;
       }  
    }
  }
  
  
  private function addGovernmentBodyEntity($name, $fedspending_name, $parent_id = null)
  {
    $new_gov_body = new Entity;
    $new_gov_body->addExtension('Org');
    $new_gov_body->addExtension('GovernmentBody');
    $new_gov_body->name = $name;
    $new_gov_body->is_federal = 1;
    if ($parent_id)
    {
      $new_gov_body->parent_id = $parent_id;
    }    
    
    $new_gov_body->save();

    $alias = new Alias;
    $alias->context = 'fedspending_government_body'; 
    $alias->name = $fedspending_name;
    $alias->entity_id = $new_gov_body->id;
    $alias->save();

    return $new_gov_body;

  }

  
  
  private function getBusinessQuery()
  {
    $q = EntityTable::getByExtensionQuery('Business')->limit($this->_org_limit);
    if ($this->hasMeta($this->_round,'last_processed') && $start_id = $this->getMeta($this->_round,'last_processed'))
    {
      $q->addWhere('e.id > ?', $start_id);
    }
    $q->addWhere('e.id < ?', '1006');
    return $q;    
  }
  
  private function cleanGovtBodyName($name)
  {
    $name = str_replace('Dept.', 'Department', $name);
    $name = str_replace('.','',$name);
    if ($pos = strpos($name,'('))
    {
      if (stristr($name,'army') && stristr($name,'corps of engineers'))
      {
        $name = 'Army Corps of Engineers';
      }
      else
      {
        $name = substr($name,0,$pos);
      }
    }
    $name = trim($name);
    if (preg_match('/([^\,]*)\,\s+Department\s+of(\s+the)?$/isu',$name,$match))
    {
      $name = 'Department of ' . trim(ucwords(strtolower($match[1])));
    }
    return $name;
  }
  
}