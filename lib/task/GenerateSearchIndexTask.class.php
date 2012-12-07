<?php

class GenerateSearchIndexTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'search';
    $this->name             = 'generate-index';
    $this->briefDescription = 'Generates entity index for Zend Lucene Search';
    $this->detailedDescription = <<<EOF
This task generates index files for entities to be used by Zend Lucene Search
EOF;
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod');
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of entities to index', 200);
    $this->addOption('offset', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of entities to skip before indexing', 0);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', false);
    $this->addOption('index_file', null, sfCommandOption::PARAMETER_REQUIRED, 'search index file', sfConfig::get('app_search_lucene_index_file'));
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


    //get id of last-indexed entity
    $index = EntityTable::getLuceneIndex($options['index_file']);
    $index->setMergeFactor(200);
    $index->setMaxBufferedDocs(20);


    if ($count = $index->count())
    {
      if (!$lastDoc = $index->getDocument($count-1))
      {
        throw new Exception("Can't find last document in index");
      }       
      $maxEntityId = $lastDoc->key;
    }
    else
    {
      $maxEntityId = 0;
    }

    //find non-deleted entities with greater IDs
    $q = LsDoctrineQuery::create()
      ->from('Entity e')
      ->leftJoin('e.Alias a')
      ->where('e.id > ? AND e.is_deleted = ?', array($maxEntityId, false))
      ->andWhere('a.context IS NULL')
      ->offset($options['offset'])
      ->limit($options['limit'])
      ->orderBy('e.id ASC');

    //index entities      
    $optimize = 0;
    foreach ($q->fetchArray() as $entity)
    {
      if (EntityTable::updateLuceneIndex($entity, $index, $batchMode=true))
      {
        if ($options['debug_mode'])
        {
          printf("Indexed entity with ID %s\n", $entity['id']);
        }
      }
      else
      {
        if ($options['debug_mode'])
        {
          printf("Skipped entity with ID %s\n", $entity['id']);
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