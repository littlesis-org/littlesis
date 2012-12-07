<?php

class scrapeEmployeeInfoTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'scraper';
    $this->name             = 'employeeinfo';
    $this->briefDescription = 'Scrapes info for employees of orgs';
    $this->detailedDescription = <<<EOF
Scrapes basic info for orgs.
  [php symfony scrapeFortune1000|INFO]
EOF;

    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'test mode', false);  
    $this->addOption('meta_name', null, sfCommandOption::PARAMETER_REQUIRED, 'set the name of the set of bios/info being scraped', 'lobbyist_bios');  
    $this->addOption('org_extensions', null, sfCommandOption::PARAMETER_REQUIRED, 'set the extensions of orgs whose employees will be scraped', 'LobbyingFirm');  
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize database manager
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);      
    
    $scraper = new EmployeeInfoScraper($options['test_mode'], $options['debug_mode'], $this->configuration);
    $scraper->setMetaName($options['meta_name']);
    $scraper->setOrgExtensions($options['org_extensions']);
    $scraper->run();
 
  }
  

}