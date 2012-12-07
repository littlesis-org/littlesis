<?php

class cleanupReferencesTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'cleanup';
    $this->name             = 'references';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [cleanup:references|INFO] task does things.
Call it with:

  [php symfony cleanup:references|INFO]
EOF;
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Test mode', false);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);

  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);
    $refs = Doctrine::getTable('Reference')->findBySource('');
    foreach($refs as $ref)
    {
      $ref->delete();
    }
  }
}