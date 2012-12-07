<?php

class EducationScraper extends Scraper {

  protected $refreshDays = 90; //refresh every three months  
  protected $lookbackDays = 365;  
  protected $forceScaper = false;  
  protected $_limit = 100;

  public function execute()
  {  
    if (!$this->safeToRun('education'))
    {
      $this->printDebug('script already running');
      die;
    }
    $q = EntityTable::getByExtensionQuery(array('Person', 'BusinessPerson'))->limit($this->_limit);
    if ($this->hasMeta('first_round','last_processed'))
    {
      $q->addWhere('e.id > ?', $this->getMeta('first_round','last_processed'));
    }
    $people = $q->execute();
    foreach ($people as $key => $person )
    {

      //get DB connection for transactions		
      try 
      {        
        //begin transaction
        $this->db->beginTransaction();
        $this->printDebug("\n***** Searching ".$person->name." *****");
        $this->printDebug("Memory used: " . LsNumber::makeBytesReadable(memory_get_usage()) );
        $this->printDebug("Now: ". date('l jS \of F Y h:i:s A') );
        
        if (0)//$this->hasMeta($person->id, 'refresh_time') && time() < (int)$this->getMeta($person->id, 'refresh_time') && !$this->forceScaper) 
        {
          $this->printDebug("Refresh time: " . date('l jS \of F Y h:i:s A', (int)$this->getMeta($person->id, 'refresh_time') ) );
          $this->printDebug($person->name . " already scraped; skipping");
          $this->db->rollback();
          continue;
        }
        
        $this->getBusinessWeek($person);
        
        if ($this->limit === $key) { break; }		
        if ($this->testMode) { continue; }	
  
        //commit transaction
        $this->db->commit();

        $refresh_days = time() + ($this->refreshDays * 24 * 60 * 60);        
        $this->saveMeta($person->id, 'scraped', 1); 
        $this->saveMeta('first_round','last_processed',$person->id);       
        
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
  
  function getBusinessWeek(Entity $person)
  {
    /*
    $yahoo = new LsYahoo;
    $yahoo->setService('Web Search');
    $yahoo->setSite('http://investing.businessweek.com');
    $yahoo->setQuery($person->name);
    $this->printDebug($yahoo->getQueryUrl());

    $yahoo->execute();    
    $results = $yahoo->getResults();  */
    
    $google_scraper = new LsGoogle();
    $google_scraper->setQuery('site:investing.businessweek.com ' . $person->name);
    $this->printDebug('site:investing.businessweek.com ' . $person->name);
    $google_scraper->execute();
    if (!$google_scraper->getNumResults())
    {
      return null;
    }
    
    $results = $google_scraper->getResults();
    $businessweek_profile = null;
    
    foreach($results as $result)
    {
      $this->printDebug($result->unescapedUrl);
      if(preg_match('/^.*?person\.asp\?personId=\d+/is', $result->unescapedUrl, $match) )
      {
        $businessweek_profile = $match[0];
        break;
      }
    }
    
    if(!$businessweek_profile)
    {
      foreach($results as $result)
      {
        $url = $result->unescapedUrl;
        if (preg_match('/^(.*?)\&/is',$url, $match))
        {
          $url = $match[1];     
        }
        if (!stristr($url,'http://'))
        {
          $url = 'http://investing.businessweek.com/' . $url;
        }   
        $this->printDebug('new url: ' . $url);
        if (!$this->browser->get($url)->responseIsError())
        {
          $text = $this->browser->getResponseText();
          //var_dump($text);
          $links = LsHtml::matchLinks($text);    
          foreach($links as $link)
          {
            if (preg_match('/' . $person->getNameRegex(true) . '/s',$link['text']) && preg_match('/^.*?person\.asp\?personId=\d+/is', $link['url'], $match))
            {
              $url = $match[0];
              if (!stristr($url,'http://'))
              {
                $url = 'http://investing.businessweek.com/' . $url;
              } 
              $businessweek_profile = $url;
              break;
            }
          }
          if ($businessweek_profile)
          {
            $this->printDebug('Businessweek profile found on 2nd attempt: ' . $businessweek_profile);
            break;
          }
        }
      }
      if (!$businessweek_profile)
      {
        $this->printDebug('Buisnessweek profile not found');
        return;
      }
    }
    

    $education_found = false;
    $employment_found = false;
    $summary_found = false;
    $ed_matched = false;
    //go to businessweek profile and get education
    $this->browser->get($businessweek_profile);
    
    if ($text = $this->browser->getResponseText())
    {
      //$education = null;
      //$employment = null;
      
      if( preg_match('#EDUCATION[\*]?<\/h2>[\n\s]*(.+?)\<h2#is', $text, $education ) )
      {
        $ed_matched = preg_match_all('/<strong>(.+?)<\/strong>\s*(\d{4})?\s*<\/div><div.*?>(.+?)</s', $education[1], $education_found );
      }

      if( preg_match('#OTHER AFFILIATIONS[\*]?<\/h2>[\n\s]*(.+?)\<\/td#s', $text, $employment ) )
      {
        preg_match_all('#href\=\".+?\"\>(.+?)\<\/a\>#is', $employment[1], $employment_found );
      }
      
      preg_match('#BACKGROUND[\*]?<\/h2>[\n\s]*(.+?)\<\/p>#s', $text, $summary_found);

      $summary_found = strip_tags($summary_found[1]);
      
      //var_dump($summary_found);
      if($ed_matched)
      {
        $this->printDebug('Education info found at Businessweek');         
      }
      else
      {
        $this->printDebug('Education info not found at Businessweek');  
        return;
      }
    }
    else
    {
      $this->printDebug('Businessweek browser error');
      return;
    }
    
    
    $education_history = null;
    $employment_history = null;

    $wikipedia = new LsWikipedia;
    $wikipedia->request($person->name);    
    $wikipedia->execute();
    $plaintext = $wikipedia->getPlainText();    
    
    
    foreach($education_found[3] as $key=> $institution)
    {
      $arr = null;
      $arr['institution'] = $institution;
      $arr['degree'] = $education_found[1][$key];
      $arr['year'] = null;
      if ($education_found[2][$key] != '')
      {
        $arr['year'] = $education_found[2][$key];
      }

      $wikipedia_matches = LsLanguage::getCommonPronouns( $arr['institution'], $plaintext, array_merge( LsLanguage::$business,
                                                                                                        LsLanguage::$schools,
                                                                                                        LsLanguage::$grammar) );
      
      if($wikipedia_matches)
      {
        $arr['source'] = 'http://en.wikipedia.org/wiki/' . str_replace('+','_', $wikipedia->getTitle());        
      }
      else
      {
        $arr['source'] = $businessweek_profile;        
      }
      
      $education_history[] = (object)$arr;
        
    }

    foreach($employment_found[1] as $key => $company)
    {
      $arr = null;
      $arr['company'] = $company;
      $arr['title'] = null;
      $employment_history[] = (object)$arr;
    }

    
    $possible_person = array('name' => $person->name, 'summary' => $summary_found, 'employment_history' => (object)$employment_history, 'education' => (object)$education_history);
    $possible_persons[] = (object)$possible_person;
    
    
    
    $this->import($person, $possible_persons);
      
  }
  
  
  function getFreebase(Entity $person)
  {

    $query = array( "name" => $person->name_first . " " . $person->name_last, "type" => "/people/person", 
                    "education" => array(array("degree" => null, "institution" => null )),
                    "employment_history" => array(array("company" => null, "title" => null )),                      
                  );
    
    $freebase = new LsFreebase;
    $response = $freebase->read($query);
    $this->import($person, $response);
  }
  
  
  function import(Entity $person, $possible_persons)
  {
    
    //loop through the people we found. usually just one.
    foreach($possible_persons as $possible_person)
    { 

      $this->printDebug('Query returned '.count($possible_person).' person named ' . $possible_person->name);      

      //this person does not provide education. we skip
      if( count($possible_person->education))
      {
        $this->printDebug('Education found');
      }
      else
      {
        $this->printDebug('No education history found');
        continue;        
      }
      
      //get employement info for this possible match
      $possible_person_bio = $possible_person->summary;      
      if( count($possible_person->employment_history) )
      {
        
        foreach($possible_person->employment_history as $employment)
        {
          $possible_person_bio .= ' ' . $employment->company. " ";
        }
        $this->printDebug('Employment found');
      }
      else
      {
        $this->printDebug('No employment history found');
        continue;
      }
            
      //get employment info for the person in our database
      $relationship_orgs = $person->getRelatedEntitiesQuery('Org', RelationshipTable::POSITION_CATEGORY,null,null,null,false,1)->execute();
      $person_bio = $person->summary;            
            
      foreach ($relationship_orgs as $org)
      {
        $person_bio .= ' ' . $org->name;
      }      
      
      //lets see how many matches we get
      $matches = LsLanguage::getCommonPronouns($person_bio, trim($possible_person_bio), LsLanguage::$business );
      
      if( count($matches))
      {

        foreach($possible_person->education as $school)
        {

          $school->institution = mb_convert_encoding($school->institution, 'UTF-8');
          $school->institution = preg_replace('//isu',' ',$school->institution);
          $this->printDebug('Looking for the school: '.  $school->institution);
          
          $current_school = EntityTable::findByAlias($school->institution,$context = 'bw_school');

          //find school
          if ($current_school)
          {
            $this->printDebug('Found school');              
          }
          else
          {
            $current_school = EntityTable::getByExtensionQuery(array('Org','School'))->addWhere('LOWER(org.name) LIKE ?', '%'.strtolower($school->institution)."%")->fetchOne();
            if (!$current_school)
            {
              $new_school = new Entity;
              $new_school->addExtension('Org');
              $new_school->addExtension('School');
              $new_school->name = $school->institution;
              $wikipedia = new LsWikipedia;
              $wikipedia->request($school->institution);    
              if ($wikipedia->execute() && !$wikipedia->isDisambiguation())
              {
                
                $info_box = $wikipedia->getInfoBox();
                if (isset($info_box['students']) && preg_match('/([\d\,]{2,})/isu', $info_box['students']['clean'], $match))
                {
                  $new_school->students = LsNumber::clean($match[1]);
                }
                else
                {
                  $student_types = array('undergrad','postgrad','grad','doctoral');
                  $num_students = 0;
                  foreach($student_types as $st)
                  {
                    if (isset($info_box[$st]) && preg_match('/([\d\,]{2,})/isu', $info_box[$st]['clean'], $match))
                    {
                      $num_students += LsNumber::clean($match[1]);
                    }
                  }
                  if ($num_students > 0)
                  {
                    $new_school->students = $num_students;;
                  }
                }
                if (isset($info_box['faculty']) && preg_match('/([\d\,]{2,})/isu', $info_box['faculty']['clean'], $match))
                {
                  $new_school->faculty = LsNumber::clean($match[1]);
                }
                if (isset($info_box['type']))
                {
                  if (stristr($info_box['type']['clean'],'public'))
                  {
                    $new_school->is_private = 0;
                  }
                  else if (stristr($info_box['type']['clean'],'private'))
                  {
                    $new_school->is_private = 1;
                  }
                }                
                if (isset($info_box['endowment']))
                {
                  if (preg_match('/(\$[\d\,\.\s]+)(million|billion)/isu',$info_box['endowment']['clean'],$match))
                  {
                    if (strtolower($match[2]) == 'billion')
                    {
                      $factor = 1000000000;
                    }
                    else
                    {
                      $factor = 1000000;
                    }
                    $new_school->endowment = LsNumber::formatDollarAmountAsNumber($match[1], $factor);
                  }
                }
                if (isset($info_box['established']))
                {
                  $year = null;
                  if ($date = LsDate::convertDate($info_box['established']['clean']))
                  {
                    $new_school->start_date = $date;
                  }
                  else if (preg_match('/\b(\d\d\d\d)\b/isu',$info_box['established']['clean'], $match))
                  {
                    $new_school->start_date = $match[1];
                  }
                }
                $summary = trim($wikipedia->getIntroduction());
                $summary = preg_replace('/\n\s*\n/isu','',$summary);
                if (strlen($summary) > 10)
                {
                  $new_school->summary = $summary;      
                }      
                $new_school->save();
                $new_school->addReference($source = $wikipedia->getUrl(),
                                                                       $excerpt = null,
                                                                       $fields = array('summary'),
                                                                       $name = 'Wikipedia');
              }
              else
              {
                $new_school->save();
              }
              $current_school = $new_school;  
              $this->printDebug('Adding new school');
            }  
            $alias = new Alias;
            $alias->name = $school->institution;
            $alias->context = 'bw_school';
            $alias->Entity = $current_school;
            $alias->save();
          }
          
          //find degree
          $degree = null;
          if(!$degree = DegreeTable::getByText($school->degree))
          {
            $degree = DegreeTable::addDegree($school->degree);              
            $this->printDebug('Adding new degree');  
          }
          
          
          //find relationship
          $relationship = null;
          $relationships = $person->getRelationshipsWithQuery($current_school, RelationshipTable::EDUCATION_CATEGORY)->execute();
          foreach($relationships as $existing_relationship)
          {
            if ($existing_relationship->degree_id == $degree->id)
            {
              $relationship = $existing_relationship;
              break;
            }
          
          }        
            
          if($relationship)
          {          
            $this->printDebug('Relationship between person and school exists');
          }
          else
          {            
            $relationship = new Relationship;
            $relationship->Entity1 = $person;
            $relationship->Entity2 = $current_school;
            $relationship->description1 = 'student';
            $relationship->is_current = 0;
            if ($school->year)
            {
              $relationship->end_date = $school->year;
            }
            $relationship->setCategory('Education');
            $this->printDebug('Creating new relationship between person and school');
          }

          //save
          $relationship->save();          
          
          //add degree and reference
          if( $relationship->degree_id == null )
          {
            $reference_name = (strstr($school->source, 'wikipedia') ) ? "Wikipedia" : "BusinessWeek";            

            $relationship->Degree = $degree; 
            $relationship->save();
            $relationship->addReference( $source = $school->source,
                                       $excerpt = null,
                                       $fields=array('degree_id'),
                                       $name = $reference_name,
                                       $detail = null,
                                       $date = null);
                                       
            $this->printDebug('Adding degree and reference');

          }          
        }
      }
      else
      {
        $this->printDebug('No organization matches');
        return false;
      }
    }
    return true;
  }  
  
    
  private function getPersonsQuery()
  {
    $q = EntityTable::getByExtensionQuery(array('Person', 'BusinessPerson'))->limit($this->_limit);
    return $q;
  }


}
