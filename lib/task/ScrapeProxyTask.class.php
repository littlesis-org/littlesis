<?php

class scrapeProxyTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'scraper';
    $this->name             = 'proxy';
    $this->briefDescription = 'Scrapes SEC proxies';
    $this->detailedDescription = <<<EOF
Scrapes SEC proxies
  [php symfony scrapeProxy|INFO]
EOF;

    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'test mode', false);
    
    $this->addOption('limit',null,sfCommandOption::PARAMETER_OPTIONAL, 'Set the limit of companies to scrape', '1');
    $this->addOption('start_id',null,sfCommandOption::PARAMETER_OPTIONAL, 'Set the id of the first (or only) public company to scrape', '1');
    $this->addOption('ticker',null,sfCommandOption::PARAMETER_OPTIONAL, 'Set the ticker of the public company to scrape', '');
 
  
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize database manager
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);      
    
    $proxy_scraper = new ProxyScraper($options['test_mode'], $options['debug_mode'], $this->configuration);
    $proxy_scraper->setCorpIds($options['limit'], $options['start_id'], $options['ticker']);
 
    $proxy_scraper->run();
 
  }
  

}