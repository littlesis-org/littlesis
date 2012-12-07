<?php

class RelationshipApi
{
  static function get($id)
  {
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT ' . LsApi::generateSelectQuery(array('r' => 'Relationship')) . ' FROM relationship r WHERE r.id = ? AND r.is_deleted = 0';
    $stmt = $db->execute($sql, array($id));
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
  
  
  static function getDetails($id, $categoryId)
  {
    $categoryName = RelationshipCategoryTable::$categoryNames[$categoryId];
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT * FROM ' . Doctrine_Inflector::tableize($categoryName) . ' WHERE relationship_id = ?';
    $stmt = $db->execute($sql, array($id));
    $ret = $stmt->fetch(PDO::FETCH_ASSOC);
    unset($ret['id'], $ret['relationship_id']);

    return $ret;
  }


  static function getReferences($id)
  {
    $db = Doctrine_Manager::connection();
    $select = LsApi::generateSelectQuery(array('r' => 'Reference'));
    $sql = 'SELECT ' . $select . ' FROM reference r WHERE r.object_model = ? AND r.object_id = ?';
    $stmt = $db->execute($sql, array('Relationship', $id));
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);  
  }


  static function getUri($id, $format='xml')
  {
    return 'http://api.littlesis.org/relationship/' . $id . '.' . $format;
  }


  static function addUris($rel)
  {
    if ($rel['id'])
    {
      $rel['uri'] = RelationshipTable::getUri($rel);    
      $rel['api_uri'] = self::getUri($rel['id']);
    }
    else
    {
      $rel['uri'] = null;
      $rel['api_uri'] = null;
    }

    return $rel;
  }  
}