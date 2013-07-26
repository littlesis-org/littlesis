<?php

class CreateSquareS3ImagesTask extends LsTask
{
  protected function configure()
  {
    $this->namespace        = 's3';
    $this->name             = 'create-square-images';
    $this->briefDescription = 'Creates square versions of large LittleSis images and uploads them to Amazon S3';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Maximum number of images to upload', 50000);
    $this->addOption('list_id', null, sfCommandOption::PARAMETER_REQUIRED, 'Limit to entities on this list', null);
    $this->addOption('size', null, sfCommandOption::PARAMETER_REQUIRED, 'Size of image in pixels', 300);
    $this->addOption('check_first', null, sfCommandOption::PARAMETER_REQUIRED, "Don't upload if file already exists", false);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      

    $this->debugMode = $options['debug_mode'];
    $this->db = Doctrine_Manager::connection();
    $this->s3 = new S3(sfConfig::get('app_amazon_access_key'), sfConfig::get('app_amazon_secret_key'));

    // hide strict errors
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
    
    //get array of active entity image filenames
    if ($options['list_id'])
    {
      $sql = "SELECT i.id, i.filename, i.url, i.entity_id, e.name, e.primary_ext " .
             "FROM ls_list_entity le " . 
             "LEFT JOIN image i ON (i.entity_id = le.entity_id) " .
             "LEFT JOIN entity e ON (e.id = le.entity_id) " .
             "WHERE le.list_id = ? AND le.is_deleted = 0 " .
             "AND i.is_deleted = 0 AND i.has_square = 0 " .
             "ORDER BY id DESC LIMIT " . $options['limit'];
      $params = array($options['list_id']);
    }
    else
    {
      $sql = "SELECT i.id, i.filename FROM image i " .
             "WHERE is_deleted = 0 AND has_square = 0 " . 
             "ORDER BY id DESC LIMIT " . $options['limit'];
      $params = array();
    }
    $stmt = $this->db->execute($sql, $params);    
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = count($images);

    foreach ($images as $image)
    {
      $this->printDebug("Processing image {$image['id']} belonging to entity {$image['entity_id']}...");

      if ($this->downloadLarge($image['filename']))
      {
        $this->printDebug("Downloaded large image from S3: " . $s3Path);      
      }
      else
      {
        $s3Path = ImageTable::generateS3Url(ImageTable::getPath($image['filename'], 'large'));
        $this->printDebug("Couldn't download large image from S3: " . $s3Path);

        if ($image['url'])
        {
          if ($this->downloadToTmp($image['url'], $image['filename']))
          {
            $this->printDebug("Downloaded original image: " . $image['url']);        
          }
          else
          {
            $this->printDebug("Couldn't download original image: " . $image['url']);        

            if ($this->downloadFromGoogle($image['name']))
            {
              $this->printDebug("Downloaded new image of {$image['name']} from google");        
            
            }
            else
            {
              $count--;
              continue;
            }
          }
        }
        else
        {
          $count--;
          continue;        
        }
      }

      if (!$this->createSquare($image['filename'], $options['size']))
      {
        $this->printDebug("Coudln't create square image: {$image['filename']}");
        $count--;
        continue;
      }

      if ($this->uploadFile($image['filename'], $options['check_first'], $options['debug_mode']))
      {
        $this->recordSquare($image['id']);
      }
      
      $count--;
      print($count . " images remaining...\n");
    }
    
    //DONE
    LsCli::beep();
  } 

  function downloadLarge($filename)
  {
    $url = ImageTable::generateS3Url(ImageTable::getPath($filename, 'large'));

    return $this->downloadToTmp($url, $filename);
  }
  
  function downloadToTmp($url, $filename)
  {
    $defaultHeaders = array(
      'User-Agent' => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20061205 Iceweasel/2.0.0.1 (Debian-2.0.0.1+dfsg-2)'
    );
    $b = new LsWebBrowser($defaultHeaders, 'sfCurlAdapter', array('cookies' => true), $followRedirects = false);

    if ($b->get($url)->responseIsError() || in_array($b->getResponseCode(), array(301, 302, 303, 307)))
    {
      return false;
    }

    $fileData = $b->getResponseText();
    unset($b);
    $filePath = sfConfig::get('sf_temp_dir') . DIRECTORY_SEPARATOR . $filename;    
    $localImage = fopen($filePath, 'wb');
    fwrite($localImage, $fileData);

    if (!fclose($localImage))
    {
      throw new Exception("Couldn't close file: " . $filePath);
    }
    
    return true;  
  }
  
  function downloadFromGoogle($name)
  {
    return false;
  }
  
  function createSquare($filename, $writeSize)
  {
    $tmpPath = sfConfig::get('sf_temp_dir') . DIRECTORY_SEPARATOR . $filename;
    $newFilename = $this->newFilename($filename);

    if (!self::imageMagickInstalled())
    {
      return false;
    }

    if (!$size = getimagesize($tmpPath))
    {
      return false;
    }   

    $savePath = sfConfig::get('sf_image_dir') . DIRECTORY_SEPARATOR . "square" . DIRECTORY_SEPARATOR . $newFilename; 

    if (($size[0] > $size[1] * 2) || ($size[1] > $size[0] * 2))
    {
      # pad to square if one dimension is more than twice the other dimension
      exec(sfConfig::get('app_imagemagick_binary_path') . " {$tmpPath} -virtual-pixel background -background white -set option:distort:viewport \"%[fx:max(w,h)]x%[fx:max(w,h)]-%[fx:max((h-w)/2,0)]-%[fx:max((w-h)/2,0)]\" -filter point -distort SRT 0 +repage {$savePath}");
    }
    else
    {
      # otherwise, crop to square  
      exec(sfConfig::get('app_imagemagick_binary_path') . " {$tmpPath} -virtual-pixel edge -set option:distort:viewport \"%[fx:min(w,h)]x%[fx:min(w,h)]+%[fx:max((w-h)/2,0)]+%[fx:max((h-w)/2,0)]\" -filter point -distort SRT 0 +repage {$savePath}");
    }

    # resize
    exec(sfConfig::get('app_imagemagick_binary_path') . " {$savePath} -resize {$writeSize}x{$writeSize} {$savePath}");

    unlink($tmpPath);

    return $savePath;    
  }
  
  function uploadFile($filename, $check_first = true, $debug = false)
  {
    $newFilename = $this->newFilename($filename);
    $localPath = sfConfig::get('sf_image_dir') . DIRECTORY_SEPARATOR . 'square' . DIRECTORY_SEPARATOR . $newFilename;
    $input = $this->s3->inputResource($f = fopen($localPath, "rb"), filesize($s = $localPath));
    $uri = ImageTable::generateS3path('square', $newFilename);

    if ($check_first && $this->s3->getObjectInfo(sfConfig::get('app_amazon_s3_bucket'), $uri) !== false)
    {
      return true;
    }

    if (S3::putObject($input, sfConfig::get('app_amazon_s3_bucket'), $uri, S3::ACL_PUBLIC_READ)) 
    {
      print("UPLOADED: " . $uri . "\n");
      return true;
    }
    else
    {
      if ($debug)
      {
        print("Couldn't upload image to S3: " . $uri . "\n");
      }
      
      return false;
    }
  }
  
  function recordSquare($id)
  {
    $sql = "UPDATE image SET has_square = 1 WHERE id = ?";
    $stmt = $this->db->execute($sql, array($id));
  }
  
  function imageMagickInstalled()
  {
    return (bool) (trim(shell_exec('which convert')) || trim(shell_exec(sfConfig::get('app_imagemagick_binary_path'))));
  }
  
  function newFilename($filename)
  {
    $fileType = sfConfig::get('app_images_square_file_type');

    if ($fileType != 'png')
    {
      return preg_replace("/png$/i", $fileType, $filename);
    }
    else
    {
      return $filename;
    }    
  }
}