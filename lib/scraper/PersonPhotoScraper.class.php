<?php

class PersonPhotoScraper extends ImageScraper{
	
	public function execute(){

		$persons = $this->getEntityTypeWithOutImageQuery('Person')->execute();

		//loop through persons
		foreach ($persons as $person)
		{				
			try 
			{
				//begin transaction
				$this->db->beginTransaction();
				$this->printDebug( "Fetching Google image for person: " . $person->name );
		
        switch(true){
          case true:
            $this->printDebug("-- Checking Wikipedia");
            if( $this->getPhotoFromWikipedia($person) )
            {
              $this->printDebug("++ Found in Wikipedia");
              break;              
            }       
          case true:
            $this->printDebug("-- Checking Google");
            if( $this->getPhotoFromGoogleImages($person))
            {
              $this->printDebug("++ Found in Google");
              break;              
            }        
        }
        
				if ($this->testMode) { continue; }		
		
				$person->save();
		
				//commit transaction
				$this->db->commit();
				
				$this->printDebug( $person->name . ": OK\n" );
			}
			catch (Exception $e)
			{
				//something bad happened, rollback
				$this->db->rollback();		
        throw $e;

			}	
		}
	}
	
	protected function getPhotoFromWikipedia(Entity $person)
	{
    
		if($this->imageExists($person)){
			return true;
		}
    
		//construct search query
    $wikipedia = new LsWikipedia;
    $response = $wikipedia->requestImages($person->name);

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
      
      
      $photo_url = $attributes['url'];
      $photo_alt = $attributes['comment'];

      //var_dump($photo_alt);
      $this->printDebug( "Checking: " . $photo_url );
      if(preg_match('/^http.*'.$person->name_last.'.*(jpg|jpeg|gif)$/i', $photo_url))
      {
        $this->printDebug("Photo found on URL: ".$photo_url );
        return $this->attachImage($person, $photo_url, 'Photograph');
      }
    }
    
    $this->printDebug( "Photo not found" );
    return false;
	}

	protected function getPhotoFromGoogleImages(Entity $person)
	{
    
		if($this->imageExists($person)){
			return true;
		}
    
		//construct search query
		$query = null;
    if ($person->name_middle)
		{
			$query = '"' . $person->name . '"';
		}
		else
		{
			if($org = $person->getRelatedEntitiesQuery('Org', RelationshipTable::POSITION_CATEGORY)->fetchOne())
      {
        $query = '"' . $person->name . '" ' . $org->name ;
      }
      else
      {
        $query = '"' . $person->name. '"';
      }
		}    
    
    $google = new LsGoogle;
    $google->setService('images');
    $google->setParameter('imgtype', 'face');
    $google->setQuery($query);
    $google->execute();
    $results = $google->getResults();				

    if($google->getNumResults() == 0)
    {
      return false;
    }
    
    
    foreach($results as $key => $result){
      $image_url = $result->url;
      $image_content = $result->contentNoFormatting;
      
      if( preg_match('/(jpg|jpeg|gif)$/i', $image_url) ){
        $this->printDebug("Checking ".  basename($image_url));
        $basefilename = basename($image_url);
        
				if (stristr($basefilename . " " . $image_content, $person->getNameLast()))
        {				
          //Entity $entity, $url, $title = 'title', $caption='caption', $is_featured = 1, $is_free = 0 
          $this->printDebug("Imported ".  $image_url);
          return $this->attachImage($person, $image_url, 'Photograph');	
        }
      }
    }
    $this->printDebug( "Photo not found" );
    return false;
	}

	
	

}
