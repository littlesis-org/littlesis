<?php

class LsGDAdapter extends sfGDAdapter{

	
  public function loadFile($thumbnail, $image)
  {
    $imgData = @GetImageSize($image);

    if (!$imgData)
    {
      throw new Exception(sprintf('Could not load image %s', $image));
    }

    if (in_array($imgData['mime'], $this->imgTypes))
    {
      $loader = $this->imgLoaders[$imgData['mime']];
      if(!function_exists($loader))
      {
        throw new Exception(sprintf('Function %s not available. Please enable the GD extension.', $loader));
      }

      $this->source = $loader($image);
      $this->sourceWidth = $imgData[0];
      $this->sourceHeight = $imgData[1];
      $this->sourceMime = $imgData['mime'];
      $thumbnail->initThumb($this->sourceWidth, $this->sourceHeight, $this->maxWidth, $this->maxHeight, $this->scale, $this->inflate);

      $this->thumb = imagecreatetruecolor($thumbnail->getThumbWidth(), $thumbnail->getThumbHeight());
			imagecolortransparent($this->thumb, imagecolorallocate($this->thumb, 0, 0, 0));
			imagealphablending($this->thumb, false);
			imagesavealpha($this->thumb, true);

			if ($imgData[0] == $this->maxWidth && $imgData[1] == $this->maxHeight)
      {
        $this->thumb = $this->source;
      }
      else
      {
        imagecopyresampled($this->thumb, $this->source, 0, 0, 0, 0, $thumbnail->getThumbWidth(), $thumbnail->getThumbHeight(), $imgData[0], $imgData[1]);
      }

      return true;
    }
    else
    {
      throw new Exception(sprintf('Image MIME type %s not supported', $imgData['mime']));
    }
  }
	
}

