<?php

class UpdateLinksTask extends LsTask
{
  protected $db = null;
  

  protected function configure()
  {
    $this->namespace        = 'links';
    $this->name             = 'update';
    $this->briefDescription = 'Maintains two unidirectional links for each relationship';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Maximum number of Relationship modifications to process', 100);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      


    $this->db = Doctrine_Manager::connection();


    //get last processed merge modification
    if (!$this->hasMeta('log', 'last_modification_id'))
    {
      $lastModId = 0;
    }
    else
    {
      $lastModId = $this->getMeta('log', 'last_modification_id');
    }

    //get merges since last processed merge modification
    $sql = 'SELECT m.* FROM modification m WHERE m.id > ? AND m.object_model = ? AND m.is_merge = 1 AND m.merge_object_id IS NOT NULL ORDER BY m.id DESC';
    $stmt = $this->db->execute($sql, array($lastModId, 'Entity'));
    $mods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //for each merge, update any links involving the merged entity
    foreach ($mods as $mod)
    {
      if ($options['debug_mode'])
      {
        print("Updating links for merged entity " . $mod['object_id'] . " (to " . $mod['merge_object_id'] . ")\n");
      }

      $sql = 'UPDATE link l SET entity1_id = ? WHERE entity1_id = ?';
      $stmt = $this->db->execute($sql, array($mod['merge_object_id'], $mod['object_id']));

      $sql = 'UPDATE link l SET entity2_id = ? WHERE entity2_id = ?';
      $stmt = $this->db->execute($sql, array($mod['merge_object_id'], $mod['object_id']));
    }
    
    //save highest merge modification for next time
    if (count($mods))
    {
      $this->saveMeta('log', 'last_modification_id', $mods[0]['id']);
    }
    


    //get relationships with is_deleted = 0 and without links
    $sql = 'SELECT r.* FROM relationship r LEFT JOIN link l ON (r.id = l.relationship_id) WHERE r.is_deleted = 0 AND l.id IS NULL ORDER BY r.id LIMIT ' . $options['limit'];
    $stmt = $this->db->execute($sql);
    $rels = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rels as $rel)
    {
      if ($options['debug_mode'])
      {
        print("Creating Links for Relationship " . $rel['id'] . "\n");
      }

      $this->createLinks($rel);
    }


    //get relationships with is_deleted = 1 and with links
    $sql = 'SELECT r.* FROM relationship r LEFT JOIN link l ON (r.id = l.relationship_id) WHERE r.is_deleted = 1 AND l.id IS NOT NULL GROUP BY r.id LIMIT ' . $options['limit'];
    $stmt = $this->db->execute($sql);
    $rels = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rels as $rel)
    {
      if ($options['debug_mode'])
      {
        print("Deleting Links for Relationship " . $rel['id'] . "\n");
      }

      $this->deleteLinks($rel);
    }

    
    //DONE
    LsCli::beep();
  }


  public function createLinks($rel)
  {
    $sql = 'INSERT INTO link (entity1_id, entity2_id, category_id, relationship_id, is_reverse) VALUES(?, ?, ?, ?, ?), (?, ?, ?, ?, ?)';
    $stmt = $this->db->execute($sql, array($rel['entity1_id'], $rel['entity2_id'], $rel['category_id'], $rel['id'], false, $rel['entity2_id'], $rel['entity1_id'], $rel['category_id'], $rel['id'], true));  
  }
  
  
  public function deleteLinks($rel)
  {
    $sql = 'DELETE FROM link WHERE relationship_id = ?';
    $stmt = $this->db->execute($sql, array($rel['id']));
  }
}