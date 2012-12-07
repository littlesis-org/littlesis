<?php

class OccupyBoardroomTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'occupy';
    $this->name             = 'boardroom';
    $this->briefDescription = 'tasks to help with occupation';
    $this->detailedDescription = <<<EOF
Various tasks to help with occupation research.
EOF;
  
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('list_id',null, sfCommandOption::PARAMETER_REQUIRED, 'the list id', null);
    $this->addOption('entity_ids',null, sfCommandOption::PARAMETER_REQUIRED, 'related entity ids', null);
    
  }

  protected function execute($arguments = array(), $options = array())
  {
  	if (!$options['list_id'])
		{
			echo "you need a list id\r";
			die;
		}
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);      
    
    $db = Doctrine_Manager::connection();


    //
    $sql = 'SELECT e.name,e.blurb,GROUP_CONCAT(distinct(e2.name) separator "; ") as banks,GROUP_CONCAT(distinct(em.address) separator "; ") as emails,GROUP_CONCAT(distinct(ph.number) SEPARATOR "; ") as phones, GROUP_CONCAT(distinct(ph2.number) SEPARATOR "; ") as phones2, GROUP_CONCAT(distinct(ph3.number) SEPARATOR "; ") as phones3, GROUP_CONCAT(distinct(a.id) SEPARATOR "; ") as verified_addresses, GROUP_CONCAT(distinct(a2.id) SEPARATOR "; ") as addresses, GROUP_CONCAT(distinct(po.is_board) SEPARATOR "; ") as board,p.name_last as last_name, e.id
    FROM ls_list_entity le 
    LEFT JOIN entity e ON e.id = le.entity_id 
    LEFT JOIN person p on p.entity_id = e.id
    LEFT JOIN relationship r on r.entity1_id = e.id and r.category_id = 1 and (r.is_current = 1 or r.is_current is null) and r.is_deleted = 0
    LEFT JOIN position po on po.relationship_id = r.id
    LEFT JOIN entity e2 on e2.id = r.entity2_id 
    LEFT JOIN email em on em.entity_id = e.id and em.is_deleted = 0
    LEFT JOIN phone ph on e.id = ph.entity_id and ph.is_deleted = 0 and ph.type = ?
    LEFT JOIN phone ph2 on e.id = ph2.entity_id and ph2.is_deleted = 0 and ph2.type = ?
    LEFT JOIN phone ph3 on e.id = ph3.entity_id and ph3.is_deleted = 0 and ph3.type = ?
    LEFT JOIN address a on a.entity_id = e.id and a.is_deleted = 0 and a.category_id in (4,5)
    LEFT JOIN address a2 on a2.entity_id = e.id and a2.is_deleted = 0 and a2.category_id in (1,2,3)
    WHERE le.list_id = ? and le.is_deleted = 0 and e2.id in (?)
    GROUP BY e.id';
    $stmt = $db->execute($sql,array('work','home','phone',$options['list_id'],$options['entity_ids']));
    $rows = $stmt->fetchAll(PDO::FETCH_CLASS);  
    foreach($rows as $row)
    {
    	$row = (array) $row;
    	$str1 = implode("\t",array_slice($row,0,3));
    	$str2 = implode("\t",array_slice($row,3));
    	echo $str1 . "\t";
    	$e = Doctrine::getTable('Entity')->find($row['id']);
    	echo EntityTable::getUri($e) . '/editContact' . "\t\t";
    	echo $str2 . "\n";
    }
  }
  
  protected function printFirst ($options)
  {
		if (!$options['list_id'])
		{
			echo "you need a list id\r";
			die;
		}
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);      
    
    $db = Doctrine_Manager::connection();


    //CREATE NEW PRIMARY ALIASES
    $q = Doctrine_Query::create()
      ->from('Entity e')
      ->leftJoin('e.LsListEntity le')
      ->leftJoin('e.Person p')
      ->where('le.list_id = ? and le.is_deleted = 0', $options['list_id'])
      ->orderBy('le.created_at ASC');
    
      
    $entities = $q->execute();

    foreach ($entities as $e)
    {
    	echo $e->name . "\t\t" . EntityTable::getUri($e) . '/editContact' . "\t" . EntityTable::getUri($e) . "\t\t\t\t\t\t\t" . $e->Person->name_last . "\t" . $e->id . "\n";
    }

  }
  
}