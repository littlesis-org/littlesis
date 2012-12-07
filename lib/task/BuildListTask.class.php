<?php

class BuildListTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'build';
    $this->name             = 'list';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [BuildList|INFO] task does things.
Call it with:

  [php symfony BuildList|INFO]
EOF;
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('list_id', null, sfCommandOption::PARAMETER_REQUIRED, 'id of list to add to', null);
    $this->addOption('related_entity_ids', null, sfCommandOption::PARAMETER_REQUIRED, 'entity ids to pull related entities from', null);
    $this->addOption('category_ids', null, sfCommandOption::PARAMETER_REQUIRED, 'relationship category ids', null);
    $this->addOption('direct_entity_ids',null, sfCommandOption::PARAMETER_REQUIRED, 'entity ids - to add directly to list', null);
    $this->addOption('description',null, sfCommandOption::PARAMETER_REQUIRED, 'searches description1 and description2 for match to this word/phrase', null);
    $this->addOption('is_current',null, sfCommandOption::PARAMETER_REQUIRED, 'is relationship current?', null);
    
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      
    $db = Doctrine_Manager::connection();
    
    $list_id = $options['list_id'];
    
    if ($options['direct_entity_ids'])
    {
      $entity_ids_for_list = explode(",", $options['direct_entity_ids']);  
    }
    else
    {
      $entity_ids = "(" . $options['related_entity_ids'] . ")";
      $category_ids = "(" . $options['category_ids'] . ")";
     
      $sql = 'SELECT l.entity2_id FROM link l LEFT JOIN relationship r ON r.id = l.relationship_id WHERE l.entity1_id in ' . $entity_ids . ' and l.category_id in ' . $category_ids;
      if ($options['description'])
      {
        $sql .= " and (r.description1 like '" . $options['description'] . "' or r.description2 like '" . $options['description'] . "')";
      }
      if ($options['is_current'])
      {
        $sql .= " and r.is_current = " . $options['is_current'];
      }
      $stmt = $db->execute($sql);
      $entity_ids_for_list = $stmt->fetchAll(PDO::FETCH_COLUMN);  
    }
    
    try
    {
      $db->beginTransaction();
      foreach($entity_ids_for_list as $entity_id)
      {
        $sql = 'SELECT count(entity_id) FROM ls_list_entity WHERE entity_id = ? and list_id = ?';
        $stmt = $db->execute($sql, array($entity_id, $list_id));
        $ct = $stmt->fetch(PDO::FETCH_COLUMN);
        if(!$ct)
        {
          $list_entity = new LsListEntity();
          $list_entity->entity_id = $entity_id;
          $list_entity->list_id = $list_id;
          $list_entity->save();
        }
      }
      $db->commit();
    }
    catch (Exception $e)
    {
      $db->rollback();
    }

  }
}