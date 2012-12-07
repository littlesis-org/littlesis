<?php


class LsThumbnail extends sfThumbnail{
	
  public function __construct($maxWidth = null, $maxHeight = null, $scale = true, $inflate = true, $quality = 75, $adapterClass = null, $adapterOptions = array())
  {		
		$adapterClass = 'LsGDAdapter';
    $this->adapter = new $adapterClass($maxWidth, $maxHeight, $scale, $inflate, $quality, $adapterOptions);
  }
	
}

