<?php

class GenerateNamesTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'cleanup';
    $this->name             = 'generate-names';
    $this->briefDescription = 'generates canonical names for entities';
    $this->detailedDescription = <<<EOF
This taks is for generating canonical names for entities.
EOF;
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'How many entities to perform this operatio on', 1000);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      


    $db = Doctrine_Manager::connection();

    $q = LsDoctrineQuery::create()
      ->from('Entity e')
      ->leftJoin('e.Alias a')
      ->where('NOT EXISTS( SELECT id FROM alias a WHERE a.entity_id = e.id AND a.context IS NULL AND a.name = e.name)')
      ->limit($options['limit']);
  
    foreach ($q->execute() as $entity)
    {
      try
      {
        $db->beginTransaction();

        $primary = $entity->primary_ext; 

        if ($primary == 'Person')
        {
          $name = $entity->getExtensionObject('Person')->getFullName(false, $filterSuffix=true);

          //change entity name
          $entity->setEntityField('name', $name);
          $entity->save();
        }
        else
        {
          $name = $entity->rawGet('name');
        }


        //create primary Alias
        $a = new Alias;
        $a->Entity = $entity;
        $a->name = $name;
        $a->is_primary = true;
        $a->save();


        //create another Alias if there's a nickname
        if (($primary == 'Person') && $entity->name_nick)
        {
          $a = new Alias;
          $a->Entity = $entity;
          $a->name = ($primary == 'Person') ? $entity->name_nick . ' ' . $entity->name_last : $entity->name_nick;
          $a->is_primary = false;
          $a->save();
        }
      
        $db->commit();
      }
      catch (Exception $e)
      {
        $db->rollback();
        throw $e;
      }
    }

    LsCli::beep();
  }
}