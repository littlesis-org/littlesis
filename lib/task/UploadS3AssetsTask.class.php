<?php

class UploadS3AssetsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 's3';
    $this->name             = 'upload-assets';
    $this->briefDescription = 'Uploads all LittleSis CSS and JavaScript files to Amazon S3';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('types', null, sfCommandOption::PARAMETER_REQUIRED, 'Asset types to upload', 'js,css');
    $this->addOption('check_first', null, sfCommandOption::PARAMETER_REQUIRED, "Don't upload if file already exists", false);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->s3 = new S3(sfConfig::get('app_amazon_access_key'), sfConfig::get('app_amazon_secret_key'));
    $types = explode(",", $options['types']);

    // hide strict errors
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
    
    
    foreach (array('js', 'css') as $type)
    {
      if (in_array($type, $types))
      {    
        $dir = sfConfig::get('sf_' . $type . '_dir');
        
        if ($handle = opendir($dir)) 
        {
          /* This is the correct way to loop over the directory. */
          while (false !== ($file = readdir($handle))) 
          {
            if (strpos($file, ".") == 0 || is_dir($dir . DIRECTORY_SEPARATOR . $file))
            {
              continue;
            }
  
            $this->uploadFile($type, $file, $options['check_first'], $options['debug_mode']);
          }
        
          closedir($handle);
        }
      }
    }

    
    //DONE
    LsCli::beep();
  } 
  
  
  private function _generateS3Path($type, $filename)
  {
    return $type . DIRECTORY_SEPARATOR . $filename;
  }
  
  
  function uploadFile($type, $filename, $check_first = true, $debug = false)
  {
    $localPath = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $filename;
    $input = $this->s3->inputResource($f = fopen($localPath, "rb"), filesize($s = $localPath));
    $uri = $this->_generateS3path($type, $filename);

    if ($check_first && $this->s3->getObjectInfo(sfConfig::get('app_amazon_s3_bucket'), $uri) !== false)
    {
      print($uri . " already exists on S3; skipping...\n");

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