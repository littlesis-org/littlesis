<?php

class mergePoliticalDuplicatesTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'merge';
    $this->name             = 'political-duplicates';
    $this->entity = null;
    $this->list = null;
    $this->url = null;
    $this->url_name = null;
    $this->briefDescription = '';
    $this->appConfiguration = null;
    $this->db = null;
    $this->detailedDescription = <<<EOF
The [mergePoliticalDuplicates|INFO] task does things.
Call it with:

  [php symfony mergePoliticalDuplicates|INFO]
EOF;
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Test mode', true);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);  
    $this->addOption('house_senate', null, sfCommandOption::PARAMETER_REQUIRED, 'house or senate', 'house');  
    
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      
		
    $this->db = Doctrine_Manager::connection();
    
    if ($options['house_senate'] == 'house')
    {
			$sql = 'select e1.id,e2.id from entity e1 left join political_candidate pc on pc.entity_id = e1.id left join political_candidate pc2 on pc2.house_fec_id = pc.house_fec_id left join entity e2 on e2.id = pc2.entity_id where e1.is_deleted = 0 and e2.is_deleted = 0 and e1.id <> e2.id and pc.id is not null and pc2.id is not null and pc.id <> pc2.id and pc.house_fec_id is not null and pc.house_fec_id <> "" and e1.id < e2.id group by e1.id,e2.id';
		}
		else if ($options['house_senate'] == 'senate')
		{
			$sql = 'select e1.id,e2.id from entity e1 left join political_candidate pc on pc.entity_id = e1.id left join political_candidate pc2 on pc2.senate_fec_id = pc.senate_fec_id left join entity e2 on e2.id = pc2.entity_id where e1.is_deleted = 0 and e2.is_deleted = 0 and e1.id <> e2.id and pc.id is not null and pc2.id is not null and pc.id <> pc2.id and pc.senate_fec_id is not null and pc.senate_fec_id <> "" and e1.id < e2.id group by e1.id,e2.id';		
		}
		else
		{
			echo 'House or Senate not selected...ending script' . "\n";
			die;
		}
		
    $stmt = $this->db->execute($sql);
    $rows = $stmt->fetchAll();  
    foreach($rows as $row)
    {
     	$e1 = Doctrine::getTable('Entity')->find($row[0]);
     	$e2 = Doctrine::getTable('Entity')->find($row[1]);
     	$mergedEntity = EntityTable::mergeAll($e1, $e2);
      $e2->setMerge(true);
      $e2->clearRelated();
			$e2->delete();
			echo '  Successfully merged ' . $e2->name . "\n";
			if ($options['test_mode'])
			{
				die;
			}
    }
	}


}

