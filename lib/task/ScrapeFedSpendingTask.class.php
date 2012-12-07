<?php

class ScrapeFedSpendingTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'scraper';
    $this->name             = 'fedspending';
    $this->briefDescription = 'Scrapes donation details from FedSpending website';
    $this->detailedDescription = <<<EOF
Scrape
EOF;
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'test mode', false);  
    $this->addOption('year', null, sfCommandOption::PARAMETER_REQUIRED, 'year span between 2000-2008', false);  
    $this->addOption('filing_limit', null, sfCommandOption::PARAMETER_REQUIRED, 'number of filings', 1000);  
    $this->addOption('org_limit', null, sfCommandOption::PARAMETER_REQUIRED, 'number of orgs', 10);  
    $this->addOption('round', null, sfCommandOption::PARAMETER_REQUIRED, 'name of round of scraping/meta name', 'fortune_04_06');  
  }


  protected function execute($arguments = array(), $options = array())
  {
		$databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);
  
    $scraper = new FedSpendingScraper($options['test_mode'], $options['debug_mode'], $this->configuration);		
		$scraper->setOrgLimit($options['org_limit']);
		$scraper->setRound($options['round']);
		$scraper->setFilingLimit($options['filing_limit']);
		$scraper->setYear($options['year']);	
		$scraper->execute();	
  }
}