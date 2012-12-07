<?php

class ScrapeListTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'scraper';
    $this->name             = 'list';
    $this->briefDescription = 'Scrapes generic lists';
    $this->detailedDescription = <<<EOF
Scrape
EOF;
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'test mode', false);  
    $this->addOption('list_id', null, sfCommandOption::PARAMETER_REQUIRED, 'list id', false);  
    $this->addOption('org_id', null, sfCommandOption::PARAMETER_REQUIRED, 'org id', false);  
    $this->addOption('description1', null, sfCommandOption::PARAMETER_REQUIRED, 'relationship description1', false);
    $this->addOption('urls', null, sfCommandOption::PARAMETER_REQUIRED, 'urls to look at', false);
    $this->addOption('regex', null, sfCommandOption::PARAMETER_REQUIRED, 'regex', false);  
    $this->addOption('is_board', null, sfCommandOption::PARAMETER_REQUIRED, 'is_board', false);
    $this->addOption('relationship_category', null, sfCommandOption::PARAMETER_REQUIRED, 'relationship category', 'Position');
    $this->addOption('org_org', null, sfCommandOption::PARAMETER_REQUIRED, 'is it an org org relationship', false);
    $this->addOption('org_extensions', null, sfCommandOption::PARAMETER_REQUIRED, 'org extensions for org org relationships', false);    
    $this->addOption('last_first', null, sfCommandOption::PARAMETER_REQUIRED, 'last name is first', false);    
  }


  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);

    $scraper = new ListScraper($options['test_mode'], $options['debug_mode'], $this->configuration);		
		$scraper->setListId($options['list_id']);
		$scraper->setUrls($options['urls']);
	  $scraper->setOrgId($options['org_id']);
    $scraper->setBoard($options['is_board']);
    $scraper->setRelationshipCategory($options['relationship_category']);
    $scraper->setDescription1($options['description1']);
	  $scraper->setRegex($options['regex']);	
	  $scraper->setOrgOrg($options['org_org']);
	  $scraper->setOrgExtensions($options['org_extensions']);
	  $scraper->setLastFirst($options['last_first']);
    $scraper->execute();	
  }
}

/*


symfony scraper:list --urls='http://www.telegraph.co.uk/news/newstopics/uselection2008/1904761/The-most-influential-US-political-pundits-50-41.html,http://www.telegraph.co.uk/news/newstopics/uselection2008/1906932/The-most-influential-US-political-pundits-40-31.html,http://www.telegraph.co.uk/news/newstopics/uselection2008/1913909/The-most-influential-US-political-pundits-30-21.html,http://www.telegraph.co.uk/news/newstopics/uselection2008/1913909/The-most-influential-US-political-pundits-20-11.html,http://preview.telegraph.co.uk/news/newstopics/uselection2008/article1920863.ece?token=-1880627018' --regex='/ong>(?<rank>\d\d?)\.\s*(?<name>[^<]+)\s*<\/strong>(?<bio>.*?)<s/su' --list_id=1










*/