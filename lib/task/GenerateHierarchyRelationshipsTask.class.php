<?php

class GenerateHierarchyRelationshipsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'generate';
    $this->name             = 'hierarchy-relationships';
    $this->briefDescription = 'creates hierarchy relationships based on old hierarchy structure';
    $this->detailedDescription = <<<EOF
This task creates hierarchy relationships based on old hierarchy structures.
EOF;
    
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'How many entities to perform this operation on', 5000);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      


    $db = Doctrine_Manager::connection();

    //get entities with parent_ids
    $sql = 'SELECT e.id, e.parent_id FROM entity e ' . 
           'LEFT JOIN entity p ON (p.id = e.parent_id) LEFT JOIN relationship r on (r.entity1_id = e.id AND r.entity2_id = e.parent_id AND r.category_id = ?)' . 
           'WHERE e.parent_id IS NOT NULL AND e.is_deleted <> 1 AND p.is_deleted <> 1 AND r.id IS NULL';
           
    $stmt = $db->execute($sql, array(RelationshipTable::HIERARCHY_CATEGORY));
    $entities = $stmt->fetchAll(PDO::FETCH_ASSOC);    

    //create hierarchy relationship for each entity
    foreach ($entities as $entity)
    {
      print("creating hierarchy relationship between entity " . $entity['id'] . " and entity " . $entity['parent_id'] . "\n");

      //create relationship
      $sql = 'INSERT into relationship (entity1_id,entity2_id,category_id,is_current,last_user_id,created_at,updated_at) VALUES (?,?,11,1,1,NOW(),NOW())';
      $stmt = $db->execute($sql, array($entity['id'], $entity['parent_id']));
      $lastId = $db->lastInsertId();

      //create hierarchy
      $sql = 'INSERT INTO hierarchy (relationship_id) VALUES (?)';
      $stmt = $db->execute($sql, array($lastId));
    }
    
    //DONE
    LsCli::beep();
  }
}