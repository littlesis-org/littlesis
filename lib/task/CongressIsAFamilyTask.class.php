<?php

class CongressIsAFamilyTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'bioparse';
    $this->name             = 'congress-family';
    $this->briefDescription = 'adds family relationships from congress member blurbs';
    $this->detailedDescription = <<<EOF
This task adds family relationships from congress member blurbs.
EOF;

    $this->addOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($options['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      
    
    $q = EntityTable::getByExtensionQuery(array('Person','ElectedRepresentative'))->addWhere('summary like ? OR summary like ? OR summary like ? OR summary like ? OR summary like ? OR summary like ? OR summary like ? OR summary like ? OR summary like ?',array('(daughter%','(son%','(father%','(mother%','(cousin%','(husband%','(wife%','(brother%','(sister%'))->orderBy('person.name_last');
    $members = $q->execute();
    foreach($members as $member)
    {
      if (preg_match('/\([^\)]*\)/isu',$member->summary,$match))
      {
        echo $member->name . ":\n";
        if (preg_match_all('/(brother|sister|daughter|mother|father|wife|husband|cousin)\sof\s+([^\;\)\,]*)(\;|\)|\,)/isu',$match[0],$matches, PREG_SET_ORDER))
        {
          foreach($matches as $m)
          {
            echo "\t\t" . $m[1] . ' : of : ' . $m[2] . "\n";
            $m[2] = str_replace('.','',$m[2]);
            $parts = LsString::split($m[2]);
            $q = EntityTable::getByExtensionQuery(array('Person','ElectedRepresentative'));
            foreach($parts as $part)
            {
              $q->addWhere('e.name like ?', '%' . $part . '%');
            }
            $people = $q->execute();
            $family = array();
            foreach($people as $person)
            {
              echo "\t\t\t\t" . $person->name . "\n"; 
              if ($person->id != $member->id)
              {
                $family[] = $person;
              }
            }
            if (count($family) == 1)
            {
              $q = LsDoctrineQuery::create()->from('Relationship r')->where('(r.entity1_id = ? or r.entity2_id =?) and (r.entity1_id = ? or r.entity2_id = ?)',array($member->id,$member->id,$person->id,$person->id));
              if (!$q->count())
              {
                if ($description2 = FamilyTable::getDescription2($m[1],$family[0]->Gender->id))
                {
                  $relationship = new Relationship;
                  $relationship->setCategory('Family');
                  $relationship->Entity1 = $member;
                  $relationship->Entity2 = $person;
                  $relationship->description1 = $m[1];  
                  $relationship->description2 = $description2;
                  $relationship->save();
                  $ref = LsQuery::getByModelAndFieldsQuery('Reference',array('object_model' => 'Entity','object_id' => $member->id,'name' => 'Congressional Biographical Directory'))->fetchOne();
                  if ($ref)
                  {
                    $relationship->addReference($ref->source,null,null,$ref->name,$ref->source_detail,$ref->publication_date);
                  }
                  echo "-------------------------------added relationship\n";
                }
              }
            }  
          }
        }
        echo "\n";
      }
    }
    
  }
}