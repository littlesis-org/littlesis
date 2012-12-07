<?php

class ParseGovtrackCongressCommitteesTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'govtrack';
    $this->name             = 'parse-congress-committees';
    $this->_senateEntityId = 12885;
    $this->_houseEntityId = 12884;
    $this->_committees = null;
    $this->briefDescription = '';
    $this->appConfiguration = null;
    $this->db = null;
    $this->detailedDescription = <<<EOF
The congress-committees task loads congressional committee membership data from govtrack.
Call it with:

  [php symfony uploadSpreadsheet|INFO]
EOF;
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Test mode', false);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);  
    $this->addOption('path', null, sfCommandOption::PARAMETER_REQUIRED, 'path for committees.xml', 'data/govtrack/committees.xml');	
    $this->addOption('start_id', null, sfCommandOption::PARAMETER_REQUIRED, 'start id', 0);
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'limit', 10);
  }
  
  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      
		require sfConfig::get('sf_lib_dir') . '/vendor/votesmart/VoteSmart.php';
    
    
    //load file
		$path = sfConfig::get('sf_root_dir') . DIRECTORY_SEPARATOR . $options['path'];
		$xml = simplexml_load_file($path);
    
    //array of committees
    $committees = $xml->xpath('/committees/committee');
    
    //sort out start and limit for session
    if ($options['start_id'] == 0 && $options['session_name'])
    {
      
    }
    else $start_id = $options['start_id'];
    if ((count($committees) - $options['start_id']) > $options['limit'])
    {
      $lim = $options['start_id'] + $options['limit'];
    }
    else
    {
      $lim = count($committees);
    }
    
    for($i = $options['start_id']; $i < $lim; $i++)
    {
      echo "\n\n *********** Committee numero $i *************** \n\n"; 
      $gt_committee = $committees[$i];
      $ls_cmte = $this->getCommittee($gt_committee);
      $ref_link = 'http://www.govtrack.us/congress/committee.xpd?id=' . $gt_committee->attributes()->code;
      $this->loadCommitteeMembers($gt_committee,$ls_cmte,$ref_link);
      foreach($gt_committee->subcommittee as $sc)
      {
        $ls_subcmte = $this->getSubcommittee($sc, $ls_cmte, $ref_link);
        $ref_link = 'http://www.govtrack.us/congress/committee.xpd?id=' . $gt_committee->attributes()->code . $sc->attributes()->code;
        $this->loadCommitteeMembers($sc,$ls_subcmte,$ref_link);
      }
    }
	}
	
	protected function loadCommitteeMembers($gt_cmte,$ls_cmte, $ref_link)
	{
	  $current_members = array();
	  $current_roles = array();
	  foreach($gt_cmte->xpath('member') as $member)
	  {
      $id = (string) $member->attributes()->id;
	    $role = isset($member->attributes()->role) ? (string) $member->attributes()->role : null;
	    $current_members[] = $id;
	    $current_roles[$role] = $id;
	  }
	  
	  //set old memberships to not current
    $q = LsDoctrineQuery::create()
          ->from('Relationship r')
          ->leftJoin('r.Entity1 e')
          ->leftJoin('e.ElectedRepresentative er')
          ->whereNotIn('er.govtrack_id', $current_members)
          ->andWhere('r.entity2_id = ? and r.category_id = ?', array($ls_cmte->id,RelationshipTable::MEMBERSHIP_CATEGORY))
          ->andWhere('r.is_current = 1 or r.is_current is NULL');
    $old = $q->execute();

    foreach($old as $o)
    {
      $o->is_current = 0;
      $o->save();
    }
    echo "\t\t" . count($old) . " existing membership relationships set to past\n";
    
    // find relationships to add
    $q = LsDoctrineQuery::create()
          ->from('Relationship r')
          ->leftJoin('r.Entity1 e')
          ->leftJoin('e.ElectedRepresentative er')
          ->whereIn('er.govtrack_id', $current_members)
          ->andWhere('r.entity2_id = ?', $ls_cmte->id)
          ->andWhere('r.is_current = 1 or r.is_current is NULL');
    $existing_members = array();
    $existing = $q->execute();
    foreach($existing as $e)
    {
      $existing_members[] = $e->Entity1->ElectedRepresentative->govtrack_id;
    }
    $members_to_add = array_diff($current_members,$existing_members);
    
    // create new membership relationships 
    if (count($members_to_add))
    {
      $q = LsDoctrineQuery::create()
            ->from('Entity e')
            ->leftJoin('e.ElectedRepresentative er')
            ->whereIn('er.govtrack_id', $members_to_add);
      $members_to_add = $q->execute();
      foreach($members_to_add as $new_member)
      {
        $rel = new Relationship;
        $rel->Entity1 = $new_member;
        $rel->Entity2 = $ls_cmte;
        $rel->is_current = 1;
        $rel->setCategory('Membership');
        $rel->save();
        $rel->addReference($ref_link, null, $modified, 'GovTrack.us - ' . $gt_cmte->attributes()->displayname);
      }
      echo "\t\t" . count($members_to_add) . " new memberships created \n";
    }      

    $possible_roles = array('Chair','Chairman','Vice Chair','Vice Chairman','Ranking Member','Ex Officio');
    $possible_roles_kv = array('Chair' => 'Chairman','Chairman' => 'Chair','Vice Chair' => 'Vice Chairman', 'Vice Chairman' => 'Vice Chair','Ranking Member' => 'Ranking Member','Ex Officio' => 'Ex Officio');
    $q = LsDoctrineQuery::create()
          ->from('Relationship r')
          ->leftJoin('r.Entity1 e')
          ->leftJoin('e.ElectedRepresentative er')
          ->whereIn('r.description1', $possible_roles)
          ->andWhere('r.entity2_id = ? and r.category_id = ?', array($ls_cmte->id,RelationshipTable::POSITION_CATEGORY))
          ->andWhere('r.is_current = 1 or r.is_current is NULL');
    $existing_roles = $q->execute();
    foreach($existing_roles as $er)
    {
      $er->is_current = 0;
      $er->save();
    }
    foreach($current_roles as $ck => $cv)
    {
      if (in_array($ck,$possible_roles))
      {
        $q = LsDoctrineQuery::create()
              ->from('Relationship r')
              ->leftJoin('r.Entity1 e')
              ->leftJoin('e.ElectedRepresentative er')
              ->whereIn('r.description1', array($ck,$possible_roles_kv[$ck]))
              ->andWhere('r.entity2_id = ? and r.category_id = ? and er.govtrack_id = ?', array($ls_cmte->id,RelationshipTable::POSITION_CATEGORY, $cv));
        if ($rel = $q->fetchOne())
        {
          $rel->is_current = 1;
          $rel->save();
          echo "\t\tShifted one role back to current\n";
        }
        else
        { 
          $elected = Doctrine::getTable('ElectedRepresentative')->findOneByGovtrackId($cv);
          $rel = new Relationship;
          $rel->entity1_id = $elected->entity_id;
          $rel->Entity2 = $ls_cmte;
          $rel->description1 = $ck;
          $rel->setCategory('Position');
          $rel->save();
          $rel->addReference($ref_link, null, $modified, 'GovTrack.us - ' . $gt_cmte->attributes()->displayname);
          echo "\t\tAdded a new role\n";
        }
      }
    }
    
	}
	
	protected function getCommittee($gt_committee)
	{
	  //standardize name
	  $original_name = $name = $gt_committee->attributes()->displayname;
	  if (strpos($name,"Committee on ") > 0 && strpos($name,"Joint ") !== 0)
	  {
      $name = str_replace("Committee on the ","",$name);
	    $name = str_replace("Committee on ","",$name);
	  }
    $name = strstr($name, 'Committee') ? $name : $name . ' Committee';
    $name = html_entity_decode($name);
    
    $q = LsDoctrineQuery::create()
          ->from('Entity e')
          ->leftJoin('e.GovernmentBody g')
          ->leftJoin('e.Alias a')
          ->where('a.name = ? and g.id is not null', array($name));
    $cmte = $q->fetchOne();
    echo "Processing $name\n";
    
    if (!$cmte)
    {
      $cmte = new Entity;
      $cmte->addExtension('Org');
      $cmte->addExtension('GovernmentBody');
      $cmte->name = $name;
      if (stripos($cmte->name, 'House') === 0)
      {
        $cmte->parent_id = $this->_houseEntityId;
      }
      else if (stripos($cmte->name, 'Senate') === 0)
      {
        $cmte->parent_id = $this->_senateEntityId;
      }
      $cmte->save();
      $modified = $cmte->getAllModifiedFields();
      $cmte->addReference('http://www.govtrack.us/congress/committee.xpd?id=' . $gt_committee->attributes()->code, null, $modified, 'GovTrack.us - ' . $original_name);
      
      echo "\tCreated new committee: " . $name . "\n";
    
    }
    $original_name = trim($original_name);
    if($cmte->name != $original_name)
    {
      $alias = new Alias;
      $alias->entity_id = $cmte->id;
      $alias->name = $original_name;
      $cmte->save();
    }
    return $cmte;
	}
	
	protected function getSubcommittee($subcommittee, $parent_ls_committee, $ref_link)
	{
	  //standardize name
	  $original_name = $name = $subcommittee->attributes()->displayname;
	  $name = trim($name);
    $name = html_entity_decode($name);
    if (strpos($name,"Subcommittee") === false)
    {
      $name = $name . " Subcommittee";
    }
    echo "\tProcessing " . $name . "\n";
    
    $q = LsDoctrineQuery::create()
          ->from('Entity e')
          ->leftJoin('e.GovernmentBody g')
          ->leftJoin('e.Alias a')
          ->where('a.name = ? and g.id is not null and e.parent_id = ?', array($name,$parent_ls_committee->id));
    $cmte = $q->fetchOne();
    if (!$cmte)
    {
      $cmte = new Entity;
      $cmte->addExtension('Org');
      $cmte->addExtension('GovernmentBody');
      $cmte->name = $name;
      $cmte->parent_id = $parent_ls_committee->id;
      $cmte->save();
      $modified = $cmte->getAllModifiedFields();
      $cmte->addReference($ref_link, null, $modified, 'GovTrack.us - ' . $original_name);
      
      echo "\t\tCreated new subcommittee: " . $name . "\n";
    
    }
    $original_name = trim($original_name);
    if($cmte->name != $original_name)
    {
      $alias = new Alias;
      $alias->entity_id = $cmte->id;
      $alias->name = $original_name;
      $cmte->save();
    }
    return $cmte;
	}

}

