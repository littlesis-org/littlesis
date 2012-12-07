<?php

class ScrapePersonPhotoTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'scraper';
    $this->name             = 'photos';
    $this->briefDescription = 'Scrapes donation details from FEC website';
    $this->detailedDescription = <<<EOF
Scrape
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
  
    $scraper = new PersonPhotoScraper($options['test_mode'], $options['debug_mode'], $this->configuration);		
    $scraper->execute();
  }
}
