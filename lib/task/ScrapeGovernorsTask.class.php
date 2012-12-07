<?php

class scraperGovernorsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'scraper';
    $this->name             = 'governors';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [scraper:governors|INFO] task does things.
Call it with:

  [php symfony scraper:governors|INFO]
EOF;
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'test mode', false);  
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);

    $scraper = new GovernorScraper($options['test_mode'], $options['debug_mode'], $this->configuration);		
    $scraper->execute();	
  }
}