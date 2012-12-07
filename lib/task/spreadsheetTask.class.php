<?php

class spreadsheetTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'upload';
    $this->name             = 'spreadsheet';
    $this->entity = null;
    $this->list = null;
    $this->url = null;
    $this->url_name = null;
    $this->briefDescription = '';
    $this->appConfiguration = null;
    $this->db = null;
    $this->detailedDescription = <<<EOF
The [uploadSpreadsheet|INFO] task does things.
Call it with:

  [php symfony uploadSpreadsheet|INFO]
EOF;
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Test mode', false);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
    $this->addOption('url', null, sfCommandOption::PARAMETER_REQUIRED, 'url');
    $this->addOption('url_name', null, sfCommandOption::PARAMETER_REQUIRED, 'url name');
    $this->addOption('entity_id', null, sfCommandOption::PARAMETER_REQUIRED, 'entity id');
    $this->addOption('list_id', null, sfCommandOption::PARAMETER_REQUIRED, 'list id');
    $this->addOption('filename', null, sfCommandOption::PARAMETER_REQUIRED, 'filename');    
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      

    $this->db = Doctrine_Manager::connection();
    
    if ($options['entity_id'])
    {
      $this->entity = Doctrine::getTable('Entity')->find($options['entity_id']);
    }
    else
    {
      $this->list = Doctrine::getTable('LsList')->find($options['list_id']);
    }
    $this->url = $options['url'];
    $this->url_name = $options['url_name'];
    $accept = strtolower($this->readline(" $this->entity ok? (y or n)"));
    if ($accept == 'n')
    {
      die;
    }
    
    $filename = $options['filename'];
    
    $str = trim(file_get_contents($filename));
    $str = str_replace('"','',$str);
    $arr = explode("\n",$str);
    $headers = explode("\t",array_shift($arr));
    
    //cleanup array  
    $rows = array();
    foreach($arr as $a)
    {
      if (trim($a) == '') continue;
      $row = explode("\t",$a);
      $row = array_change_key_case(array_combine($headers,$row));
      $rows[] = $row;
    }

    foreach($rows as $row)
    {
      try
      {
        $this->db->beginTransaction();
        $this->processRow($row);
        $this->db->commit();
      }
      catch (Exception $e)
      {
        $this->db->rollback();
      }
    }
  }
  
  public function processRow($row)
  {
    if (isset($row['url']) && $row['url'] != '' && isset($row['url_name']) && $row['url_name'] != '')
    {
      $url = $row['url'];
      $url_name = $row['url_name'];
    }
    else
    {
      $url = $this->url;
      $url_name = $this->url_name;
    }

    foreach($row as &$r)
    {
      trim($r);
    }
    unset($r);
    
    if ($this->entity)
    {
      $required = array('entity_name','primary_type','relationship_category');
    }
    else
    {
      $required = array('entity_name','primary_type');
    }  
    foreach($required as $req)
    {
      if (!isset($row[$req]) || $row[$req] == '') 
      {
        $this->printDebug('!!! > skipping row, ' . $req . ' not set');
        return;
      }
    }

    if ($row['primary_type'] != 'Person' && $row['primary_type'] != 'Org')
    {
      $this->printDebug('!!! > primary type not properly set, skipping row...');
      return;
    }
    if ($this->entity)
    {
      $relationship_category = trim($row['relationship_category']);
      $relationship_category_id = array_search($relationship_category,RelationshipCategoryTable::$categoryNames);
      if (!$relationship_category_id) 
      {
        $this->printDebug('!!! > relationship type not properly set, skipping row...');
        return;
      }
    }
    $this->printDebug("processing: " . $row['entity_name'] . '......');

    if ($row['primary_type'] == 'Person')
    {
      $entity2 = PersonTable::parseFlatName($row['entity_name']);
      $similar_entities = PersonTable::getSimilarQuery2($entity2)->execute();
    }
    else
    {
    
      $entity2 = new Entity;
      $entity2->addExtension('Org');    
      $entity2->setEntityField('name', $row['entity_name']);  
    
      $similar_entities = OrgTable::getOrgsWithSimilarNames($entity2->name);
    }
    
    $matched = false;
    
    foreach ($similar_entities as $similar_entity)
    {
      if ($similar_entity['primary_ext'] == 'Person')
      {
        $this->printDebug('  POSSIBLE MATCH: ' . $similar_entity->name . ' (Orgs :: ' . $similar_entity->getRelatedOrgsSummary() . "  Bio :: $similar_entity->summary)");
      }
      else
      {
        $this->printDebug('  POSSIBLE MATCH: ' . $similar_entity->name . ' (Summary :: ' . $similar_entity->summary . ')');
      }      
      $accept = $this->readline('  Is this the same entity? (y or n or b to break)');
      if ($accept == 'y')
      {
        $entity2 = $similar_entity;
        $matched = true;
        $this->printDebug('             [accepted]');
        break;
      } 
      else if ($accept == 'b')
      {
        break;
      }     
    }
    
    $created = false;
    if (!$matched)
    {
      if ($entity2->getPrimaryExtension() == 'Person')
      {
        $this->printDebug('  New person: ' . $entity2->name_first . ' ' . $entity2->name_last);
      }
      else
      {
        $this->printDebug('  New org: ' . $entity2->name);
      }
      $accept = $this->readline('    create this new entity? (y or n) ');
      if ($accept == 'y')
      {
      
        try
        {
          $extensions = LsString::split($row['entity_extensions'],'\s*\,\s*');
          foreach($extensions as $extension)
          {
            $entity2->addExtension($extension);
          }
          $entity2->save();   
          $entity2->addReference($url,null,null,$url_name);
        }
        catch (Exception $e)
        {
          $this->printDebug('   !!! problems with extensions for this row');
        }
        $fields = array('summary','blurb','website');
        foreach($fields as $field)
        {
          if (isset($row[$field]))
          {
            $entity2[$field] = $row[$field];
          }
        }
        $entity2->save();
        $entity2->addReference($url,null,null,$url_name);
        $created = true;
        $this->printDebug(' ' . $entity2->name . ' saved');
        //sleep(1);
      }
      else
      {
        $entity2 = null;
      }
    }

    // create relationship
    if ($entity2)
    {
      if ($this->entity)
      {
        $relationship = new Relationship;
        if (isset($row['relationship_order']) && $row['relationship_order'] != '')
        {
          if ($row['relationship_order'] == '1')
          {
            $relationship->Entity1 = $this->entity;
            $relationship->Entity2 = $entity2;
          }
          else
          {
            $relationship->Entity2 = $this->entity;
            $relationship->Entity1 = $entity2;
          }
        }
        else if ($relationship_category == 'Position' || $relationship_category == 'Education')
        {
          if ($row['primary_type'] == 'Org')
          {
            $relationship->Entity1 = $this->entity;
            $relationship->Entity2 = $entity2;
          }
          else
          {
            $relationship->Entity1 = $entity2;    
            $relationship->Entity2 = $this->entity;
          }
        }
        else 
        {
          $relationship->Entity1 = $this->entity;
          $relationship->Entity2 = $entity2;
        }
        $relationship->setCategory($relationship_category);
        $cols = array('description1', 'description2', 'start_date', 'end_date', 'goods', 'amount','is_board','is_executive','is_employee');
        foreach($cols as $col)
        {
          if (isset($row[$col]) && $row[$col] != '')
          {
            try
            {
              $relationship[$col] = $row[$col];
            }
            catch (Exception $e)
            {
              $this->printDebug("   could not set $col for relationship, skipping");
            }
          }
        }
        $q = LsDoctrineQuery::create()
            ->from('Relationship r')
            ->where('r.entity1_id = ? and r.entity2_id = ? and r.category_id = ? and r.id <> ?', array($relationship->entity1_id, $relationship->entity2_id, $relationship->category_id, $relationship->id))
            ->fetchOne();
                
        if ($q)
        {
          $this->printDebug('   (relationship already found, skipping...)');
          return;
        }
        $relationship->save();
        $relationship->addReference($url,null,null,$url_name); 
        $this->printDebug(" Relationship saved: $relationship\n");    
      }
      else if ($this->list)
      {
        $q = LsDoctrineQuery::create()
            ->from('LsListEntity le')
            ->where('le.entity_id = ? and le.list_id = ?', array($entity2->id, $this->list->id))
            ->fetchOne();
                
        if ($q)
        {
          $this->printDebug('   (already on list, skipping...)');
          return;
        }
        
        $le = new LsListEntity;
        $le->LsList = $this->list;
        $le->Entity = $entity2;
        var_dump($row);
        if (isset($row['rank']))
        {
          echo $row['rank'];
          $le->rank = $row['rank'];
        }
        $le->save();
      }
    }
  }
  
  

  public function printDebug($str)
  {
    echo $str . "\n";
  }
  
  public function readline($prompt="", $possible = array('y','n','b'), $lim = 5)
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

