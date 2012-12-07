<?php

class CleanupPrimaryAliasesTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'cleanup';
    $this->name             = 'primary-aliases';
    $this->briefDescription = 'generates primary aliases for entities without one and removes duplicate primary aliases';
    $this->detailedDescription = <<<EOF
This task is for generating primary aliases for entities without one and removing duplicate primary aliases.
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


    //CREATE NEW PRIMARY ALIASES
    $q = Doctrine_Query::create()
      ->from('Entity e')
      ->where('NOT EXISTS (SELECT id FROM Alias a WHERE a.entity_id = e.id AND a.is_primary = ?)', true)
      ->limit($options['limit']);

    foreach ($q->fetchArray() as $entity)
    {
      $a = new Alias;
      $a->name = $entity['name'];
      $a->is_primary = true;
      $a->entity_id = $entity['id'];
      $a->save(false);
    }


    //REMOVE DUPLIATE PRIMARY ALIASES
    $q = Doctrine_Query::create()
      ->from('Alias a1')
      ->where('a1.is_primary = ?', true)
      ->andWhere('EXISTS (SELECT a2.id FROM Alias a2 WHERE a1.entity_id = a2.entity_id AND a2.is_primary = ? AND a1.id > a2.id)', true);
        
    foreach ($q->execute() as $alias)
    {
      $alias->delete();
    }
    
    
    //DONE
    LsCli::beep();
  }
}