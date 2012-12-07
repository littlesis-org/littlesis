<?php

class ScrapeAddressesTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'scraper';
    $this->name             = 'addresses';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [cleanup:addresses|INFO] task does things.
Call it with:

  [php symfony cleanup:addresses|INFO]
EOF;
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'test mode', false);  
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'address limit', 50);  
    $this->addOption('address_id', null, sfCommandOption::PARAMETER_REQUIRED, 'address id', null); 
    $this->addOption('entity_id', null, sfCommandOption::PARAMETER_REQUIRED, 'entity id', null); 
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);
    $scraper = new AddressScraper($options['test_mode'], $options['debug_mode'], $this->configuration);		
		$scraper->setLimit($options['limit']);
		$addresses = null;
		if ($options['address_id'])
		{
		  $addresses = array(Doctrine::getTable('Address')->find($options['entity_id']));
		}
		else if ($options['entity_id'])
		{
		  $addresses = Doctrine::getTable('Address')->findByEntityId($options['entity_id']);
		}
		if ($addresses)
		{
		  $scraper->setAddresses($addresses);
		}
    $scraper->execute();	
    
  }
}