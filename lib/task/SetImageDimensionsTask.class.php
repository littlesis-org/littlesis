<?php

class SetImageDimensionsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'cleanup';
    $this->name             = 'set-image-dimensions';
    $this->briefDescription = 'random suff';
    $this->detailedDescription = <<<EOF
This task makes sure all Images have a width and height derived from the image file.
EOF;
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      


    //GET IMAGES LACKING WIDTH OR HEIGHT
    $rows = LsDoctrineQuery::create()
      ->from('Image i')
      ->where('i.width IS NULL OR i.height IS NULL')
      ->fetchArray();

    echo "Found " . count($rows) . " images lacking width or height...\n";

    foreach ($rows as $row)
    {
      $path = sfConfig::get('sf_root_dir') . '/web/images/profile/' . $row['filename'];

      if (!file_exists($path))
      {
        echo "Couldn't find file: " . $row['filename'] . "; skipping\n";
        continue;
      }

      list($width, $height) = getimagesize($path);
      
      $result = LsDoctrineQuery::create()
        ->update('Image i')
        ->set('i.width', $width)
        ->set('i.height', $height)
        ->where('i.id = ?', $row['id'])
        ->execute();        
    }
    
    echo "Done!\n";
  }
}