<?php

class UploadS3ImagesTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 's3';
    $this->name             = 'upload-images';
    $this->briefDescription = 'Uploads all LittleSis images to Amazon S3';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Maximum number of images to upload', 50000);
    $this->addOption('types', null, sfCommandOption::PARAMETER_REQUIRED, 'Image types to upload', 'system,user,small,profile,large');
    $this->addOption('check_first', null, sfCommandOption::PARAMETER_REQUIRED, "Don't upload if file already exists", false);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      

    $this->db = Doctrine_Manager::connection();
    $this->s3 = new S3(sfConfig::get('app_amazon_access_key'), sfConfig::get('app_amazon_secret_key'));
    $types = explode(",", $options['types']);

    // hide strict errors
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
    
    
    if (count(array_intersect($types, array("profile", "small", "large"))))
    {
      //get array of active entity image filenames
      $sql = "SELECT i.filename FROM image i WHERE is_deleted = 0 ORDER BY id DESC LIMIT " . $options['limit'];
      $stmt = $this->db->execute($sql);    
      $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $count = count($images);

      foreach ($images as $image)
      {
        foreach ($types as $type)
        {
          if (in_array($type, array("system", "user")))
          {
            continue;
          }
  
          $this->uploadFile($type, $image['filename'], $options['check_first'], $options['debug_mode']);
        }
        
        $count--;
        print($count . " entities remaining...\n");
      }
    }


    if (in_array("system", $types))
    {    
      //do system images
      if ($handle = opendir(sfConfig::get('sf_image_dir') . "/system")) {
    
        /* This is the correct way to loop over the directory. */
        while (false !== ($file = readdir($handle))) 
        {
          if (strpos($file, ".") == 0)
          {
            continue;
          }

          $this->uploadFile("system", $file, $options['check_first'], $options['debug_mode']);                  
        }
      
        closedir($handle);
      }
    }


    if (in_array("user", $types))
    {
      //get array of active entity image filenames
      $sql = "SELECT p.filename FROM sf_guard_user_profile p WHERE p.filename IS NOT NULL";
      $stmt = $this->db->execute($sql);    
      $files = $stmt->fetchAll(PDO::FETCH_COLUMN);
      $count = count($files);
  
      foreach ($files as $file)
      {
        foreach (array("profile", "small") as $type)
        {
          $this->uploadFile($type, $file, $options['check_first'], $options['debug_mode']);
        }
        
        $count--;
        print($count . " users remaining...\n");
      }
    }  
    
    
    //DONE
    LsCli::beep();
  } 
  
  
  function uploadFile($type, $filename, $check_first = true, $debug = false)
  {
    $localPath = sfConfig::get('sf_image_dir') . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $filename;
    $input = $this->s3->inputResource($f = fopen($localPath, "rb"), filesize($s = $localPath));
    $uri = ImageTable::generateS3path($type, $filename);

    if ($check_first && $this->s3->getObjectInfo(sfConfig::get('app_amazon_s3_bucket'), $uri) !== false)
    {
      return;
    }

    if (S3::putObject($input, sfConfig::get('app_amazon_s3_bucket'), $uri, S3::ACL_PUBLIC_READ)) 
    {
      print("UPLOADED: " . $uri . "\n");
    }
    else
    {
      if ($debug)
      {
        print("Couldn't upload image to S3: " . $uri . "\n");
      }
    }
  }
}