<?php

class ScrapeLobbyingTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'scraper';
    $this->name             = 'lobbying';
    $this->briefDescription = 'Scrapes lobbying data';
    $this->detailedDescription = <<<EOF
Scrapes lobbying data from Senate.gov.
EOF;
    
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('limit',null,sfCommandOption::PARAMETER_OPTIONAL, 'Set the limit of orgs to find lobbying data for', '1');
    $this->addOption('start_id',null,sfCommandOption::PARAMETER_OPTIONAL, 'Set the id of the first (or only) org to find lobbying data for', '1');
    $this->addOption('mode',null,sfCommandOption::PARAMETER_OPTIONAL, "Set to either 'import' data to raw or 'mine' for use", 'import');
    $this->addOption('filing_id',null,sfCommandOption::PARAMETER_OPTIONAL, '', null);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'test mode', false);
    $this->addOption('continuous', null, sfCommandOption::PARAMETER_REQUIRED, 'picks up where scraper last left off', false);

  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);      
    $scraper = new LobbyingScraper($options['test_mode'], $options['debug_mode'], $this->configuration);
    $scraper->setMode($options['mode']);
    $scraper->setContinuous($options['continuous']);
    if ($options['filing_id'])
    {
      $scraper->setFilingId($options['filing_id']);
    }
    $scraper->setOrgIds($options['limit'], $options['start_id']);
    $scraper->run();
  }
  
}