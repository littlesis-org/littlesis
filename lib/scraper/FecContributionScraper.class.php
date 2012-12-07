<?php

class FecContributionScraper extends Scraper
{
  
  
  protected $fecBaseUrl = 'http://query.nictusa.com/';
  protected $fecContributorUrl = 'http://query.nictusa.com/cgi-bin/qind/';
  protected $fecContributorGetUrl = 'http://query.nictusa.com/cgi-bin/qindcont/';
  protected $fecCommitteeUrl = 'http://query.nictusa.com/cgi-bin/com_detail/';
  protected $fecCandidateUrl = 'http://query.nictusa.com/cgi-bin/can_detail/';
  protected $fecFormsUrl = 'http://query.nictusa.com/cgi-bin/dcdev/forms/';
  protected $years = array('2008');
  protected $committees = array();
  protected $person = null;
  protected $committee = null;
  protected $refreshDays = 90; //refresh every three months  
  protected $lookbackDays = 365;  
  protected $forceScaper = false;  
  protected $limit = 10;
  protected $_time;
  protected $_url;
  protected $_entity_reference = false;
  protected $entity_id = null;
  protected $prompt = false;
  protected $ignore_middle = false;
  protected $temp_postal = array();
  
  public static $fecImageUrl = 'http://images.nictusa.com/cgi-bin/fecimg/?';

  const YES = 10; 
  const NO = 0;
  const AMBIGUOUS = 1; 
  
  protected static $labels = array(self::NO => 'NO', 
                                    self::AMBIGUOUS =>'AMBIGUOUS', 
                                    self::YES=>'YES');
  
  public function setEntityId($entity_id)
  {
    $this->entity_id = $entity_id;
  }
  
  public function setPrompt($prompt)
  {
    $this->prompt = $prompt;
  }
  
  public function setForce($force)
  {
    $this->forceScraper = $force;
  }
  
  public function setIgnoreMiddle($ignore)
  {
    $this->ignore_middle = $ignore;
  }
  
  
  public function execute()
  {    
    if (!$this->safeToRun('fec'))
    {
      $this->printDebug('script already running');
      die;
    }
    
    $this->beginTimer();
    $this->stopTimer();
    $this->_time = $this->timer->getElapsedTime();
    $this->beginTimer();
    //loop through persons
    
    $persons = $this->getPersonsQuery($this->entity_id)->execute();

    if($persons->count())
    {
      foreach ($persons as $count=>  $person)
      {
        //get DB connection for transactions    
        try 
        {
          $this->temp_postal = array();
          $this->printDebug("\n***** Searching person: " . $person->getName() . " *****");
          $this->printDebug("Memory used: " . LsNumber::makeBytesReadable(memory_get_usage()) );
          $this->printDebug("Now: ". date('l jS \of F Y h:i:s A') );
          
          if ($this->hasMeta($person->id, 'scraped') && $this->getMeta($person->id, 'scraped') && $this->forceScraper != true) 
          {
            $this->printDebug($person->name . " already scraped; skipping");
            continue;
          }
          $this->getDonations($person);
          
          if ($this->limit === $count) { break; }    
          if ($this->testMode) { continue; }    
  
          //commit transaction
         
          $this->saveMeta($person->id, 'scraped', 1); 
          if (!$this->entity_id)
          {
            $this->saveMeta('first_round','last_processed',$person->id);
          }       
          $this->printDebug( $person->name . ": OK");
          
        }
        catch (Exception $e)
        {
          //something bad happened, rollback
          throw $e;
        }
      }
    }
    else
    {
      $this->printDebug('No persons found on database'); 
    }
    
  }

 
  public function setLimit($limit){
    $this->limit = $limit;
  }

  public function setYear($years){
    if(strlen($years) ){
      $this->years = explode(",", $years);
    }
  }
  
  
  function getDonations($person)
  {
    $this->person = $person;
    $last_name = strtoupper($this->person->name_last);  
    //take care of spaces in last name
    $last_name = preg_replace('/\s+/isu','|',trim($last_name));
    $first_name = strtoupper($this->generateFirstName($this->person->name_first, $this->person->name_middle));
    if ($this->ignore_middle)
    {
      $first_name = strtoupper($this->generateFirstName($this->person->name_first, null));
    }
    $limit_query = null;
    if ($this->hasMeta($person->id, 'scraped') && $this->getMeta($person->id, 'scraped')  && $this->forceScraper != true)
    {
      
      $scrape_start_time = time() - ($this->lookbackDays * 24 * 60 * 60);
      $scrape_start_date = date('m+d+Y', $scrape_start_time);
      $limit_query = '|AND|(tdate|>=|:'.$scrape_start_date.':)';
      $this->printDebug('We are limiting the search to start on ' + date('d-m-Y', $scrape_start_time) );

    }

    $page = 1;
    while($page)
    {  
      $query = $page.'/(lname|MATCHES|:'.$last_name.'*:)|AND|(fname|MATCHES|:'.$first_name.'*:)' . $limit_query;
      $url = $this->fecContributorGetUrl . $query;   
      $this->_url = $url;

      $this->printDebug($url);
      if ($this->prompt == 1)
      {
        $parse = $this->readline('  parse this page? (y or n)');
        $attempts = 1;
        while ($parse != 'y' && $parse != 'n' && $attempts < 5)
        {
           $parse = $this->readline('  parse this page? (y or n) ');
           $attempts++;
        }
      }
      try
      {
        $this->db->beginTransaction();
        
        if (!$this->browser->get($url)->responseIsError())
        {
          $text = $this->browser->getResponseText();
          if ($this->prompt == 0 || ($this->prompt == 1 && $parse == 'y'))
          {
            $this->parseDonorData($text);
          }
          preg_match('/cgi-bin\/qindcont\/(\d+)/i',  $text, $matches);
          $page = count($matches) ? $matches[1] : 0 ;
        }
        else
        {
          $this->printDebug("Couldn't get " . $this->fecContributorUrl );
          $this->saveMeta($person->id, 'url_error', 1);  
          $page = 0;
        }
        $this->db->commit();
      }
      catch (Exception $e)
      {
        //something bad happened, rollback
        $this->db->rollback();    
        throw $e;
      }
    }
  }


  
  function parseDonorData($text)
  {
    $this->_entity_reference = false;
    $contributors = $this->getContributors($text);
    $this->printDebug("Found " . count($contributors) . " possible donations" );
    
    foreach ($contributors as $contributor)
    {
      $contribution = $contributor[0];
      $donor = $this->generateDonor($contribution);
      
      
      $first_name_match = self::NO;
      $last_name_match = self::NO;
      $middle_name_match = self::NO;      
      $common_name = self::NO;
      $organization_matches = 0;      
      $city_match = self::NO;
      $state_match = self::NO;
      $zip_match = self::NO;
      $common_city = self::NO;      
      
      if($this->person->name_first == $donor->name_first){
        $first_name_match = self::YES;  
      }
      if($this->person->name_middle == $donor->name_middle){
        $middle_name_match = self::YES;  
      }
      if($this->person->name_last == $donor->name_last){
        $last_name_match = self::YES;  
      }
            
      //middle names are set
      if(strlen($this->person->name_middle) && strlen($donor->name_middle)){
        if($this->person->name_middle == $donor->name_middle)
        {
          $middle_name_match = self::YES;
        }
        else
        {
          //make sure the middle names
          if (strlen($this->person->name_middle) > 1 && strlen($this->person->name_middle) > 1 && !stristr($this->person->name_middle, $donor->name_middle) && !stristr($donor->name_middle, $this->person->name_middle))
          {
            $middle_name_match = self::AMBIGUOUS;
          }
          
          //initials match
          if (  (strlen($this->person->name_middle) == 1 || strlen($donor->name_middle) == 1) &&
            ( substr($this->person->name_middle, 0 , 1) == substr($donor->name_middle, 0 , 1)) )
          {
            $middle_name_match = self::AMBIGUOUS;
          }
          
        }
        
      }
            
      if (strlen($donor->name_first) < 2){
        $first_name_match = self::AMBIGUOUS;
      }
      if (strlen($donor->name_middle) > 0 && strlen($donor->name_middle) < 2){
        $middle_name_match = self::AMBIGUOUS;        
      }
      if (strlen($donor->name_last) < 2 ){
        $last_name_match = self::AMBIGUOUS;
      }
      
      if (in_array($this->person->name_last, LsLanguage::$commonLastNames) && in_array($this->person->name_first, LsLanguage::$commonFirstNames)){
        $common_name = self::YES;
      }
      
      //checking organizations      
      $this->printDebug("  Donor name: " . $donor->name_first. " "  .$donor->name_middle. " ". $donor->name_last ) ;      
      $this->printDebug("  Donor address: " . $donor->Address[0]->State->name . ", " . LsLanguage::titleize($donor->Address[0]->city) .", ".  $donor->Address[0]->postal );
      $this->printDebug("  Donor organization: " . LsLanguage::titleize(trim($donor->summary)) );            
      $this->printDebug("  Person name: " . $this->person->name_first. " "  .$this->person->name_middle. " ". $this->person->name_last ) ;      
 
      //checking address      
      foreach ($this->person->Address as $key=>$address){
        
        $this->printDebug("  Person Address: " .  $address->State->name . ", " . $address->city . ", " .$address->postal. " ");
        
        if ($this->person->Address[$key]->State->name == $donor->Address[0]->State->name){
          $state_match = self::YES;
        }        
        if (LsLanguage::titleize($this->person->Address[$key]->city) == LsLanguage::titleize($donor->Address[0]->city)) {
          $city_match = self::YES;
        }        
        
        if (substr($this->person->Address[$key]->postal, 0, 3) == substr($donor->Address[0]->postal, 0, 3) ) {
          $zip_match = self::AMBIGUOUS;
        }        
        if ($this->person->Address[$key]->postal == $donor->Address[0]->postal){
          $zip_match = self::YES;
        }
        if (in_array($donor->Address[0]->postal, $this->temp_postal))
        {
          $zip_match == self::YES;
        }        
        if (in_array($donor->Address[0]->city, LsLanguage::$commonCities )){
          $common_city = self::YES;
        }  
        
        break; //currently support only one address;
      }

      //check that first and last names are exact match
      /*$q = LsDoctrineQuery::create()
           ->from('Entity e')
           ->leftJoin('e.Relationship r ON (r.entity2_id = e.id)')
           ->where('r.entity1_id = ? AND r.category_id = ?', array($this->person->id, RelationshipTable::POSITION_CATEGORY));*/
      $orgs = $this->person->getRelatedEntitiesQuery('Org', RelationshipTable::POSITION_CATEGORY,null,null,null,false,1)->execute();  
      //$orgs = $q->execute();
      $bio = $this->person->summary;            
      $aliases = $this->person->Alias;            
            
      foreach ($aliases as $alias)
      {
        $this->printDebug("  Aliases: ".$alias->name."...");
        
        $alias_name = LsLanguage::getCommonPronouns($this->person->name, 
                                                    $alias->name, 
                                                    array_merge( LsLanguage::$business,
                                                      LsLanguage::$schools,
                                                      LsLanguage::$grammar,
                                                      LsLanguage::$states,
                                                      LsLanguage::$geography,
                                                      array(
                                                        $this->person->name_last,
                                                        $this->person->name_first,
                                                        $this->person->name_middle,
                                                        $this->person->name_nick,
                                                        'Retired',
                                                        'Requested',
                                                        'Info',
                                                        'Employed'
                                                       )
                                                     )
                                                    );
        
        $bio .= ' ' . $alias_name;        
      }
      foreach ($orgs as $org)
      {
        $this->printDebug("  Person organizations: ".$org->name."...");
        $bio .= ' ' . $org->name;
      }
            
      $summary_matches = LsLanguage::getCommonPronouns(LsLanguage::titleize(trim($donor->summary)), trim($bio), array_merge(
        LsLanguage::$business,
        LsLanguage::$schools,
        LsLanguage::$grammar,
        LsLanguage::$states,
        LsLanguage::$geography,
        array(
          $this->person->name_last,
          $this->person->name_first,
          $this->person->name_middle,
          $this->person->name_nick,
          'Retired',
          'Requested',
          'Info',
          'Employed'
          )));
      
      $this->printDebug("  Person organizations: ". $bio);
      $organization_matches = count($summary_matches);

      echo ' ';
      echo ' Matching First: '.self::$labels[$first_name_match].", ";
      echo ' Last: '.self::$labels[$last_name_match].", ";
      echo ' Middle: '.self::$labels[$middle_name_match].", ";
      
      echo ' City: '.self::$labels[$city_match].", ";
      echo ' State: '.self::$labels[$state_match].", ";
      echo ' Zip: '.self::$labels[$zip_match].", ";

      
      echo 'Organization count: '.$organization_matches;
      if(count($summary_matches)){
        $i = 0;
        echo "  (";
        foreach($summary_matches as $key => $o){
          echo $o;
          
          if($i != count($summary_matches)-1 ){
            echo ', ';
            $i++;
          }
        }
        echo ")";

      }
      echo "\n";
      
      
      $confident = false;      
      /* direct hit */      
      if( $first_name_match == self::YES && $middle_name_match > self::NO && $last_name_match == self::YES && $state_match == self::YES && $city_match == self::YES && $zip_match == self::YES){                
        $this->printDebug( "  CONFIDENT 1" );      
        $confident = true;
      }
      elseif( $first_name_match > self::NO && $middle_name_match > self::NO && $last_name_match == self::YES && $organization_matches > 1 && !$common_name){              
        $this->printDebug( "  CONFIDENT 2 (not common name)" );      
        $confident = true;
      }

      elseif( $first_name_match == self::YES && $middle_name_match > self::NO && $last_name_match == self::YES && $state_match > self::NO && $organization_matches  && !$common_name){
        $this->printDebug( "  CONFIDENT 3 (not common name)" );      
        $confident = true;
      }

      elseif( $first_name_match == self::AMBIGUOUS && $middle_name_match == self::YES && $last_name_match == self::YES && $state_match > self::NO && $organization_matches  && !$common_name){
        $this->printDebug( "  CONFIDENT 4 (not common name)" );      
        $confident = true;
      }
      
      elseif( $first_name_match == self::YES && $middle_name_match > self::NO && $last_name_match == self::YES && $state_match > self::NO && $city_match > self::NO && $zip_match > self::NO && !$common_city  ){                
        $this->printDebug( "  CONFIDENT 5" );      
        $confident = true;
      }

      elseif( $first_name_match == self::YES && $middle_name_match > self::NO && $last_name_match == self::YES && $state_match > self::NO && $city_match > self::NO && $zip_match > self::NO && !$common_city && !$common_name ){                
        $this->printDebug( "  CONFIDENT 6" );      
        $confident = true;
      }
      
      elseif( $first_name_match == self::YES && $middle_name_match > self::NO && $last_name_match == self::YES && $state_match > self::NO && $city_match > self::NO &&  $zip_match > self::NO  ){                
        $this->printDebug( "  CONFIDENT 7" );      
        $confident = true;
      }
        
      elseif( $first_name_match == self::YES && $middle_name_match > self::NO && $last_name_match == self::YES && $state_match > self::NO && $city_match > self::NO &&  $zip_match > self::NO  ){                
        $this->printDebug( "  CONFIDENT 8" );      
        $confident = true;
      }
      elseif( $first_name_match == self::YES && $middle_name_match > self::NO && $last_name_match == self::YES && $state_match > self::NO && $city_match > self::NO &&  $zip_match > self::NO  ){                
        $this->printDebug( "  CONFIDENT 9" );      
        $confident = true;
      }
      elseif( $first_name_match == self::YES && $middle_name_match > self::NO && $last_name_match == self::YES && $zip_match > self::YES && $organization_matches){
        $this->printDebug( "  CONFIDENT 10" );      
        $confident = true;
      }
      
      if ($this->prompt == 1)
      {
        $accept = $this->readline('  Is this the same entity? (y or n)');
        $attempts = 1;
        while ($accept != 'y' && $accept != 'n' && $attempts < 5)
        {
           $accept = $this->readline('  Is this the same entity? (y or n) ');
           $attempts++;
        }
        if ($accept == 'y')
        {
          $confident = true;
        }
        else
        {
          $confident = false;
        }
      }
      
      if($confident)
      {
        $this->parseRecipients($contribution);      
        $this->temp_postal[] = $donor->Address[0]->postal;
      }
      else{
        $this->printDebug( "  NO CONFIDENCE. SKIPPING...\n" );      
      }
      
    }
    
  }



  function  parseRecipients($contribution){  
    $recipients = $this->getRecipients($contribution);

    $this->printDebug( "  Number of recipients " . count($recipients) );         
      
    foreach ($recipients as $recipient){


      $candidate = $this->getCandidateInfo($recipient[0]);
      $committee = $this->getCommitteeInfo($recipient[0]);
      $committee_name = trim($committee[2]) ;
      $committee_fec_id = trim($committee[1]);

      
      //CHECK FOR EXISTING COMMITTEE
      unset($current_committee);
      $current_committee = EntityTable::getByExtensionQuery(array('Org','PoliticalFundraising'))->addWhere("org.name = ?", $committee_name)->fetchOne();

      if( $current_committee ){
        $this->printDebug("  Found Committee " . $committee_name . " (" . $committee_fec_id . ")");        
      }
      else
      {
        //clear cache
        Doctrine::getTable('ExtensionDefinition')->clear();

        $current_committee = new Entity;
        $current_committee->addExtension('Org');    
        $current_committee->addExtension('PoliticalFundraising');        
        $current_committee->name = LsLanguage::titleize($committee_name);        
        $current_committee->fec_id =  $committee_fec_id;
        $current_committee->save();

        $current_committee->addReference($source = $this->fecCommitteeUrl.$committee_fec_id, 
                                        $excerpt=null, 
                                        $fields=array('name', 'fec_id'), 
                                        $name='FEC Disclosure Report', 
                                        $detail=null, 
                                        $date=null, false);

        $this->printDebug( "  Adding new committee: " . $committee_name . " (" . $committee_fec_id . ")" );         
      }

      $this->committee = $current_committee;
      $this->updateCommitteeDetails($current_committee);

      $transactions = $this->getTransactions($recipient[0]);

      //RECORD DONATIONS
      $validate_existance_of_donation = true;
      foreach ($transactions as $transaction){
        
        
        list($month, $day, $year) = explode('/', $transaction[1]);
        
        $donation_amount = $transaction[2];
        $donation_fec_id = $transaction[4];
        $donation_date = $year . '-' . $month . '-' . $day;

        
        if ($this->hasMeta($this->person->id, $donation_fec_id) && !$this->forceScaper)
        {
          $this->printDebug( "#$donation_fec_id Already scraped" );          
          continue;  
        
        }
        
        if($validate_existance_of_donation){
          $donation_exists =  FecFilingTable::getFecFiling($donation_fec_id);
          $validate_existance_of_donation = false;
        }
        
        if(  !$donation_exists )
        {
          
          $this->printDebug( "  Donation exists:  FALSE " );
                    
  
  
          $this->printDebug( "  Donation ($donation_fec_id): " . $donation_amount . " on  " . $donation_date ) ;
  
          
          $this->printDebug( "  Creating relationship between \"" . $this->person->name_first . " " . $this->person->name_last . "\" and \"" .   $current_committee->name . "\"");      

 
          $filing = new FecFiling;
          $filing->amount = $donation_amount;
          $filing->fec_filing_id = $donation_fec_id;
          $filing->start_date = $donation_date;
          $filing->end_date = $donation_date;
  
          $relationship = null;
          if($relationship = $this->person->getRelationshipsWithQuery( $current_committee, RelationshipTable::DONATION_CATEGORY)->fetchOne() ){          
            $relationship->addFecFiling($filing);
          }
          else
          {
            $relationship = new Relationship;
            $relationship->Entity1 = $this->person;
            $relationship->Entity2 = $current_committee;        
            $relationship->setCategory('Donation');
            $relationship->description1 = 'Campaign Contribution';
            $relationship->is_current = 1;
            $relationship->save();
            $relationship->addFecFiling($filing);
            $relationship->addReference($source = self::$fecImageUrl . $donation_fec_id, 
                                        $excerpt=null, 
                                        $fields=array('amount', 'start_date', 'end_date', 'description1'), 
                                        $name='FEC Filing', 
                                        $detail=null, 
                                        $date=null);                    
            $filing->save();
            $relationship->addReference($source = $this->_url,
                                         $excerpt = null,
                                         $fields=array('amount', 'start_date', 'end_date', 'description1'),
                                         $name = 'FEC contribution search',
                                         $detail = null);
            if ($this->_entity_reference == false)
            {
              $this->person->addReference(
                                                    $source = $this->_url,
                                                    $excerpt = null,
                                                    $fields = null,
                                                    $name = 'FEC contribution search');
              $this->_entity_reference = true;
            }
          }  
          
          $this->saveMeta($this->person->id, $donation_fec_id, 1);          
          
        }
        else
        {
          $this->printDebug("  Donation exists: TRUE" );
          break;
        }
      }      
      
    }
    
    $this->printDebug( "+ Adding Donation: COMPLETE\n");
  }
  
  


  

  
  public function updateCommittees()
  {
    $this->getCommitteesQuery()->execute();
        
    while ($committee = current($this->committees))
    {
      $this->getCommitteeDetails($committee);

      next($this->committees);      
    }  
  }


  /**
    * start with committee's fec_id
    *     go to committee forms url:
    *     'http://query.nictusa.com/cgi-bin/dcdev/forms/' . $committee->fec_id
    *   
    *     find correct report based on contribution date
    *   
    *     go to report url with appended sa/ALL, eg:
    *     http://query.nictusa.com/cgi-bin/dcdev/forms/C00290825/308370/sa/ALL
    *   
    *     search for person name, see if there are new addresses!
    */
  private function updateDonorDetails()
  {
    $committee = $this->committee;
    
    $this->printDebug( "  + Updating person details: " . $this->fecFormsUrl . $committee->fec_id );
    
    if (!$this->browser->get($this->fecFormsUrl . $committee->fec_id)->responseIsError()){
      $text = $this->browser->getResponseText();

      
    }
    else{
      $this->printDebug( "Couldn't get " . $this->fecContributorUrl );
    }
    
    $this->person->save();
    $this->printDebug( "  + Updating person details:  COMPLETE" );
    
  }

  private function updateCommitteeDetails(Entity $committee)
  {
    
    $this->printDebug( "  Updating committee details: " . $this->fecCommitteeUrl . $committee->fec_id );

    if (!$this->browser->get($this->fecCommitteeUrl . $committee->fec_id)->responseIsError()){

      $text = $this->browser->getResponseText();
      
      if ( preg_match('/Treasurer Name:<\/B><\/TD><TD>(.+)<\/TD><\/TR>/', $text, $treasurer_name) ) {
        //nothing
      }
      
      $committee_designation = null;
      if ( preg_match('/Committee Designation: &nbsp;<\/B><\/TD><TD>(\w)/', $text, $committee_designation) ) {  
        switch(trim($committee_designation[1]) ){
          
          case 'P':
            $committee->addExtension('IndividualCampaignCommittee'); 
            $committee_designation = "Principal Campaign Committee";
          break;
            
          case 'A':
            $committee->addExtension('IndividualCampaignCommittee'); 
            $committee_designation = "Authorized Campaign Committee";
          break;
            
          default:
            $committee->addExtension('OtherCampaignCommittee');             
            $committee_designation = "Other Campaign Committee";

          }
      }
      else
      {
        $committee->addExtension('OtherCampaignCommittee');             
        $committee_designation = "Other Campaign Committee";      
      }
      
      if ( preg_match('/Committee Type: &nbsp;<\/B><\/TD><TD WIDTH=300>([^<]+)<\/TD><\/TR>/', $text, $committee_type) ) {
        //var_dump($committee_type);
        $type = null;
        switch(trim($committee_type[1])){
          case 'PRESIDENTIAL':
            $type = 'pres';
          break;
          case 'HOUSE':
            $type = 'house';
          break;
          case 'SENATE':
            $type = 'senate';
          break;
          
        }
      }

      if ( preg_match('/Candidate State:<\/B><\/TD><TD>([^<]+)<\/TD><\/TR>/', $text, $candidate_state) ) {
        //var_dump($candidate_state);
        
        if( trim($candidate_state[1]) != "Presidential Candidate"){
          $committee->State =  AddressStateTable::retrieveByText($candidate_state[1]);
        }
      } 

      if (preg_match_all('#cgi-bin/can_detail/([^"]+)?\/">([^<]+)</A>#i', $text, $candidates, PREG_PATTERN_ORDER) ) {
        
        /*
         * $candidates[1]  =  FEC ID
         * $candidates[2]  =  NAME
         */
        foreach ($candidates[1] as $key=> $candidate_id)
        {
          $candidate_name = $candidates[2][$key];
          $found_candidate = null;
          
          //look for candidate by their FEC IDs
          $found_candidate = $this->getCandidatesQuery()->addWhere('politicalcandidate.senate_fec_id = ? OR politicalcandidate.house_fec_id = ? OR politicalcandidate.pres_fec_id = ?', array($candidate_id, $candidate_id, $candidate_id))->fetchOne();
          
          $found_msg = ($found_candidate) ? " FOUND" : " NOT FOUND";
          
          $this->printDebug( "  + Looking for candidate by ID: ". $found_msg );

          //next look for them by their names
          $where_clause = null;
          $p1 = null;
          if(!$found_candidate)
          {
            $p1 = PersonTable::parseCommaName($candidate_name);

            $candidate_query = $this->getCandidatesQuery()->addWhere("person.name_first = ? AND person.name_last = ?", array($p1->name_first,$p1->name_last));
            
            if(strlen($p1->name_middle) )
            {
              $candidate_query = $candidate_query->addWhere('person.name_middle LIKE  ?', "%".$p1->name_middle );          
            }
            
            $found_candidate = $candidate_query->fetchOne();

            $found_msg =  ($found_candidate) ? " FOUND" : " NOT FOUND";            

            $this->printDebug( "  + Looking for candidate by name: ". $p1->name_first . " " . $p1->name_last . $found_msg. " ");
            
            //candidate is not in database. we should add them
            if(!$found_candidate)
            {

              if (!$this->browser->get($this->fecCandidateUrl . $candidate_id )->responseIsError())
              {
                $text = $this->browser->getResponseText();

                
                $office_sought_arr = $this->getOfficeSought($text);
                
                if( !$office_sought_arr[1][0] )
                {
                  continue;
                }                
                $office_sought = $office_sought_arr[1][0];
                $found_candidate = PersonTable::parseCommaName($candidate_name);

                $found_candidate->addExtension('PoliticalCandidate');                
                
                $found_candidate->is_federal =  true;        
                $found_candidate->is_state = false ;        
                $found_candidate->is_local = false ;        
                
                $found_candidate->pres_fec_id = ($office_sought == 'President') ? $candidate_id : null;        
                $found_candidate->senate_fec_id = ($office_sought == 'Senate') ? $candidate_id : null;        
                $found_candidate->house_fec_id = ($office_sought == 'House') ? $candidate_id : null;
                
                $found_candidate->save();

                $found_candidate->addReference($source = $this->fecCandidateUrl . $candidate_id, 
                                               $excerpt=null, 
                                               $fields=array('name_first', 'name_middle', 'name_last', 'name_prefix', 'name_suffix', 'pres_fec_id', 'senate_fec_id', 'house_fec_id'), 
                                               $name='FEC Disclosure Report',
                                               $detail=null, 
                                               $date=null,
                                               false);
                
                
                $this->printDebug( "  Adding new candidate: " . $p1->name_first . " " . $p1->name_middle . " " . $p1->name_last );
              }
            }
          }
          $q = LsDoctrineQuery::create()
            ->from('Relationship r')
            ->where('r.entity1_id = ? AND r.entity2_id = ? AND r.category_id = ? AND description1 = ?', array($committee->id, $found_candidate->id, RelationshipTable::DONATION_CATEGORY, $committee_designation));

          if (!$q->count()){          

            $relationship = new Relationship;
            $relationship->Entity1 = $committee;        
            $relationship->Entity2 = $found_candidate;
            $relationship->setCategory('Donation');
            //$relationship->amount = $donation_amount;
            $relationship->description1 = $committee_designation;
            //$relationship->fec_donation_id = $donation_fec_id;
            //$relationship->start_date = $donation_date;
            //$relationship->end_date = $donation_date;
            $relationship->is_current = 1;
            $relationship->save();

            //campaign contribution
            $relationship->addReference($source = $this->fecCandidateUrl . $candidate_id, 
                                        $excerpt=null, 
                                        $fields=array('description1'), 
                                        $name='FEC Disclosure Report', 
                                        $detail=null, 
                                        $date=null);

          }
        }
      }
    
    }
    else
    {
      $this->printDebug( "Couldn't get " . $this->fecCommitteeUrl );
    }
    
    $committee->save();
    $this->printDebug( "  Updating committee details:  COMPLETE" );
  }
  
  
  private function getPersonsQuery($entity_id = null)
  {
    $start_id = null;
    if (!$entity_id && $this->hasMeta('first_round', 'last_processed')) 
    {
      $start_id = $this->getMeta('first_round', 'last_processed');
    }    
    else
    {
      $start_id = $entity_id - 1;
    }
    $q = EntityTable::getByExtensionQuery('Person');
    if ($start_id)
    {
      $q->addWhere('e.id > ?', $start_id);
    }
    if($this->limit)
    {
      $q->limit($this->limit);
    }
    return $q;
  }
  
  private function getCommitteesQuery(){
    return EntityTable::getByExtensionQuery('PoliticalFundraising');
  }
  
  private function getCandidatesQuery(){
    return EntityTable::getByExtensionQuery(array('Person','PoliticalCandidate'));
  }
  
  
  //regular expression wrapper functions
  
  /**
    * get contributor blocks  
    */
  function getContributors($text){
    preg_match_all('/^<B>[^<]+<\/B><BR>.*<\/TABLE><BR>\n(?!<TABLE)/Umsi', $text, $contributors, PREG_SET_ORDER);
    return $contributors;
  }  
  
  function getTransactions($text){  
    preg_match_all('/<TR><TD WIDTH=50><\/TD><TD WIDTH=150>([^<]+)<\/TD>\n<TD WIDTH=150>([^<]+)<\/TD>\n(<TD WIDTH=150 ALIGN=CENTER><A HREF ="http:\/\/images\.nictusa\.com\/cgi-bin\/fecimg\/\?(\d+)">|<TD WIDTH=150 ALIGN=CENTER><B>(\d+)<\/B>)/i', $text, $transactions, PREG_SET_ORDER);
    return $transactions;
  }

  //get candidate info  
  function getCandidateInfo($text){
    preg_match('/cgi-bin\/can_detail\/([^"]+)">([^<]+)<\/A>/i', $text, $candidate);
    return $candidate;
  }
  
  //get committee info
  private function getCommitteeInfo($text){
    preg_match('/cgi-bin\/com_detail\/([^"]+)">([^<]+)<\/A>/i', $text, $committee);
    return $committee;
  }

  private function getRecipients($text){
    preg_match_all('/<TABLE.*TABLE>/Usmi', $text, $recipients, PREG_SET_ORDER);  
    return $recipients;
  }  
    
  private function getOfficeSought($text){
    preg_match_all('/Office Sought: &nbsp;<\/B><\/TD><TD>(.+)<\/TD><\/TR>/', $text, $office_sought_arr); 
    return $office_sought_arr;
  }  
  
  
  private function generatePerson($name_str, $summary = null, $orgs = null )
  {
    $person = PersonTable::parseCommaName($name_str);

    return $person;
  }


  
  /**
   *  get donor info       
   */
  private function generateDonor($text)
  {
    $text_arr = explode("<BR>", $text);
    //var_dump($text_arr[0]);
    $donor = $this->generatePerson(LsHtml::stripTags($text_arr[0],''));
    $address_arr = LsLanguage::parseCityStatePostal($text_arr[1]);
    
    $a = new Address;
    $a->street1 = isset($address_arr['street1']) ? $address_arr['street1'] : null;
    $a->street2 = isset($address_arr['street2']) ? $address_arr['street2'] : null;
    $a->city = $address_arr['city'];
  
    if ($state = AddressStateTable::retrieveByText($address_arr['state']))
    {
      $a->State = $state;
    }
  
    $a->postal = $address_arr['zip'];
    
    $donor->addAddress($a);
    $donor->summary =  strip_tags(trim($text_arr[2]));    
    return $donor;

  }  
  

  
  private function generateFirstName($first, $middle)
  {
    $query = null;
     
     //$firstName = (strlen($person->name_first) == 1 ) ? strtoupper($person->name_first)."|".str_replace(' ', '.', strtoupper($person->name_middle)) : strtoupper($person->name_first);

    $first = str_replace(' ', '.', $first);
    $middle = str_replace(' ', '.', $middle);
    
    if ($middle)
    {
      if (strlen($first) == 1)
      {
        $query = $first . '|' . $middle;
      }
      else
      {
        $query = $first. '|' . substr($middle, 0, 1);
      }
    }    
    else
    {
      $query = $first;
    }
    
    return $query;
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
  
  public function readline($prompt="")
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
     return $out;
  }
  
}
