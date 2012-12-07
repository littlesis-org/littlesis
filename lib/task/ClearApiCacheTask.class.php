<?php

require_once(dirname(__FILE__) . '/LsTask.class.php');

class ClearApiCacheTask extends LsTask
{
  protected function configure()
  {
    $this->namespace        = 'api';
    $this->name             = 'clear-cache';
    $this->briefDescription = 'Clears cache for recently-edited entities and relationships';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->debugMode = $options['debug_mode'];


    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);


    $db = Doctrine_Manager::connection();
    $cache = LsApiCacheFilter::getApiCache();
    $models = array('Entity', 'Relationship', 'LsList', 'Image', 'Alias', 'Reference');
    $modelsToClear = array();


    foreach ($models as $model)
    {
      //get max modification id from last execution
      if (!$this->hasMeta($model, 'last_modification_id'))
      {
        //if no max modification id set, then set it
        $sql = 'SELECT MAX(id) FROM modification m WHERE m.object_model = ?';
        $stmt = $db->execute($sql, array($model));
        $result = $stmt->fetch(PDO::FETCH_COLUMN);
        $this->saveMeta($model, 'last_modification_id', $result);
      }

      $lastId = $this->getMeta($model, 'last_modification_id');

      //get records modified since then
      $sql = 'SELECT id, object_id FROM modification m WHERE id > ? AND object_model = ? AND is_create = 0 ORDER BY id';
      $stmt = $db->execute($sql, array($lastId, $model));
      
      $maxId = null;
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if (count($rows))
      {
        $modelsToClear[] = $model;
      }
      
      foreach ($rows as $row)
      {
        $this->printDebug('Clearing API cache for ' . $model . ' ' . $row['object_id']);

        //clear record cache
        $cacheClearMethod = 'get' . $model . 'Patterns';

        foreach (self::$cacheClearMethod($row['object_id']) as $pattern)
        {
          $cache->removePattern($pattern);
        } 
        
        $maxId = $row['id'];
      }
          
      //set new max modification id
      if ($maxId)
      {
        $this->saveMeta($model, 'last_modification_id', $maxId);
      }
    }

    
    //we're not done yet! gotta clear searches!
    if (in_array('Entity', $modelsToClear))
    {
      $this->printDebug('Clearing API cache for Entity search');      
      $cache->removePattern('/entities*');
      $cache->removePattern('/batch/entities*');
    }
    
    if (in_array('Relationship', $modelsToClear))
    {
      $this->printDebug('Clearing API cache for Relationship search');
      $cache->removePattern('/relationships*');
      $cache->removePattern('/batch/relationships*');
    }
    
    if (in_array('LsList', $modelsToClear))
    {
      $this->printDebug('Clearing API cache for List search');
      $cache->removePattern('/lists*');
    }    
  }


  static function getEntityPattern($id)
  {
    return '/entity/' . $id . '*';
  }


  static function getEntityPatterns($id)
  {
    //clear this entity
    $patterns = array(self::getEntityPattern($id));

    //also clear related entities
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT entity1_id, entity2_id FROM relationship r WHERE (entity1_id = ? OR entity2_id = ?) AND r.is_deleted = 0';
    $stmt = $db->execute($sql, array($id, $id));
    
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row)
    {
      $relatedId = ($row['entity1_id'] == $id) ? $row['entity2_id'] : $row['entity1_id'];

      foreach (array('relationships', 'board', 'boards', 'related') as $part)
      {
        $patterns[] = '/entity/' . $relatedId . '/' . $part . '*';
      }
    }

    //also clear lists it's on
    $sql = 'SELECT le.list_id FROM ls_list_entity le WHERE le.entity_id = ?';
    $stmt = $db->execute($sql, array($id));
    
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $listId)
    {
      $patterns[] = '/list/' . $listId . '/entities*';
    }
 
    //also clear parent entity, if any
    $entity = EntityApi::get($id);
    if ($parentId = $entity['parent_id'])
    {
      $patterns[] = '/entity/' . $parentId . '/child-orgs*';
    }
 
    return $patterns;
  }


  static function getRelationshipPattern($id)
  {
    return '/relationship/' . $id . '*';
  }
  
  
  static function getRelationshipPatterns($id)
  {
    //clear relationship cache
    $patterns = array(self::getRelationshipPattern($id));
    
    //clear entity cache
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT entity1_id, entity2_id FROM relationship r WHERE r.id = ?';
    $stmt = $db->execute($sql, array($id));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    foreach (array('relationships', 'board', 'boards', 'related') as $part)
    {
      $patterns[] = '/entity/' . $row['entity1_id'] . '/' . $part . '*';
      $patterns[] = '/entity/' . $row['entity2_id'] . '/' . $part . '*';
    }

    return $patterns;
  }
  
  
  static function getLsListPattern($id)
  {
    return '/list/' . $id . '*';
  }
  
  
  static function getLsListPatterns($id)
  {
    //clear list cache
    $patterns = array(self::getLsListPattern($id));
    
    //clear cache for member entities
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT le.entity_id FROM ls_list_entity le WHERE le.list_id = ?';
    $stmt = $db->execute($sql, array($id));
    
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $entityId)
    {
      $patterns[] = '/entity/' . $entityId . '/lists*';
    }
    
    return $pattern;
  }
  
  
  static function getImagePatterns($id)
  {
    //clear image resource of the image's entity
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT i.entity_id FROM image i WHERE i.id = ?';
    $stmt = $db->execute($sql, array($id));
    $entityId = $stmt->fetch(PDO::FETCH_COLUMN);
    
    return array('/entity/' . $entityId . '/image*');
  }
  
  
  static function getAliasPatterns($id)
  {
    //clear image resource of the alias' entity
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT a.entity_id FROM alias a WHERE a.id = ?';
    $stmt = $db->execute($sql, array($id));
    $entityId = $stmt->fetch(PDO::FETCH_COLUMN);
    
    return array('/entity/' . $entityId . '/aliases*');
  }
  
  
  static function getReferencePatterns($id)
  {
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT r.object_model, r.object_id FROM reference r WHERE r.id = ?';
    $stmt = $db->execute($sql, array($id));
    $ref = $stmt->fetch(PDO::FETCH_ASSOC);

    //clear reference resources if model is entity or relationship
    switch ($ref['object_model'])
    {
      case 'Entity':
        $patterns = array(
          '/entity/' . $ref['object_id'] . '/references*',
          '/entity/' . $ref['object_id'] . '/relationships/references*'
        );
        break;
      case 'Relationship':
        $patterns = array('/relationship/' . $ref['object_id'] . '/references*');
        break;
      default:
        $patterns = array();
        break;
    }    
    
    return $patterns;
  }
}