<?php

class LogoScraper extends ImageScraper{
	
	
	public function execute()
	{		
		//loop through persons
		$orgs = $this->getEntityTypeWithOutImageQuery('Org')->execute();

		foreach ($orgs as $org)
		{
			//get DB connection for transactions		
			try 
			{				
				//begin transaction
				$this->db->beginTransaction();
				$this->printDebug("Importing Logo for: " . $org->getName());

        switch(true){
          case true:
            $this->printDebug("-- Checking BrandsofthetheWorld");
            if( $this->getLogoFromBrandsOfTheWorld($org) )
            {
              $this->printDebug("++ Found in BrandsofthetheWorld");
              break;              
            }
          case true:
            $this->printDebug("-- Checking Wikipedia");
            if( $this->getLogoFromWikipedia($org) )
            {
              $this->printDebug("++ Found in Wikipedia");
              break;              
            }       
          case true:
            $this->printDebug("-- Checking Google");
            if( $this->getLogoFromGoogleImage($org))
            {
              $this->printDebug("++ Found in Google");
              break;              
            }        
        }
        
				if ($this->testMode) { continue; }		
		
				//commit transaction
				$this->db->commit();
				
				$this->printDebug($org->getName() . ": OK\n");
			}
			catch (Exception $e)
			{
				//something bad happened, rollback
				$this->db->rollback();
        throw $e;
			}
		}
	}
		

	protected function getLogoFromBrandsOfTheWorld(Entity $org, $in_depth = 0)
	{
		
		if($this->imageExists($org)){
			return true;
		}
		
    $logoBaseUrl =  "http://brandsoftheworld.com";
    $logoSearchUrl =  "http://brandsoftheworld.com/search/";
    $success = null;
    
		$name = $org->getName();	
		$name = preg_replace('/[,.]/', ' ', $name);
		$name = preg_replace('/\s{2,}/', ' ', $name);
		
		$nameParts = array_diff(explode(' ', $name), array_merge( LsLanguage::$business, LsLanguage::$businessAbbreviations));
		$cleanName = trim(implode(' ', $nameParts));		
		
		$searchTerm = trim(urlencode($cleanName));
		//$this->printDebug("Company URL: " . $org->website);			

		//$this->printDebug("Search Term: " . $searchTerm );
		$this->browser->post($logoSearchUrl);

		$this->browser->setField('search_query', $searchTerm);
		
		
		if (!$this->browser->click('Search')->responseIsError())
		{
			
			$text = $this->browser->getResponseText();			
			$return_url = $this->browser->getUrlInfo();			
			$direct_match = preg_match('/html$/i', $return_url['path']);
			
			$companies = null;

			$this->printDebug($logoBaseUrl. $return_url['path']);
			
			if($direct_match)
			{
				$companies[]['matching_url'] = $return_url['path'];				
				$this->printDebug("Name match: Direct Hit");
			}
			else
			{
				//get first match
				preg_match_all('/<tr><td><a href="(.+)">(.+)<\/a><\/td><\/tr>/U', $text, $result_matches);
		
				foreach($result_matches[2] as $key=> $organization)
				{	
					$organization_match_array = explode(' ', $organization);
					$organization_name_array = array_diff(explode(' ', $org->name), array_merge( LsLanguage::$business, LsLanguage::$businessAbbreviations));
					
					if(trim($organization) == $org->name)
					{
						$companies[]['matching_url'] = $result_matches[1][$key];
					}
					else if(array_intersect($organization_match_array, $organization_name_array) && $in_depth){
						$companies[]['matching_url'] = $result_matches[1][$key];
					}
				}	
				
				if($companies_found = count($companies))
				{
					//$this->printDebug("Name match: Exact name found on search result");
					//$this->printDebug("Companies found with mathing name: " . $companies_found);						
				}
				else
				{
					$this->printDebug("Name match: Company name not found on search result");
					return false;				
				}
			}
			
			foreach($companies as $key=> $company)
			{
				
				//$this->printDebug("Matching URL: " . $logoBaseUrl . $company['matching_url'] );
				$logo_text = null;
				$logo_image_url= null;
				if ( !$this->browser->get($logoBaseUrl . $company['matching_url'])->responseIsError() )
				{	
					$logo_text = $this->browser->getResponseText();					
					preg_match('/<div class="brandIcon">\n<a href="\/download\/brand\/(.+).html"><img src="(.+)"/iU', $logo_text, $match);				
					$logo_image_url = $match[2];				
				}
				else
				{
					$this->printDebug("Logo URL not found");
					continue;
				}
				
				$this->printDebug("Logo URL: " . $logoBaseUrl . $logo_image_url);			
				
				
				//look for exact website match
				if ($org->website)
				{
					
					$website = strtolower(str_ireplace('http://www.', '', $org->website));
					// get last two segments of host name
					//preg_match('/[^.]+\.[^.]+$/', strtolower($org->website), $website);
					//$website = $website[0];
					$regex = str_replace('/', '\/', $website);
					$regex = str_replace('.', '\.', $regex);
					preg_match('/' . $regex . '/', $logo_text, $match);
					
          //Entity $entity, $url, $title = 'title', $caption='caption', $is_featured = 1, $is_free = 0 

					if ($match)
					{
						$attached = $this->attachImage($org, $logoBaseUrl . $logo_image_url, 'Organization logo' );
            if( $attached )
            {
              $this->printDebug( "Saved");
              return true;
            }
					}
					else
					{
						//$this->printDebug("Website could not be confirmed for logo");
						continue;
					}
				}
				else{
						//$this->printDebug("Database does not have URL saved");															
						continue;
				}
			}
			return false;
		}
		else
		{
			$this->printDebug("Couldn't get " . $logoSearchUrl );
			return false;
		}
	}
	
	
	protected function getLogoFromGoogleImage(Entity $org)
	{
    
		if($this->imageExists($org)){
			return true;
		}
    
		//construct search query
		$nameParts = array_diff(explode(' ', $org->name), array_merge( LsLanguage::$business, LsLanguage::$businessAbbreviations));
		$cleanName = trim(implode(' ', $nameParts));		
		$query =  $cleanName .  ' logo';
		$this->printDebug( "Querying Google with term: " . $query );
    
    
    $google = new LsGoogle;
    $google->setService('images');
    $google->setQuery($query);
    $google->execute();
    $results = $google->getResults();				

    foreach($results as $key => $result){
      $image_url = $result->url;
      $image_content = $result->contentNoFormatting;

      $this->printDebug("Checking: ".  $image_url);          
      
      if( preg_match('/(png|gif|jpg)$/i', $image_url) ){
        $this->printDebug("Checking ".  $image_url);
        $basefilename = basename($image_url);
        //$organization_name_parts = array_diff(explode(' ', strtolower($org->name)), array_merge( LsLanguage::$business, LsLanguage::$businessAbbreviations));
        $organization_name_parts = split("[ \.\_\-]", strtolower($org->name) );
        $organization_name_parts[] = "logo";
        $organization_name_parts[] = "seal";

        $organization_match_parts = LsArray::arrayTrim(split("[ \.\_\-]", preg_replace("/[0-9]/","", strtolower(basename(urldecode($basefilename)). " ". urldecode($image_content) )) ));

        $intersect = array_intersect($organization_name_parts, $organization_match_parts);

        //var_dump($organization_name_parts);
        //var_dump($organization_match_parts);
        //var_dump($intersect);
        
        if( count($intersect) >= 2 )
        {				
          //Entity $entity, $url, $title = 'title', $caption='caption', $is_featured = 1, $is_free = 0 
          
          $attached = $this->attachImage($org, $image_url, 'Organization logo');
          if( $attached )
          {
            $this->printDebug( "Saved");
            return true;
          }
          
        }
      }
    }
    $this->printDebug( "Logo not found on Google" );
    return false;
	}

	
	
	protected function getLogoFromWikipedia(Entity $org)
	{

		if($this->imageExists($org)){
			return true;
		}
    
		//construct search query
    $wikipedia = new LsWikipedia;
    $response = $wikipedia->requestImages($org->name);

    if(!$response)
    {
      return false;
    }
    $matches = $response->query->pages->page;    
    $this->printDebug( "Images on Wikipedia page: " . count($matches) );

    foreach ($matches as $match)
    {
      $ii = (array)$match->imageinfo->ii;
      $attributes = $ii['@attributes'];
      
      
      $logo_image_url = $attributes['url'];
      $logo_image_alt = $attributes['comment'];
      
      if(preg_match('/^http.*(Wikinews|Wikiversity|Wikisource|Wikiquote|Wikibooks|Wiktionary|Commons-logo)/i', $logo_image_url)){
        continue;
      }				
      
      $this->printDebug( "Checking:  ".$logo_image_url );
      
      $org_name_parts = split("[ \.\_\-]", strtolower($org->name) );
      $org_name_parts[] = "logo";
      $org_name_parts[] = "seal";

      $image_name_parts = split("[ \.\_\-]", preg_replace("/[0-9]/","", strtolower(basename(urldecode($logo_image_url)))) );
      $intersect = array_intersect($image_name_parts, $org_name_parts);
      
      if( count($intersect) >= 2 )
      {
        if($this->attachImage($org, $logo_image_url, 'Organization logo'))
        {
          $this->printDebug( "Saved");
          return true;
        }        
      }

      
      /*
      if((preg_match('/^http.*(logo|seal).*(png|gif|svg|jpg)$/i', $logo_image_url)) )
      {
        $this->printDebug( "Found 1");
        if($this->attachImage($org, $logo_image_url, 'Organization logo'))
        {
          $this->printDebug( "Committed");
          return true;
        }
      }

      if( (preg_match('/(png|gif|svg|jpg)$/i',$logo_image_url) && preg_match('/\b(logo|seal)\b/i',$logo_image_alt)) )
      {
        $this->printDebug( "Found 2");
        if($this->attachImage($org, $logo_image_url, 'Organization logo'))
        {
          $this->printDebug( "Committed");
          return true;
        }
      }
      
      if(preg_match('/^http.*'.str_replace(LsLanguage::$punctuations, '_', $org->name).'.+\.(png|gif|svg)$/i', $logo_image_url))
      {
        $this->printDebug( "Found 3");
        if( $this->attachImage($org, $logo_image_url, 'Organization logo'))
        {
          $this->printDebug( "Committed");
          return true;  
        }        
      }
      */
    }
    
    $this->printDebug( "Logo not found on Wikipedia" );
    return false;
	}

}
