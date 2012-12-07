<?php

class Forbes400Scraper extends ForbesScraper
{
  protected $list_urls = array(
    '1996'=> array('source_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=54&passYear=1996&passListType=Person&resultsStart=1&resultsHowMany=25&resultsSortProperties=-numberfield2%2C%2Bstringfield1&resultsSortCategoryName=worth', 
                 'reg_ex_match' => '',
                 'list_base_url' => '',
                 'profile_url' =>  '',
                 'img_src' => 'http://images.forbes.com/media/lists/54/2000/',
                 'append' => '',
                 'count_by' => 25,
                 'enabled' => false,                                             
                 'counting_exception' => false,                                          
                ),
    '1997'=> array('source_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=54&passYear=1997&passListType=Person&resultsStart=1&resultsHowMany=25&resultsSortProperties=-numberfield2%2C%2Bstringfield1&resultsSortCategoryName=worth', 
                 'reg_ex_match' => '',
                 'list_base_url' => '',
                 'profile_url' =>  '',
                 'img_src' => 'http://images.forbes.com/media/lists/54/2000/',
                 'append' => '',
                 'count_by' => 25,
                 'enabled' => false,                                             
                 'counting_exception' => false,                                            
                ),
    '1998'=> array('source_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=54&passYear=1998&passListType=Person&resultsStart=1&resultsHowMany=25&resultsSortProperties=-numberfield2%2C%2Bstringfield1&resultsSortCategoryName=worth', 
                 'reg_ex_match' => '',
                 'list_base_url' => '',
                 'profile_url' =>  '',
                 'img_src' => 'http://images.forbes.com/media/lists/54/2000/',
                 'append' => '',
                 'count_by' => 25,
                 'enabled' => false,                                             
                 'counting_exception' => false,                                           
                ),

    '1999'=> array('source_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=54&passYear=1999&passListType=Person&resultsStart=1&resultsHowMany=25&resultsSortProperties=-numberfield2%2C%2Bstringfield1&resultsSortCategoryName=worth', 
                 'reg_ex_match' => '',
                 'list_base_url' => '',
                 'profile_url' =>  '',
                 'img_src' => 'http://images.forbes.com/media/lists/54/2000/',
                 'append' => '',
                 'count_by' => 25,
                 'enabled' => false,                                             
                 'counting_exception' => false,                                            
                ),  
   
    '2000'=> array('source_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=54&passYear=2000&passListType=Person&resultsStart=1&resultsHowMany=25&resultsSortProperties=-numberfield2%2C%2Bstringfield1&resultsSortCategoryName=worth', 
                 'reg_ex_match' => '',
                 'list_base_url' => '',
                 'profile_url' =>  '',
                 'img_src' => 'http://images.forbes.com/media/lists/54/2000/',
                 'append' => '',
                 'count_by' => 25,
                 'enabled' => false,                                             
                 'counting_exception' => false,                                           
                ),
    
    '2001'=> array('source_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=54&passYear=2001&passListType=Person&resultsStart=1&resultsHowMany=25&resultsSortProperties=-numberfield2%2C%2Bstringfield1&resultsSortCategoryName=worth', 
                 'reg_ex_match' => '',
                 'list_base_url' => '',
                 'profile_url' =>  '',
                 'img_src' => 'http://images.forbes.com/media/lists/54/2001/',
                 'append' => '',
                 'count_by' => 25,
                 'enabled' => false,                                             
                 'counting_exception' => false,                                            
                ),
    
    '2002'=> array('source_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=54&passYear=2002&passListType=Person&resultsStart=1&resultsHowMany=25&resultsSortProperties=-numberfield2%2C%2Bstringfield1&resultsSortCategoryName=worth', 
                 'reg_ex_match' => '',
                 'list_base_url' => '',
                 'profile_url' =>  '',
                 'img_src' => 'http://images.forbes.com/media/lists/54/2002/',
                 'append' => '',
                 'count_by' => 25,
                 'enabled' => false,
                 'counting_exception' => false,                                           
                ),
    '2003'=> array('source_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=54&passYear=2003&passListType=Person&resultsStart=1&resultsHowMany=25&resultsSortProperties=-numberfield2%2C%2Bstringfield1&resultsSortCategoryName=worth', 
                 'reg_ex_match' => '',
                 'list_base_url' => '',
                 'profile_url' =>  '',
                 'img_src' => 'http://images.forbes.com/media/lists/54/2003/',
                 'append' => '',
                 'count_by' => 25,
                 'enabled' => false, 
                 'counting_exception' => false,                                           
                ),
    '2004'=> array('source_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=54&passYear=2004&passListType=Person&resultsStart=1&resultsHowMany=25&resultsSortProperties=-numberfield2%2C%2Bstringfield1&resultsSortCategoryName=worth', 
                  'reg_ex_match' => '/td class="mainlisttxt"><a href="\/finance\/lists\/54\/([^"]+)" /i',
                 'list_base_url' => '',
                 'profile_url' =>  '',
                 'img_src' => 'http://images.forbes.com/media/lists/54/2004/',
                 'append' => '',
                 'count_by' => 25,
                 'enabled' => false,
                 'counting_exception' => false,                                            
                ),
    '2005'=> array('source_url' => 'http://www.forbes.com/lists/2005/54/Rank_1.html', 
                 'reg_ex_match' => '/<a href="([A-Z1-9]{4}.html)">/is',
                 'list_base_url' => 'http://www.forbes.com/lists/2005/54/Rank_',
                 'profile_url' =>  'http://www.forbes.com/lists/2005/54/',
                 'img_src' => 'http://images.forbes.com/media/lists/54/2005/',
                 'append' => '.html',
                 'count_by' => 1,
                 'enabled' => false,
                 'counting_exception' => '',                                           
                ),
    '2006'=> array('source_url' => 'http://www.forbes.com/lists/2006/54/biz_06rich400_The-400-Richest-Americans_Rank.html', 
                 'reg_ex_match' => '/class="rowcolor"> <a href="([^"]+)">/i',
                 'list_base_url' =>  'http://www.forbes.com/lists/2006/54/biz_06rich400_The-400-Richest-Americans_Rank',
                 'profile_url' =>  'http://www.forbes.com/lists/2006/54/',
                 'img_src' => 'http://images.forbes.com/media/lists/54/2006/',
                 'append' => '.html',
                 'count_by' => 1,
                 'enabled' => true,                                             
                 'counting_exception' => 'underscore',                                            
                ),
    '2007'=> array('source_url' => 'http://www.forbes.com/lists/2007/54/richlist07_The-400-Richest-Americans_Rank.html', 
                 'reg_ex_match' => '/nowrap="nowrap"> <a href="([^"]+)">/i',
                 'list_base_url' =>  'http://www.forbes.com/lists/2007/54/richlist07_The-400-Richest-Americans_Rank',
                 'profile_url' =>  'http://www.forbes.com/lists/2007/54/',
                 'img_src' => 'http://images.forbes.com/media/lists/54/2007/',
                 'append' => '.html',
                 'count_by' => 1,                                     
                 'enabled' => true,                                             
                 'counting_exception' => 'underscore',                                          
                ),
    '2008'=> array('source_url' => 'http://www.forbes.com/lists/2008/54/400list08_The-400-Richest-Americans_Rank.html', 
                 'reg_ex_match' => '/td class="rowcolor" nowrap> <a href="([^"]+)">/i',
                 'list_base_url' =>  'http://www.forbes.com/lists/2008/54/400list08_The-400-Richest-Americans_Rank',
                 'profile_url' =>  'http://www.forbes.com/lists/2008/54/',
                 'img_src' => 'http://images.forbes.com/media/lists/54/2008/',
                 'append' => '.html',
                 'count_by' => 1,                                             
                 'enabled' => true,                                             
                 'counting_exception' => 'underscore',                                          
                )
  );

   protected function setListOptions(){
    $this->list_name = "The 400 Richest Americans";
    $this->list_description = "Forbes.com's list of the 400 Richest Americans";
    $this->list_fields="name, description, is_ranked";  
  }
  
	protected function import($url)
	{
		
    $person = null;
    $this->printDebug($url);    
		
    if (!$this->browser->get($url)->responseIsError())
		{
			$text = $this->browser->getResponseText();


      $bio = null;
      $name = null;
      $netWorth = null;
      $birthYear = null;
      $schools = null;
      $schools = null;
      $imageUrl = null;
      $rank = null;
			
			//get name & rank
			if ($this->year > 2005 && preg_match('/<b>#(\d+) ([^<]+)<\/b>/', $text, $match))
			{
				$name = trim($match[2]);
				$rank = $match[1];
			}
			if ($this->year == 2005 && preg_match('/<h2>#(\d+) ([^<]+)<\/h2>/', $text, $match))
			{
				$name = trim($match[2]);
				$rank = $match[1];
			}
      
			//get net worth
			if (preg_match('/Net Worth<\/span> <span class="red">\$([\S]+) billion/', $text, $match))
			{
				$netWorth = $match[1] * 1000000000;
			}
			
			//get birth year
			if (preg_match('/>Age<\/span> (\d+)/', $text, $match))
			{
				$birthYear = (date("Y") - $match[1])."-00-00";
			}

			//get schools
			if (preg_match('/Education<\/span>(.*)<\/td>/isU', $text, $match))
			{
				$schools = array();
				$schoolParts = explode('<br>', $match[1]);

				while ($schoolPart = current($schoolParts))
				{
					if (preg_match('/^([^,]+),\s+<b>([^<]+)<\/b>/is', trim($schoolPart), $match))
					{
						$schoolOrg = trim($match[1]);
						
						if ($schoolOrg == 'High School')
						{
							next($schoolParts);
							continue;
						}
						
						$schoolDegree = trim($match[2]);						
						$schools[] = array('org' => $schoolOrg, 'degree' => $schoolDegree);
					}
					
					next($schoolParts);
				}
			}
      

     
			if (preg_match('#<br>[\n\s]<br>(.+?)<br>[\n\s]<br>[\n\s]<img#isU', $text, $match ) )
			{
				$bio = strip_tags(trim($match[1]));
			}
      else{
        $wikipedia = new LsWikipedia;
        if( $wikipedia->request($name))
        {
          $bio = $wikipedia->getIntroduction();
        }
      }      
			
			//get image
      $regexp = '#([A-Z1-9]{4}).html#';
      if (preg_match($regexp, $url, $match))
			{

				$imageFilename = $match[1].".jpg";
				$imageUrl = $this->list_urls[$this->year]['img_src'].$imageFilename;
			}

			//echo "Rank: " . $rank . "\n";
			$this->printDebug( "Rank: " . $rank );
			$this->printDebug( "Name: " . $name );
			$this->printDebug( "Image: " . $imageUrl );
			$this->printDebug( "Net worth: " . $netWorth );
			$this->printDebug( "Birth year: " . $birthYear );
			$this->printDebug( "Bio: " . $bio );

      $person = $this->generatePerson($name, $bio);
      $person_exists = $this->getBusinessPersonQuery()->addWhere("person.name_first = ? AND person.name_last = ?", array( $person->name_first, $person->name_last  ) )->fetchOne();

      if( $person_exists != false ){
      
        $this->printDebug('Person exists');
        $person = $person_exists;
        
      }
      else{
        $this->printDebug('Saving new person');        
      }
        
			//parse name and create person object
			$person->addExtension('BusinessPerson');
      $person->start_date = ($person->start_date == null) ? $birthYear : $person->start_date;
			$person->summary = ($person->summary == null) ? $bio : $person->summary;
			$person->net_worth = ($person->net_worth == null) ? $netWorth : $person->net_worth;

      //go through schools person attended
      foreach($schools as $school)
      {
        
        //does the current school exist?
        $current_school = EntityTable::getByExtensionQuery('Org')->addWhere("org.name = ?", $school['org'])->fetchOne();
        if( $current_school ){
          $this->printDebug("  Found School " . $school['org'] ); 		
        }
        else{
          //clear cache
          Doctrine::getTable('ExtensionDefinition')->clear();
          
          $current_school = new Entity;
          $current_school->addExtension('Org');		
          $current_school->addExtension('School');				
          $current_school->name = LsLanguage::titleize($school['org']);				
          $current_school->save();
          $current_school->addReference($source = $url, 
                                          $excerpt=null, 
                                          $fields=array('name'), 
                                          $name='Forbes.com', 
                                          $detail=null, 
                                          $date=null);
          $this->printDebug( "  Adding new school: " . $school['org'] ); 				
        }
  
        
        //if there is no relationship between person and school. connect them!
        if(!$person->getRelationshipsWithQuery( $current_school, RelationshipTable::EDUCATION_CATEGORY)->fetchOne() )
        {
          $this->printDebug( "  Creating Relation between " . $current_school->name . " and ". $person->name ); 				
          
          $education = new Relationship;
          $education->Entity1 = $person;
          $education->Entity2 = $current_school;				
          $education->setCategory('Education');
          $education->description1 = $school['degree'];
          $education->is_current = 1;
          $education->save();
          $education->addReference($source = $url, 
                                      $excerpt=null, 
                                      $fields=array('description1'), 
                                      $name='Forbes.com', 
                                      $detail=null, 
                                      $date=null);
        }
    
      }


      $person->save();      
      $person->addReference($source = $url, 
                                      $excerpt=null, 
                                      $fields=array('name_prefix', 'name_first', 'name_middle', 'name_last', 'name_suffix', 'name_nick', 'summary', 'net_worth', 'start_date'), 
                                      $name='Forbes.com', 
                                      $detail=null, 
                                      $date=null);

      
      $this->saveToList($person, $rank);
      $this->attachImage($person,  $imageUrl);

		}
		else
		{
			echo "Couldn't get person: " . $url . "\n";
		}
	}

	private function getBusinessPersonQuery(){
		return EntityTable::getByExtensionQuery('Person');
	}
  


  
}
