<?php

class cleanupGeneralTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'cleanup';
    $this->name             = 'general';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [cleanup:references|INFO] task does things.
Call it with:

  [php symfony cleanup:references|INFO]
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
    
     $entities = LsDoctrineQuery::create()
                ->from('Entity e')
                ->where('CHAR_LENGTH(e.name) = 100')
                ->groupBy('e.name')
                ->execute();
    foreach($entities as $e)
    {
      echo $e->id . ' :: ' . $e->name;
      echo "\n";
      $duplicates = LsDoctrineQuery::create()
                                  ->from('Entity e')
                                  ->where('e.id != ? and e.name = ?', array($e->id, $e->name))
                                  ->execute();
      foreach($duplicates as $e2)
      {
        $mergedEntity = EntityTable::mergeAll($e, $e2);
        $e2->setMerge(true);
        $e2->clearRelated();
			  $e2->delete();
			  echo '  Successfully merged ' . $e2->name . "\n";
      }
    }
    
    
    /*$id = 0;
    $industry = LsDoctrineQuery::create()
                              ->from('Industry i')
                              ->where('id > ?',$id)
                              ->fetchOne();
    while ($industry)
    {
      $matches = LsDoctrineQuery::create()
                                  ->from('Industry i')
                                  ->where('i.name = ? and i.id != ?', array($industry->name,$industry->id))
                                  ->execute();
      echo $matches->count() . '...';
      foreach($matches as $match)
      {
        $bis = LsDoctrineQuery::create()
                                  ->from('BusinessIndustry bi')
                                  ->where('bi.industry_id = ?', $match->id)
                                  ->execute();
        foreach($bis as $b)
        {
          $b->industry_id = $industry->id;
          $b->save();
          $match->delete();
        }
        
      }
      $id = $industry->id;
      $industry = LsDoctrineQuery::create()
                              ->from('Industry i')
                              ->where('id > ?',$id)
                              ->fetchOne();
    }
    
    $images = LsQuery::getByModelAndFieldsQuery('Image',array('caption' => 'Array'));
    foreach($images as $image)
    {
      $image->caption = 'From the Biographical Directory of the United States Congress';
      $image->save();
    }*/
    
   
  }
}