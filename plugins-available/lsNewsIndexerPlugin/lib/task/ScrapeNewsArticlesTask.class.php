<?php

require_once(sfConfig::get('sf_root_dir') . '/lib/task/LsTask.class.php');

class ScrapeNewsArticlesTask extends LsTask
{
  protected $db = null;
  protected $startTime = null;
  protected $debugMode = null;
  protected $scrapers = array();


  protected function configure()
  {
    $this->namespace        = 'news';
    $this->name             = 'scrape';
    $this->briefDescription = 'Scrapes news articles from various sources';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Maximum number of articles to process', 500);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('sources', null, sfCommandOption::PARAMETER_REQUIRED, 'News sources to scrape for articles', 'nyt,wsj,wapo,bloomberg');    
  }


  protected function execute($arguments = array(), $options = array())
  {
    $this->init($arguments, $options);

    foreach ($this->scrapers as $scraper)
    {
      $scraper->run();
    }

    print "Scraped articles in " . (microtime(true) - $this->startTime) . " s\n";
  }


  protected function init($arguments, $options)
  {
    $this->startTime = microtime(true);

    $this->configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);
    
    $this->db = Doctrine_Manager::connection();
    $this->debugMode = $options['debug_mode'];
    
    //initialize scrapers now to avoid cookie/headers problems
    foreach (explode(',', $options['sources']) as $source)
    {
      $className = ucfirst($source) . 'ArticleScraper';
      
      if (class_exists($className))
      {
        $scraper = new $className(false, $options['debug_mode'], $this->configuration);
        $scraper->setLimit($options['limit']);
        $scraper->setSaveToDatabase(true);
        
        $this->scrapers[] = $scraper;
      }      
    }
  }
}