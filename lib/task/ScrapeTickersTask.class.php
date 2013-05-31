<?php

class ScrapeTickersTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'scraper';
    $this->name             = 'tickers';
    $this->briefDescription = 'grabs tickers for corps on exchanges';
    $this->detailedDescription = <<<EOF
Parses exchanges lists of tickers and adds companies to db.
EOF;
  
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('limit',null,sfCommandOption::PARAMETER_OPTIONAL, 'limit on rows to process', 100);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'test mode', false);
    $this->addOption('min_market_cap', null, sfCommandOption::PARAMETER_REQUIRED, 'minimum market cap of companies to be added', 2000000000);
    $this->addOption('exchange', null, sfCommandOption::PARAMETER_REQUIRED, 'exchange -- nyse, amex, nasdaq', 'nasdaq');
  }

  protected function execute($arguments = array(), $options = array())
  {

    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);      
    $scraper = new TickerScraper($options['test_mode'], $options['debug_mode'], $this->configuration, null, $browserTimeout=60);
    if (!in_array($options['exchange'],array('nasdaq','amex','nyse')))
    {
      $this->printDebug("you need to set exchange to nasdaq, amex, or nyse");
    }
    $scraper->setMinMarketCap($options['min_market_cap']);
    $scraper->setExchange($options['exchange']);
    $scraper->run();
  }
  
}