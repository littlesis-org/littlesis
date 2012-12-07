<?php

class S3ArchiveReferencesTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 's3';
    $this->name             = 'archive-references';
    $this->briefDescription = 'Downloads LittleSis references and uploads them to Amazon S3';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Maximum number of references to upload', 1000);
    $this->addOption('offset', null, sfCommandOption::PARAMETER_REQUIRED, 'Offset for reference query', 0);
    $this->addOption('overwrite', null, sfCommandOption::PARAMETER_REQUIRED, "Overwrite existing references on S3?", false);
    $this->addOption('localdir', null, sfCommandOption::PARAMETER_REQUIRED, "Optional local path to save references to instead of S3", false);
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
    
    $sql = "SELECT DISTINCT(r.source) FROM reference r ORDER BY id ASC LIMIT " . $options["limit"] . " OFFSET " . $options["offset"];
    $stmt = $this->db->execute($sql);
    $sources = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($sources as $source)
    {
      if (!$options["localdir"])
      {      
        $s3path = ReferenceTable::generateS3path($source);
  
        if (!$options["overwrite"] && $this->s3->getObjectInfo(sfConfig::get('app_amazon_s3_bucket'), $s3path) !== false)
        {
          print("ALREADY UPLOADED: " . $s3path . "\n");
          continue;
        }
      }

      $this->writeTmpFile($source, $options["debug_mode"], $options["localdir"]);

      if (!$options["localdir"])
      {
        $this->uploadTmpFile($source, $options["overwrite"], $options["debug_mode"]);
        
        if (unlink($this->getLocalPath($source, $options["local_dir"])) && $options["debug_mode"])
        {
          print("REMOVED LOCAL: " . $source . " [" . sha1($source) . "]\n");
        }
      }
      
      print("----------------------------------------------------------------\n");
    }
        
    //DONE
    LsCli::beep();
  } 
  
  
  function uploadTmpFile($source, $overwrite = false, $debug = false, $localdir = false)
  {
    $filePath = $this->getTmpPath($source, $localdir);
    $size = filesize($filePath);
    $input = $this->s3->inputResource($f = fopen($filePath, "r"), $size);
    $url = ReferenceTable::generateS3path($source);

    if (!$overwrite && $this->s3->getObjectInfo(sfConfig::get('app_amazon_s3_bucket'), $url) !== false)
    {
      print("ALREADY UPLOADED: " . $url . "\n");
      return;
    }

    if (S3::putObject($input, sfConfig::get('app_amazon_s3_bucket'), $url, S3::ACL_PUBLIC_READ)) 
    {
      print("UPLOADED: " . $url . "\n");
    }
    else
    {
      if ($debug)
      {
        print("Couldn't upload reference to S3: " . $url . "\n");
      }
    }    
  }

  function getLocalPath($source, $localdir = false)
  {
    return ($localdir ? $localdir : sfConfig::get('sf_temp_dir')) . DIRECTORY_SEPARATOR . sha1($source); 
  }
  
  function writeTmpFile($source, $debug = false, $localdir = false)
  {
    $defaultHeaders = array(
      'User-Agent' => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20061205 Iceweasel/2.0.0.1 (Debian-2.0.0.1+dfsg-2)'
    );    
    $b = new sfWebBrowser($defaultHeaders, 'sfCurlAdapter', array('cookies' => true));

    if ($b->get($source)->responseIsError())
    {
      return false;
    }

    $fileData = $b->getResponseText();
    unset($b);

    $filePath = $this->getLocalPath($source, $localdir);
    
    $localFile = fopen($filePath, 'w+b');
    fwrite($localFile, $fileData);

    if ($debug)
    {
      print("SAVED LOCAL: " . $source . " [" . sha1($source) . "] [" . strlen($fileData) . "]\n");
    }

    if (!fclose($localFile))
    {
      throw new Exception("Couldn't close file: " . $filePath);
    }  
  }
}