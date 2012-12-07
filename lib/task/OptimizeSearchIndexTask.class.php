<?php

class OptimizeSearchIndexTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'search';
    $this->name             = 'optimize-index';
    $this->briefDescription = 'Optimizes index created by Zend Lucene Search';
    $this->detailedDescription = <<<EOF
This task optimizes the entity search index files created by Zend Lucene Search
EOF;
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', false);
  }

  public function safeToRun()
  {
    $proc_id = getmypid();
    exec('ps aux -ww | grep symfony | grep search:optimize-index | grep -v ' . $proc_id . ' | grep -v grep', $status_arr);
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

    //get index and optimize
    $index = EntityTable::getLuceneIndex();
    $index->optimize();
    
    printf("Memory used: %s\n", LsNumber::makeBytesReadable(memory_get_usage()));
    printf("Index size: %s\n", $index->count());

    $timer->addTime();
    printf("Run time: %s\n", $timer->getElapsedTime());
    sfTimerManager::clearTimers();
  }
}