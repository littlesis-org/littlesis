<?php

class scrapeFortune1000Task extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'scraper';
    $this->name             = 'fortune1000';
    $this->briefDescription = 'Scrapes list of Fortune 1000 companies';
    $this->detailedDescription = <<<EOF
Scrapes Fortune 1000 lists, adds companies and info to database if they do not already exist, 
adds company to Fortune 1000 list if it is not already there, does nothing if this information is 
already in the database.
  [php symfony scrapeFortune1000|INFO]
EOF;

    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'test mode', false);  
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize database manager
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);      
    
    $fortune_scraper = new Fortune1000Scraper($options['test_mode'], $options['debug_mode'], $this->configuration);
    $fortune_scraper->run();
 
  }
  

}