<?php

class ScrapeUKMPCandidatesTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'scraper';
    $this->name             = 'uk-mp-candidates';
    $this->briefDescription = 'Scrapes UK MP candidate info';
    $this->detailedDescription = <<<EOF
Scrapes prospective MP info from http://yournextmp.com.
EOF;
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Test mode', false);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);

  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);      


    
    $scraper = new UKMPCandidateScraper($options['test_mode'], $options['debug_mode'], $this->configuration);

    
    $scraper->run();
  }
}