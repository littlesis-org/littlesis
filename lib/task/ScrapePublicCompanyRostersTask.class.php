<?php

class ScrapePublicCompanyRostersTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'scraper';
    $this->name             = 'sec';
    $this->briefDescription = 'Scrapes execs and dirs from SEC website';
    $this->detailedDescription = <<<EOF
Cycles through form 4s for public corporation, finds roster of directors and 
executives, checks them against latest proxy to see if they are current or
past directors or executives.
EOF;
  
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
   
    $this->addOption('limit',null,sfCommandOption::PARAMETER_OPTIONAL, 'Set the limit of companies to scrape', '1');
    $this->addOption('start_id',null,sfCommandOption::PARAMETER_OPTIONAL, 'Set the id of the first (or only) public company to scrape', null);
    $this->addOption('ticker',null,sfCommandOption::PARAMETER_OPTIONAL, 'Set the ticker of the public company to scrape', null);
    $this->addOption('search_depth',null,sfCommandOption::PARAMETER_OPTIONAL, 'Set the number of form 4 search index pages to scrape.  There are links to 10 form 4s on each page, many of them duplicates.  The number needed to get to all directors and executives varies. 50 is a thorough but slow search, 10 is adequate but faster.', '10');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'test mode', false);
    $this->addOption('years', null, sfCommandOption::PARAMETER_REQUIRED, 'Filing years to look at', '2009,2010');
    $this->addOption('repeat_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Repeat scrape for already-scraped company', false);
    $this->addOption('list_id',null,sfCommandOption::PARAMETER_OPTIONAL, 'ID of list from which to get companies to scrape', null);
  }

  protected function execute($arguments = array(), $options = array())
  {

    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);      
    $scraper = new PublicCompanyScraper($options['test_mode'], $options['debug_mode'], $this->configuration, null, $browserTimeout=60);

    if ($options['ticker'])
    {
      $scraper->setCompanyByTicker($options['ticker']);
    }
    else if ($options['start_id']) 
    {
      $scraper->setStartId($options['start_id']);
    }
    
    $scraper->setSearchDepth($options['search_depth']);
    $years = explode(',',$options['years']);
    $scraper->setYears($years);
    $scraper->setLimit($options['limit']);

    if ($options['repeat_mode'])
    {
      $scraper->setRepeatMode(true);
    }

    if ($options['list_id'])
    {
      $scraper->setListId($options['list_id']);
    }


    $scraper->run();
  }
  
}