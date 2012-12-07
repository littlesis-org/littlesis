<?php

class ParseGovtrackPeopleFileTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'govtrack';
    $this->name             = 'parse-people-file';
    $this->briefDescription = 'parses govtrack people.xml file';
    $this->detailedDescription = <<<EOF
This task takes a people.xml file from GovTrack and puts it into database tables.
EOF;

    $this->addOption('path', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'data/govtrack/people.xml');		
    $this->addOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($options['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      
        
    $rawDb = $databaseManager->getDatabase('raw');
    $db = Doctrine_Manager::connection($rawDb->getParameter('dsn'));
    

    //load file
		$path = sfConfig::get('sf_root_dir') . DIRECTORY_SEPARATOR . $options['path'];
		$xml = simplexml_load_file($path);

    
    //array of persons
    $persons = $xml->xpath('/people/person');

    //array of existing person ids
    $ids = array();
    $q = Doctrine_Query::create()
      ->select('p.govtrack_id')
      ->from('GovtrackPerson p')
      ->setHydrationMode(Doctrine::HYDRATE_NONE);

    $arrays = $q->execute();
    $q->free(true);
      
    foreach ($arrays as $array)
    {
      $ids[] = (int) $array[0];
    }

    
    while ($person = current($persons))
    {      
      $ary = current($person);
      
      if (in_array($ary['id'], $ids))
      {
        next($persons);
        continue;
      }


      echo "Importing GovTrack ID: " . $ary['id'] . "\n";

      try
      {
        $db->beginTransaction();


   
        $fieldMap = array(
          'id' => 'govtrack_id',
          'osid' => 'os_id',
          'bioguideid' => 'bioguide_id',
          'lastname' => 'name_last',
          'firstname' => 'name_first',
          'middlename' => 'name_middle',
          'birthday' => 'start_date',
          'gender' => 'gender',
          'url' => 'url',
          'district' => 'district'
        );
   
        $p = new GovtrackPerson;
  
   
        foreach ($ary as $field => $value)
        { 
          if (isset($fieldMap[$field]))
          {
            $p->set($fieldMap[$field], $value);
          }
        }
        
        $p->name_middle = str_replace('.', '', $p->name_middle);
        
        
        if (isset($ary['party']))
        {
          if (!$party = Doctrine::getTable('GovtrackParty')->findOneByName($ary['party']))
          {
            $party = new GovtrackParty;
            $party->name = $ary['party'];
            $party->save();
          }
          
          $p->Party = $party;
          $party->free();
        }
        

        if (isset($ary['state']))
        {
          if (!$state = Doctrine::getTable('GovtrackState')->findOneByAbbreviation($ary['state']))
          {
            throw new Exception("Couldn't find state with abbreviaton: " . $ary['state']);
          }
          
          $p->State = $state;
          $state->free();
        }

        $p->save();
        


        $this->loadRoles($person, $p);


        $this->loadCommittees($person, $p);


        
        $db->commit();        
      }
      catch (Exception $e)
      {
        $db->rollback();
        throw $e;
      }
      
      $p->free(true);

      
      echo number_format(memory_get_usage()) . "\n";
      
      flush();
      
      next($persons);
    }
  }


  public function loadRoles(SimpleXmlElement $person, GovtrackPerson $p)
  {
    $roles = $person->xpath('role');

    while ($role = current($roles))
    {
      $roleAry = current($role);
      
      $roleFieldMap = array(
        'startdate' => 'start_date',
        'enddate' => 'end_date',
        'district' => 'district',
        'url' => 'url'
      );
      
      $role = new GovtrackRole;
      $role->Person = $p;
      
      foreach ($roleAry as $field => $value)
      {
        if (isset($roleFieldMap[$field]))
        {
          $role->set($roleFieldMap[$field], $value);
        }
      }
    

      if (isset($roleAry['type']))
      {
        if (!$type = Doctrine::getTable('GovtrackRoleType')->findOneByName($roleAry['type']))
        {
          $type = new GovtrackRoleType;
          $type->name = $roleAry['type'];
          $type->save();
        }
        
        $role->Type = $type;
        $type->free();
      }

      if (isset($roleAry['party']))
      {
        if (!$party = Doctrine::getTable('GovtrackParty')->findOneByName($roleAry['party']))
        {
          $party = new GovtrackParty;
          $party->name = $roleAry['party'];
          $party->save();
        }
        
        $role->Party = $party;
        $party->free(true);
      }
      

      if (isset($roleAry['state']))
      {
        if (!$state = Doctrine::getTable('GovtrackState')->findOneByAbbreviation($roleAry['state']))
        {
          $state = new GovtrackState;
          $state->abbreviation = $roleAry['state'];
          $state->save();
        }
        
        $role->State = $state;
        $state->free(true);
      }

      $role->save();
      $role->free();
      
      next($roles);
    }
  }


  public function loadCommittees(SimpleXmlElement $person, GovtrackPerson $p)
  {
    $committees = $person->xpath('current-committee-assignment');

    while ($committee = current($committees))
    {
      $committeeAry = current($committee);
      
      $pc = new GovtrackPersonCommittee;
      $pc->Person = $p;

      if (!$c = Doctrine::getTable('GovtrackCommittee')->findOneByName($committeeAry['committee']))
      {
        $c = new GovtrackCommittee;
        $c->name = $committeeAry['committee'];
        $c->save();
      }

      $pc->Committee = $c;
      
      
      if (isset($committeeAry['role']))
      {
        $pc->role = $committeeAry['role'];
      }


      if (isset($committeeAry['subcommittee']))
      {
        if (!$s = Doctrine::getTable('GovtrackSubcommittee')->findOneByName($committeeAry['subcommittee']))
        {
          $s = new GovtrackSubcommittee;
          $s->name = $committeeAry['subcommittee'];
          $s->Committee = $c;
          $s->save();
        }

        
        $pc->Subcommittee = $s;
        $s->free(true);
      }

      $c->free(true);
      unset($c);
      
      $pc->save();
      $pc->free();
      
      next($committees);
    }
  }
}