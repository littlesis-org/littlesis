<?php

abstract class ImageScraper extends Scraper{
  
  
  
	protected function attachImage(Entity $entity, $url, $title = '', $caption='', $is_featured = 1, $is_free = 0 )
	{


    if( preg_match('/^https/', $url) )
    {
      $this->printDebug( "HTTPS protocol");
      return false;
    }
    
    try
    {
      
      $filename =  ImageTable::createFiles($url, basename($url));		
    }
    catch(Exception $e)
    {
      $this->printDebug($e);
      return false;
    }


		if(!$filename)
    {
			$this->printDebug( "File could not be created");
			return false;			
		}
		
		$this->printDebug( "Creating file: " . $filename);

    //insert image row
    $image = new Image;
    $image->Entity = $entity;
    $image->filename = $filename;
    $image->url = $url;			
    $image->title = $title;
    $image->caption = $caption;
    $image->is_featured = $is_featured;
    $image->is_free = $is_free;
    $image->save();			
    $image->addReference($url, null, array('filename'));
    $this->printDebug( "Imported image with ID: " . $image->getId() );
    
    return true;			
  }
  
	protected function imageExists(Entity $entity)
  {    
		return (count($entity->Image)) ? true : false;		
	}
	
	protected function getEntityTypeWithOutImageQuery($extension)
	{		
		return EntityTable::getByExtensionQuery($extension)->leftJoin('e.Image i')->addWhere('i.id IS NULL')->groupby('e.id');		
	}
  
}
