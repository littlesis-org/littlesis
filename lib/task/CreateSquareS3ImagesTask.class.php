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
    $this->addOption('size', null, sfCommandOption::PARAMETER_REQUIRED, 'Size of image in pixels', 300);
    $this->addOption('check_first', null, sfCommandOption::PARAMETER_REQUIRED, "Don't upload if file already exists", false);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      

    $this->db = Doctrine_Manager::connection();
    $this->s3 = new S3(sfConfig::get('app_amazon_access_key'), sfConfig::get('app_amazon_secret_key'));

    // hide strict errors
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
    
    //get array of active entity image filenames
    $sql = "SELECT i.id, i.filename FROM image i WHERE is_deleted = 0 AND has_square = 0 ORDER BY id DESC LIMIT " . $options['limit'];
    $stmt = $this->db->execute($sql);    
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = count($images);

    foreach ($images as $image)
    {
      $this->downloadLarge($image['filename']);

      if (!$this->createSquare($image['filename'], $options['size']))
      {
        $this->printDebug("Coudln't create square image: {$image['filename']}");
      }

      if ($this->uploadFile($image['filename'], $options['check_first'], $options['debug_mode']))
      {
        $this->recordSquare($image['id']);
      }

      unlink(sfConfig::get('sf_temp_dir') . DIRECTORY_SEPARATOR . $filename);
      
      $count--;
      print($count . " images remaining...\n");
    }
    
    //DONE
    LsCli::beep();
  } 

  function downloadLarge($filename)
  {
    $url = ImageTable::generateS3Url(ImageTable::getPath($filename, 'large'));
    $defaultHeaders = array(
      'User-Agent' => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20061205 Iceweasel/2.0.0.1 (Debian-2.0.0.1+dfsg-2)'
    );
    $b = new sfWebBrowser($defaultHeaders, 'sfCurlAdapter', array('cookies' => true));

    if ($b->get($url)->responseIsError())
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
  }
  
  function createSquare($filename, $size)
  {
    $tmpPath = sfConfig::get('sf_temp_dir') . DIRECTORY_SEPARATOR . $filename;
    return ImageTable::createSquareFile($filename, $tmpPath, 'square', $size, $upload = false);
  }
  
  function uploadFile($filename, $check_first = true, $debug = false)
  {
    $localPath = sfConfig::get('sf_image_dir') . DIRECTORY_SEPARATOR . 'square' . DIRECTORY_SEPARATOR . $filename;
    $input = $this->s3->inputResource($f = fopen($localPath, "rb"), filesize($s = $localPath));
    $uri = ImageTable::generateS3path('square', $filename);

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
}