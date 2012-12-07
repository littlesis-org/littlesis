<?php

class ScrapeFecTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'scraper';
    $this->name             = 'fec';
    $this->briefDescription = 'Scrapes donation details from FEC website';
    $this->detailedDescription = <<<EOF
Scrape
EOF;
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'test mode', false);  
    $this->addOption('year', null, sfCommandOption::PARAMETER_REQUIRED, 'comma-delimited years between 2000-2008', false);  
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'number of people', 10);  
    $this->addOption('entity_id',null, sfCommandOption::PARAMETER_REQUIRED, 'id of entity to scrape first', null);  
    $this->addOption('prompt',null, sfCommandOption::PARAMETER_REQUIRED, 'allow user to verify matches', false);
    $this->addOption('force',null, sfCommandOption::PARAMETER_REQUIRED, 'forces repeat scrapes', false);   
    $this->addOption('ignore_middle',null, sfCommandOption::PARAMETER_REQUIRED, 'ignore middle name', false);   
  }


  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);

    $scraper = new FecContributionScraper($options['test_mode'], $options['debug_mode'], $this->configuration);		
		$scraper->setLimit($options['limit']);
		$scraper->setYear($options['year']);	
		$scraper->setEntityId($options['entity_id']);
		$scraper->setPrompt($options['prompt']);
		$scraper->setForce($options['force']);
		$scraper->setIgnoreMiddle($options['ignore_middle']);
    $scraper->execute();	
  }
}