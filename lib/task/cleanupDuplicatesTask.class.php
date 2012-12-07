<?php

class cleanupDuplicatesTask extends sfBaseTask
{

  protected $fileName;
  protected $extensionTypes;
  
  protected function configure()
  {
    $this->namespace        = 'cleanup';
    $this->name             = 'duplicates';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [cleanup:duplicates|INFO] task does things.
Call it with:

  [php symfony cleanup:duplicates|INFO]
EOF;

    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Test mode', false);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
    $this->addOption('file_name', null, sfCommandOption::PARAMETER_REQUIRED, 'file name for duplicate list', null);
    $this->addOption('extensions1', null, sfCommandOption::PARAMETER_REQUIRED, 'extensions for initial query', 'Person');
    $this->addOption('extensions2', null, sfCommandOption::PARAMETER_REQUIRED, 'extensions for second query', 'Person');
    $this->addOption('exact_first', null, sfCommandOption::PARAMETER_REQUIRED, 'exact first name match?', false);
    $this->addOption('start_id',null, sfCommandOption::PARAMETER_REQUIRED, 'start id', 0);
    $this->addOption('min_duplicates',null, sfCommandOption::PARAMETER_REQUIRED, 'minimum duplicates', 1);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);
    $extensions1 = explode(',',$options['extensions1']);
    $extensions2 = explode(',',$options['extensions2']);
    $exact_first = $options['exact_first'];
    $start_id = $options['start_id'];
    $min_duplicates = $options['min_duplicates'] - 1;
    
    if ((!in_array('Person',$extensions1) && !in_array('Person',$extensions2)) && (!in_array('Org',$extensions1) && !in_array('Org',$extensions2)))
    {
      $this->printDebug('extension issues, exiting');
      die;
    }
    $this->fileName = $options['file_name'];
    $entity = EntityTable::getByExtensionQuery($extensions1)->addWhere('e.id > ?', $start_id)->limit(1)->execute()->getFirst();
    $ct = 0;
    $fh = fopen($this->fileName,'a');
    $been_done = array();
    while ($entity)
    {
      $start_id = $entity->id;
      if (in_array('Person',$extensions1))
      {
        $q = EntityTable::getByExtensionQuery($extensions2)->addWhere('person.name_last = ? and e.id <> ?',array($entity->name_last, $entity->id));
                
        if (!$exact_first)
        {
          $q->addWhere('person.name_first like ?',substr($entity->name_first,0,1) . '%');      
        }                       
        else
        {
          $q->addWhere('person.name_first = ?', $entity->name_first);
        }        
        if ($entity->name_middle)
        {
          $q->addWhere('person.name_middle like ? OR person.name_middle IS NULL', substr($entity->name_middle,0,1) . '%');
        }
      }
      else
      {
        if (strlen($entity->name) > 8)
        {
          $q->addWhere('e.name like ?', '%' . $entity->name . '%');
        }
        else
        {
          $q->addWhere('e.name = ?', $entity->name);
        }  
      }
      if ($q->count() > $min_duplicates)
      {
        $matches = $q->execute();
        $arr = array();
        echo $entity->name . "\n";
        foreach($matches as $match)
        {
          $been_done[] = $match->id;
          $arr[] = $match->name; 
        }        
        $links = $entity->name . "\t";
        $links .= implode("; ",$arr) . "\t";
        $links .=  'http://littlesis.org/entity/view?id=' . $entity->id . "\n"; 
        fwrite($fh, $links);
      }
      $entity = EntityTable::getByExtensionQuery($extensions1)->addWhere('e.id > ?', $start_id)->limit(1)->execute()->getFirst();
    } 
    fclose($fh);
  }

}