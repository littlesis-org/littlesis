<?php

class DataDumpCleanTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'doctrine';
    $this->name             = 'data-dump-clean';
    $this->briefDescription = 'Creates data fixture file without versioning or sfGuardDoctrineUser records';
    $this->detailedDescription = <<<EOF
Creates data fixture file without revision logs or sfGuardUser-related records. 
This fixture file can then be loaded without creating redundant versioning logs
causing duplicate errors (sfGuardDoctrineUser has its own fixture file).
EOF;
		
    $this->addOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($options['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);
    
    $dir = sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . 'fixtures';
    $excludedModels = array(
      'Modification', 
      'ModificationField',
    );
    Doctrine::loadModels(sfConfig::get('sf_lib_dir') . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'doctrine');
    $loadedModels = Doctrine::getLoadedModels();
    $models = array_diff($loadedModels, $excludedModels);
    
    $data = new Doctrine_Data();
    $data->exportData($dir, 'yml', $models, false);
  }
}