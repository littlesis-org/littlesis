<?php

class ScrapeSchoolsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'scraper';
    $this->name             = 'schools';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
Scrape
EOF;
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'test mode', false);  
    $this->addOption('year', null, sfCommandOption::PARAMETER_REQUIRED, 'year span between 2000-2008', false);  
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'number of companies', false);  
  }


  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);

    $scraper = new SchoolScraper($options['test_mode'], $options['debug_mode'], $this->configuration);		
		$scraper->setLimit($options['limit']);
    $scraper->execute();	
  }
}
