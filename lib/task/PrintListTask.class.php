<?php

class PrintListTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'print';
    $this->name             = 'list';
    $this->briefDescription = 'printing a list of entities';
    $this->detailedDescription = <<<EOF
Various tasks to help with occupation research.
EOF;
  
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('list_id',null, sfCommandOption::PARAMETER_REQUIRED, 'the list id', null);
    $this->addOption('entity_ids',null, sfCommandOption::PARAMETER_REQUIRED, 'entity ids', null);
    
  }

  protected function execute($arguments = array(), $options = array())
  {
  	if (!$options['list_id'] && !$options['entity_ids'])
		{
			echo "you need a list id or entity ids\r";
			die;
		}
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);      
    
    $db = Doctrine_Manager::connection();


    //entity ids
    if ($options['entity_ids'])
    {
      $sql = 'SELECT p.name_first,p.name_last,e.blurb,GROUP_CONCAT(distinct(e2.name) separator "; ") as orgs, e.name, e.id
      FROM entity e
      LEFT JOIN person p on p.entity_id = e.id
      LEFT JOIN relationship r on r.entity1_id = e.id and r.category_id in (1,3) and (r.is_current = 1 or (r.is_current is null and r.end_date is null)) and r.is_deleted = 0
      LEFT JOIN entity e2 on e2.id = r.entity2_id 
      WHERE e.id in (' . $options['entity_ids'] . ')
      GROUP BY e.id';
      $stmt = $db->execute($sql);
    }
    //list id
    else if ($options['list_id'])
    {
      $sql = 'SELECT p.name_first,p.name_last,e.blurb,GROUP_CONCAT(distinct(e2.name) separator "; ") as orgs, e.name, e.id
      FROM ls_list_entity le 
      LEFT JOIN entity e ON e.id = le.entity_id 
      LEFT JOIN person p on p.entity_id = e.id
      LEFT JOIN relationship r on r.entity1_id = e.id and r.category_id in (1,3) and (r.is_current = 1 or (r.is_current is null and r.end_date is null)) and r.is_deleted = 0
      LEFT JOIN entity e2 on e2.id = r.entity2_id 
      WHERE le.list_id = ? and le.is_deleted = 0
      GROUP BY e.id';
      $stmt = $db->execute($sql,array($options['list_id']));
    }

    $rows = $stmt->fetchAll(PDO::FETCH_CLASS);  
    foreach($rows as $row)
    {
    	$row = (array) $row;
    	$str1 = implode("\t",array_slice($row,0,3));
    	$str2 = implode("\t",array_slice($row,3));
    	echo $str1 . "\t";
    	$e = Doctrine::getTable('Entity')->find($row['id']);
    	echo EntityTable::getUri($e) . "\t";
    	echo $str2 . "\n";
    }
  }
  
  
}