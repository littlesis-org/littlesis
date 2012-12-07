<?php

class ListCsvTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'list';
    $this->name             = 'csv';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
  The [list-csv|INFO] task takes a list and generates a csv file
Call it with:

  [php symfony list-csv|INFO]
EOF;
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Test mode', false);
    $this->addOption('list_id',null,sfCommandOption::PARAMETER_REQUIRED,'list id',null);
    $this->addOption('file_name',null,sfCommandOption::PARAMETER_REQUIRED,'file name',null);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      
    $fh = fopen($options['file_name'],'a+');
    $start = count(file($options['file_name']));
    
    $this->db = Doctrine_Manager::connection();
    $this->list = Doctrine::getTable('LsList')->find($options['list_id']);
    $q = $this->list->getListEntitiesByRankQuery();
    $list_entities = $q->execute();
    
    function func ($value) 
    {
      return $value['name'];
    }
    
    for ($i = $start; $i < count($list_entities); $i++)
    {
      $entity = $list_entities[$i]->Entity;
      
      $people = $entity->getRelatedEntitiesQuery(array('Person'),array(1,2,3,4,6,7,8,9,10))->execute();
      $orgs = $entity->getRelatedEntitiesQuery(array('Org'),array(1,2,3,4,6,7,8,9,10))->execute();
      $donations = $entity->getRelatedEntitiesQuery(array('Person'),array(5))->execute();
      $people = implode("; ", array_map("func", $people->toArray()));
      $orgs = implode("; ",array_map("func", $orgs->toArray()));
      $donations = implode("; ",array_map("func", $donations->toArray()));
      $arr = array($entity,$people,$orgs,$donations);
      $str = implode("\t",$arr);
      fwrite($fh,$str .  "\n");
    }
    
  }
  

  
}