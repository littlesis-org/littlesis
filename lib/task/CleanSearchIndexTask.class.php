<?php

class CleanSearchIndexTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'search';
    $this->name             = 'clean-index';
    $this->briefDescription = 'Generates entity index for Zend Lucene Search';
    $this->detailedDescription = <<<EOF
This task generates index files for entities to be used by Zend Lucene Search
EOF;
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod');
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of entities to index', 200);
    $this->addOption('offset', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of entities to skip before indexing', 0);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', false);
    $this->addOption('optimize_freq', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of entries to index before optimizing', 50);
  }

  public function safeToRun()
  {
    $proc_id = getmypid();
    exec('ps aux -ww | grep symfony | grep search:generate-index | grep -v ' . $proc_id . ' | grep -v grep', $status_arr);
    foreach ($status_arr as $status)
    {
      //sometimes the shell startup command also appears, which is fine (script is still safe to run)
      if (preg_match('/sh\s+\-c/isu', $status) == 0)
      {
        return false;
      }
    }
    
    return true;
  }

  protected function execute($arguments = array(), $options = array())
  {
    if (!$this->safeToRun())
    {
      print("Process already running!\n");
      die;
    }


    $timer = sfTimerManager::getTimer('execute');

    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);      


    //set up index
    $index = EntityTable::getLuceneIndex();


    //delete deleted entities
    $q = LsDoctrineQuery::create()
      ->from('Entity e')
      ->where('e.is_deleted = ?', true)
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);
      
    foreach ($q->execute() as $entity)
    {
      if ($hits = $index->find('key:' . $entity['id']))
      {
        if ($options['debug_mode'])
        {
          printf("Deleting index for Entity %s\n", $entity['id']);
        }

        foreach ($hits as $hit)
        {
          $index->delete($hit->id);
        }        
      }
    }

    
    printf("Memory used: %s\n", LsNumber::makeBytesReadable(memory_get_usage()));
    printf("Index size: %s\n", $index->count());

    $timer->addTime();
    printf("Run time: %s\n", $timer->getElapsedTime());
    sfTimerManager::clearTimers();
  }
}