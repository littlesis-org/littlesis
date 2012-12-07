<?php

class DropMainDbTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('force', null, sfCommandOption::PARAMETER_NONE, 'Whether to force dropping of the main database')
    ));

    $this->aliases = array('doctrine-drop-main-db');
    $this->namespace = 'doctrine';
    $this->name = 'drop-main-db';
    $this->briefDescription = 'Drops main database for current model';

    $this->detailedDescription = <<<EOF
The [doctrine:drop-db|INFO] task drops the main database:

  [./symfony doctrine:drop-db|INFO]

The task read connection information in [config/doctrine/databases.yml|COMMENT]:
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $doctrineManager = Doctrine_Manager::getInstance();
    $databaseManager = new sfDatabaseManager($this->configuration);

    $conn = $doctrineManager->openConnection($databaseManager->getDatabase('main')->getParameter('dsn'), 'main');
    Doctrine::dropDatabases('main');
  }
}