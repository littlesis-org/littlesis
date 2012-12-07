<?php

class CongressMemberScraperTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'scraper';
    $this->name             = 'congress';
    $this->briefDescription = 'Scrapes congress member info';
    $this->detailedDescription = <<<EOF
Scrapes congress member info from http://bioguide.congress.gov.
EOF;
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Test mode', false);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of congress members to import at a time', null);
    $this->addOption('sessions', null, sfCommandOption::PARAMETER_REQUIRED, 'List or range of congressional sessions to import', '111');
    $this->addOption('update_existing', null, sfCommandOption::PARAMETER_REQUIRED, 'pvs sunlight data update', false);
    $this->addOption('offset', null, sfCommandOption::PARAMETER_REQUIRED, 'offset', 0);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);      

    if (strstr($options['sessions'], '-'))
    {
      list($first, $last) = explode('-', $options['sessions']);
      $sessions = range($first, $last);
    }
    else
    {
      $sessions = explode(',', $options['sessions']);
    }
    
    $scraper = new CongressMemberScraper($options['test_mode'], $options['debug_mode'],$this->configuration);
    $scraper->setSessions($sessions);
    if ($options['limit'])
    {
      $scraper->setLimit($options['limit']);
    }

    if($options['update_existing'])
    {
      $scraper->updateExisting($options['offset']);
    }
    else
    {
      $scraper->run();
    }
  }
}