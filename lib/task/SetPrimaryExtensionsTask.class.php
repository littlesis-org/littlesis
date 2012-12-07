<?php

class SetPrimaryExtensionsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'cleanup';
    $this->name             = 'set-primary-extensions';
    $this->briefDescription = 'sets primary_extension field for entities';
    $this->detailedDescription = <<<EOF
This taks is for setting the primary_extension field of entities.
EOF;
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'How many entities to perform this operation', 1000);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      

    $db = Doctrine_Manager::connection();

    try
    {
      $db->beginTransaction();
  
      $q = LsDoctrineQuery::create()
        ->from('Entity e')
        ->where('e.primary_ext IS NULL')
        ->andWhere('e.is_deleted IS NOT NULL')
        ->limit($options['limit']);
  
      foreach ($q->execute() as $entity)
      {
        //primary_extension automatically set upon save
        $entity->save();
      }
      
      $db->commit();
    }
    catch (Exception $e)
    {
      $db->rollback();
      throw $e;
    }
    
    LsCli::beep();
  }
}