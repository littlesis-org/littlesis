<?php


require_once(sfConfig::get('sf_root_dir') . '/lib/task/LsTask.class.php');

class UnMergeEntitiesTask extends LsTask
{
  protected
    $db = null,
    $rawDb = null,
    $browser = null,
    $debugMode = null,
    $testMode = null,
    $startTime = null,
    $databaseManager = null,
    $entity1_id = null,
    $entity2_id = null;


  protected function configure()
  {
    $this->namespace        = 'cleanup';
    $this->name             = 'unmerge';
    $this->briefDescription = 'reverts accidental merges -- finicky, test before using!';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', true);
    $this->addOption('entity1_id', null, sfCommandOption::PARAMETER_REQUIRED, 'entity id to start with', null);
  }
  
  
  protected function execute($arguments = array(), $options = array())
  {
    
    $this->init($arguments, $options);
    
    if ($this->testMode) echo "TESTING -- no changes will be saved\n";

    $merge_modification = $this->getLastMerge();
    
    if (!$merge_modification) die;
    
    $this->entity2_id = $merge_modification['object_id'];
  
    $this->entity1 = Doctrine::getTable('Entity')->find($this->entity1_id);
    $this->entity2 = Doctrine::getTable('Entity')->find($this->entity2_id);
  
  
    //GET ALL EXTENSIONS ADDED WITHIN A CERTAIN RANGE OF MODIFICATION TIME
    $sql = "SELECT m.* FROM extension_record er LEFT JOIN modification m on m.object_id = er.id AND m.object_model = ? WHERE er.entity_id = ? AND TIME_TO_SEC(TIMEDIFF(?,m.created_at)) < 100 and is_create = 1";
    $stmt = $this->db->execute($sql,array('ExtensionRecord',$this->entity1_id,$merge_modification['created_at']));
    $ext_mods = $stmt->fetchAll(PDO::FETCH_ASSOC);    

    //REMOVE EXTENSIONS THAT WERE ADDED
    if(count($ext_mods))
    {
      foreach($ext_mods as $m)
      {
        $er = Doctrine::getTable("ExtensionRecord")->find($m['object_id']);
        $ed = Doctrine::getTable("ExtensionDefinition")->find($er['definition_id']);
        if($ed)
        {
          $this->printDebug("removing " . $ed['name'] . " extension now");
          if (!$this->testMode)
          {
            $this->entity1->removeExtension($ed['name']);
            $this->entity1->save();
          }
        }
      }
    }
    
    //GET MODIFIED FIELDS
    $sql = "SELECT mf.* FROM modification_field mf LEFT JOIN modification m ON m.id = mf.modification_id WHERE m.object_model = ? AND m.object_id = ? AND TIME_TO_SEC(TIMEDIFF(?,m.created_at)) < 100"; 
    $stmt = $this->db->execute($sql,array('Entity',$this->entity1_id,$merge_modification['created_at']));
    $ent_mods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($ent_mods as $ent_mod)
    {
      $this->printDebug("\tchange " . $ent_mod['field_name'] . ": " . $ent_mod['new_value'] . " back to " . $ent_mod['old_value'] . "");
    }
    
    //UNDELETE ENTITY 2
    $sql = "UPDATE entity e SET e.is_deleted = 0 WHERE e.id = ?";     
    if (!$this->testMode)
    {
      $stmt = $this->db->execute($sql,array($this->entity2_id));
    }
    
    //GET ALL MODIFICATIONS THAT WE KNOW SOMETHING ABOUT
    $sql = "SELECT * FROM modification_field WHERE field_name like ? AND old_value = ? AND new_value = ?";
    $stmt = $this->db->execute($sql,array('%_id%',$this->entity2_id,$this->entity1_id));
    $all_mod_fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $rels_updated = array();
    foreach($all_mod_fields as $mod_field)
    {
      $mod_id = $mod_field['modification_id'];
      $mod = Doctrine::getTable('Modification')->find($mod_id);
      if($mod->object_model == 'Relationship')
      {
        //set merge to true or false?
        $rel = Doctrine::getTable("Relationship")->find($mod['object_id']);

        if ($rel)
        {
          if((strtotime($rel->updated_at) - strtotime($merge_modification['created_at'])) > 100)
          {
            $rels_updated[] = $rel;
          }
          else
          {
            $prevName = RelationshipTable::getName($rel);
            $rel[$mod_field['field_name']] = $this->entity2_id;
          
            if (!$this->testMode)
            {
              $rel->save();
            }
            $this->printDebug($prevName . " changed to " . RelationshipTable::getName($rel));
            
            //UPDATE THE LINKS
            $links = Doctrine::getTable("Link")->findByRelationshipId($rel->id);
            foreach($links as $l)
            {
              if($l->entity1_id == $this->entity1_id)
              {
                $l->entity1_id = $this->entity2_id;
              }
              else if($l->entity2_id == $this->entity1_id)
              {
                $l->entity2_id = $this->entity2_id;
              }
              if (!$this->testMode)
              {
                $l->save();
              }
              $this->printDebug("link updated");
            }
            
          }
         
        }  
        else
        {
          $this->printDebug("\tNo relationship found");
        }        
      }
      else
      {
        $this->revertObject($mod->object_model, $mod['object_id'],$mod_field['field_name']);
      }
    }
    //GET ALL REFERENCES ON ENTITY 1 THAT SHOULD ACTUALLY BE FOR ENTITY 2
    $sql = "SELECT * FROM reference WHERE object_id = ? AND object_model = ? AND TIME_TO_SEC(TIMEDIFF(?,updated_at)) < 100";
    $stmt = $this->db->execute($sql,array($this->entity1_id,'Entity',$merge_modification['created_at']));
    $references = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($references as $ref)
    {
      if (!$this->testMode)
      {
        $ref['object_id'] = $this->entity2_id;
      }
      $this->printDebug("Reference " . $ref['id'] . " changed");
    }
    
    foreach($rels_updated as $rel)
    {
      $this->printDebug("updated after: " . RelationshipTable::getName($rel));
    }
  }
  
  
  protected function revertObject($obj_class,$obj_id,$field_name)
  {
    $obj = Doctrine::getTable($obj_class)->find($obj_id);
    if($obj)
    {
      $obj[$field_name] = $this->entity2_id;
      if (!$this->testMode)
      {
        $obj->save();
      }
      $this->printDebug($obj_class . " " . $obj->id . " changed");
    }
    else
    {
      $this->printDebug("\tNo $obj_class found");
    }
  }
  
  protected function init($arguments, $options)
  {
    $this->startTime = microtime(true);

    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $this->databaseManager = new sfDatabaseManager($configuration);
    $this->databaseManager->initialize($configuration);
    $db = $this->databaseManager->getDatabase('main');
    $this->db = Doctrine_Manager::connection($db->getParameter('dsn'), 'main');
    $rawDb = $this->databaseManager->getDatabase('raw');
 
    $this->debugMode = $options['debug_mode'];
    $this->testMode = $options['test_mode'];
    $this->entity1_id = $options['entity1_id'];
  }
  
  protected function getLastMerge()
  {
    $sql = 'SELECT * FROM modification WHERE ' .
          'object_model = ? AND merge_object_id = ? AND is_merge = ?';
    $stmt = $this->db->execute($sql,array('Entity',$this->entity1_id,1));
    $modifications = $stmt->fetchAll(PDO::FETCH_ASSOC);    
    if (count($modifications) > 1)
    {
      $this->printDebug("More than one merge found for entity");
      return false;
    }
    else if (count($modifications) == 0)
    {
      $this->printDebug("No merge found for entity");
    }
    else {
      $this->printDebug("Merged object found: " . $modifications[0]['object_name']);
      return $modifications[0];
    }
  }
  
}