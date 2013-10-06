<?php

class mergeDuplicatesTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'merge';
    $this->name             = 'duplicates';
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
    $this->addOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'Political candidate, Business Person, Lobbyist, etc', 'PoliticalCandidate');  
    
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    sfContext::createInstance($this->configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      
		
    $this->db = Doctrine_Manager::connection();
    
    //merging by crp id
    $sql = 'SELECT e1.id,e2.id FROM entity e1 LEFT JOIN political_candidate pc1 ON pc1.entity_id = e1.id LEFT JOIN person p1 ON p1.entity_id = e1.id LEFT JOIN person p2 ON p2.name_first = p1.name_first AND p2.name_last = p1.name_last LEFT JOIN entity e2 ON e2.id = p2.entity_id LEFT JOIN political_candidate pc2 ON pc2.entity_id = e2.id WHERE e1.is_deleted = 0 and e2.is_deleted = 0 and pc1.id is not null and pc2.id is not null and e2.id <> e1.id and e1.id < e2.id and ((e1.start_date is null or e2.start_date is null) or year(e1.start_date) = year(e2.start_date)) and pc2.crp_id is not null and pc1.crp_id is not null and pc1.crp_id = pc2.crp_id';
		

		$this->merge($sql, false);

		//merging by house id		
		$sql = 'SELECT e1.id,e2.id FROM entity e1 LEFT JOIN political_candidate pc1 ON pc1.entity_id = e1.id LEFT JOIN person p1 ON p1.entity_id = e1.id LEFT JOIN person p2 ON p2.name_first = p1.name_first AND p2.name_last = p1.name_last LEFT JOIN entity e2 ON e2.id = p2.entity_id LEFT JOIN political_candidate pc2 ON pc2.entity_id = e2.id WHERE e1.is_deleted = 0 and e2.is_deleted = 0 and pc1.id is not null and pc2.id is not null and e2.id <> e1.id and e1.id < e2.id and ((e1.start_date is null or e2.start_date is null) or year(e1.start_date) = year(e2.start_date)) and pc2.house_fec_id is not null and pc1.house_fec_id is not null and pc1.house_fec_id = pc2.house_fec_id';
		
		//merging by senate id
		$this->merge($sql, true);

    $sql = 'SELECT e1.id,e2.id FROM entity e1 LEFT JOIN political_candidate pc1 ON pc1.entity_id = e1.id LEFT JOIN person p1 ON p1.entity_id = e1.id LEFT JOIN person p2 ON p2.name_first = p1.name_first AND p2.name_last = p1.name_last LEFT JOIN entity e2 ON e2.id = p2.entity_id LEFT JOIN political_candidate pc2 ON pc2.entity_id = e2.id WHERE e1.is_deleted = 0 and e2.is_deleted = 0 and pc1.id is not null and pc2.id is not null and e2.id <> e1.id and e1.id < e2.id and ((e1.start_date is null or e2.start_date is null) or year(e1.start_date) = year(e2.start_date)) and pc2.senate_fec_id is not null and pc1.senate_fec_id is not null and pc1.senate_fec_id = pc2.senate_fec_id';		
    
    //merging by similar name
    $this->merge($sql, true);
    		
		$sql = 'SELECT e1.id,e2.id,e1.name,e2.name,e1.blurb,e2.blurb FROM entity e1 LEFT JOIN political_candidate pc1 ON pc1.entity_id = e1.id LEFT JOIN person p1 ON p1.entity_id = e1.id LEFT JOIN person p2 ON p2.name_first = p1.name_first AND p2.name_last = p1.name_last LEFT JOIN entity e2 ON e2.id = p2.entity_id LEFT JOIN political_candidate pc2 ON pc2.entity_id = e2.id WHERE e1.is_deleted = 0 and e2.is_deleted = 0 and pc1.id is not null and pc2.id is not null and e2.id <> e1.id and e1.id < e2.id and ((e1.start_date is null or e2.start_date is null) or year(e1.start_date) = year(e2.start_date))';
    
    $this->merge($sql, true);
    
	}
	
	protected function merge($sql, $ask_user=true)
	{
	  $stmt = $this->db->execute($sql);
    $rows = $stmt->fetchAll();  
    foreach($rows as $row)
    {
     	$e1 = Doctrine::getTable('Entity')->find($row[0]);
     	$e2 = Doctrine::getTable('Entity')->find($row[1]);
     	$response='y';
     	if ($ask_user)
     	{ 

     	  $prompt = "\n\n*********\n\nDo you want to merge these entities?\n";
     	  $prompt .= $this->getEntityString($e1) . "\n**********\n" . $this->getEntityString($e2);
     	  $response = $this->readline($prompt);
     	}
     	if ($response == 'y')
     	{
        $mergedEntity = EntityTable::mergeAll($e1, $e2);
        $e2->setMerge(true);
        $e2->clearRelated();
        $e2->delete();
        echo '  Successfully merged ' . $e2->name . "\n";
      }
    }
	}
	
	protected function getEntityString($e)
	{
	  $str = '';
	  $arr = array($e->name,$e->blurb,$e->summary,$e->start_date, PersonTable::getRelatedOrgSummary($e));
	  $str = implode("\n",$arr);
	  return $str;
	}
	
	protected function readline($prompt="", $possible = array('y','n'), $lim = 5)
  {
    $response = '';
    $ct = 0;
    while (!in_array($response,$possible) && $ct < $lim)
    {
      print $prompt;
      $out = "";
      $key = "";
      $key = fgetc(STDIN);        //read from standard input (keyboard)
      while ($key!="\n")        //if the newline character has not yet arrived read another
      {
        $out.= $key;
        $key = fread(STDIN, 1);
      }
      $response = $out;
      $ct++;
    }
    return $response;
  }


}

