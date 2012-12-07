<?php

class SchoolScraper extends Scraper {

  protected $refreshDays = 30;
  protected $url = 'http://nces.ed.gov/ipedspas/dct/downloads/data/HD2007.ZIP';

  public function execute(){  
    
     
    $schools = $this->getSchoolList();
    $position = 0;
    
    if ($this->hasMeta('current_postion', 'position') && $position = $this->getMeta('current_postion', 'position') )
    {
      $this->printDebug("Resuming scraping. Starting at position: " . $position);
    }    
    //print_r($schools);
    $count = 0;
    while ($position <= count($schools) )
    {
      $school = $schools[$position];

      //get DB connection for transactions		
      try 
      {        
        //begin transaction
        $this->db->beginTransaction();
        $this->printDebug("\n***** Searching  *****");
        $this->printDebug("Memory used: " . LsNumber::makeBytesReadable(memory_get_usage()) );
        $this->printDebug("Now: ". date('l jS \of F Y h:i:s A') );
        
        $this->import($school);
        
        if ($this->limit === $count) { break; }		
        if ($this->testMode) { continue; }	
  
        //commit transaction
        $this->db->commit();
        $position++ ;
        $count++;
        $this->saveMeta('current_postion', 'position', $position);				
        $this->printDebug( "OK");
      }
      catch (Exception $e)
      {
        //something bad happened, rollback
        $this->db->rollback();		
        throw $e;
      }
    }
  }
  
  public function import($school)
  {
    if(EntityTable::getByExtensionQuery('Org')->addWhere('LOWER(org.name) LIKE ?', '%'.strtolower($school->instnm)."%")->fetchOne())
    {
      $this->printDebug("School exists in database: ".$school->instnm);
    }
    else
    {
      
      $address = new Address;
      $address->street1 = isset($school->addr) ? $school->addr : null;
      $address->street2 = isset($school->street2) ? $school->street2 : null;
      $address->city = $school->city;
      if ($state = AddressStateTable::retrieveByText($school->stabbr))
      {
        $address->State = $state;
      }    
      $address->postal = $school->zip;
      
      
      $aliases = explode("|", $school->ialias);
      $website = null;
      if(!preg_match('/^http\:\/\//i', trim($school->webaddr)))
      {
        $website = "http://" . strtolower($school->webaddr);
      }
      
      $this->printDebug($website);
  
      $newschool = new Entity;
      $newschool->addExtension('Org');
      $newschool->addExtension('School');    
      $newschool->name = $school->instnm;
      $newschool->website = $website;
      $newschool->addAddress($address);
      
      $newschool->save();
      
      

      foreach($aliases as $alias)
      {
        try
        {
          $newalias = new Alias;
          $newalias->Entity = $newschool;
          $newalias->name = $alias;
          $newalias->save();
        }
        catch(Exception $e){
          $this->printDebug("An alias exception. No biggie. It's most likely that the name already exists. so we ignore it and move on: ".$e);
        }
      }
  
      $this->printDebug("Adding new school: " . $school->instnm);
    }
    
  }
  
  public function getSchoolList()
  {

    $educational_institutions = null;
    
    $base_data_dir = sfConfig::get('sf_root_dir') . '/data/schools/';
    $filename_zip = sfConfig::get('sf_root_dir') . '/data/schools/' . basename($this->url);
    $filename_csv = sfConfig::get('sf_root_dir') . '/data/schools/' . preg_replace('/zip$/i', 'csv', strtolower(basename($this->url) ) );
    $file_contents_csv = null;

    if(!is_dir($base_data_dir )){
      mkdir($base_data_dir);
    }

    
    if (!$this->browser->get($this->url)->responseIsError() || file_exists($filename_zip) ){
      
      $zip_saved = null;
      $ret = null;
      if( !file_exists($filename_zip) )
      {
        $zip_saved = file_put_contents($filename_zip, $this->browser->getResponseText());
      }
      else
      {
        $zip_saved = true;
      }
      
      if ($zip_saved !== FALSE) 
      {
        
        if( !file_exists($filename_csv) )
        {
          exec("unzip $filename_zip  -d $base_data_dir", $ret);
        }
        else
        {
          $ret = true;          
          
        }
        
        
        if($ret)
        {          
          $educational_institutions = LsArray::CsvFileToArrayObject( $filename_csv );
          $this->printDebug(' Found schools: ' . count( $educational_institutions ));
        }
        else
        {
          $this->printDebug('Failed to load csv');
        }        
      } 
      else 
      {
        $this->printDebug('Zip failure');
      }
    
    }
    else
    {
      $this->printDebug('Browser did not get file');    
    
    }
    
    
    return $educational_institutions;     
  }
  
      /*
  
  public function getSchoolList(){

    $query = array( 'name' => null, 
                    'type' => '/education/educational_institution',
                  );
    
    $freebase = new LsFreebase;
    $educational_institution = $freebase->read($query);
    
  }
    */
  
  
  
}
