<?php

class BatchApi
{
  static function getEntities($ids, $options=array())
  {
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT ' . LsApi::generateSelectQuery(array('e' => 'Entity')) . ' FROM entity e WHERE e.id IN (' . implode(',', $ids) . ') AND e.is_deleted = 0';
    $stmt = $db->execute($sql);

    if (@$options['details'])
    {
      $entities = array();

      foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $entity)
      {
        $entities[] = array_merge($entity, EntityApi::getDetails($entity['id']));
      }
      
      return $entities;
    }
    else
    {
      return $stmt->fetchAll(PDO::FETCH_ASSOC);  
    }
  }


  static function getEntitiesWithOrgs(Array $ids, $options)
  {
    $results = array();

    //first get the entities themselves
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT e.id, e.name, p.gender_id ' . 
           'FROM entity e ' . 
           'LEFT JOIN person p ON (p.entity_id = e.id) ' .
           'WHERE e.id IN (' . implode(',', $ids) . ') AND e.is_deleted = 0';
    $stmt = $db->execute($sql);
    
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $entity)
    {
      $results[$entity['id']] = array(
        'entity' => $entity, 
        'orgs' => array()
      );
    }

    $entityIds = array_keys($results);

    if (!count($entityIds))
    {
      return array();
    }

    //now get the entities' companies
    $selectTables = array('e' => 'Entity');
    $select = 'e.id, e.name, r.entity1_id, MAX(p.is_board) is_board, MAX(p.is_executive) is_executive, GROUP_CONCAT(DISTINCT r.description1) titles';
    $from = 'relationship r LEFT JOIN entity e ON (e.id = r.entity2_id) ' .
            'LEFT JOIN position p ON (p.relationship_id = r.id)';
    $where = 'r.entity1_id IN (' . implode(', ', $entityIds) . ') AND (p.is_board = 1 OR p.is_executive = 1)';
    $params = array(); 

    if (isset($options['is_current']))
    {
      $where .= ' AND r.is_current = ?';
      $params[] = $options['is_current'];
    }
    
    $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where . ' GROUP BY r.entity2_id';
    $stmt = $db->execute($sql, $params);

    $entityMap = LsApi::$responseFields['Entity'];
   
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $entity)
    {
      $directorId = $entity['entity1_id'];
      unset($entity['entity1_id']);
      $results[$directorId]['orgs'][] = $entity;
    }
    
    return $results;    
  }


  static function getTheyRulePersons($ids, $options=array())
  {
    $results = array_fill_keys($ids, array('entity' => null, 'orgs' => array()));
    
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT e.id, e.name, p.gender_id FROM entity e LEFT JOIN person p ON (p.entity_id = e.id) WHERE e.id IN (' . implode(', ', $ids) . ') AND e.is_deleted = 0';
    $stmt = $db->execute($sql);
    
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $entity)
    {
      $results[$entity['id']]['entity'] = $entity;
    }
    
    //get basic data and orgs for entities
    $selectTables = array('e' => 'Entity');
    $select = 'e.id, e.name, r.entity1_id, MAX(p.is_board) is_board, MAX(p.is_executive) is_executive, GROUP_CONCAT(DISTINCT r.description1) titles';
    $from = 'relationship r LEFT JOIN entity e ON (e.id = r.entity2_id) ' .
            'LEFT JOIN position p ON (p.relationship_id = r.id)';
    $where = 'r.entity1_id IN (' . implode(', ', $ids) . ') AND (p.is_board = 1 OR p.is_executive = 1)';
    $params = array(); 

    if (isset($options['is_current']))
    {
      $where .= ' AND r.is_current = ?';
      $params[] = $options['is_current'];
    }
    
    $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where . ' GROUP BY r.entity2_id';
    $stmt = $db->execute($sql, $params);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $entity)
    {
      $directorId = $entity['entity1_id'];
      unset($entity['entity1_id']);
      $results[$directorId]['orgs'][] = $entity;
    }
    
    return $results;
  }
  
  
  static function getRelationships($ids, $options)
  {
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT ' . LsApi::generateSelectQuery(array('r' => 'Relationship')) . ' FROM relationship r WHERE r.id IN (' . implode(',', $ids) . ')';
    $stmt = $db->execute($sql);
    
    if (@$options['details'])
    {
      $rels = array();

      if ($catId = @$options['cat_id'])
      {
        //make Relationships accessible by id
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $rel)
        {
          $rels[$rel['id']] = $rel;
        }


        //get category data
        $categoryName = RelationshipCategoryTable::$categoryNames[$catId];
    
        $db = Doctrine_Manager::connection();
        $sql = 'SELECT * FROM ' . Doctrine_Inflector::tableize($categoryName) . ' WHERE relationship_id IN (' . implode(',', $ids) . ')';
        $stmt = $db->execute($sql, array($id));


        //merge category data with relationship data
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row)
        {
          $relId = $row['relationship_id'];
          unset($row['id'], $row['relationship_id']);
          
          $rels[$relId] = array_merge($rels[$relId], $row);
        }
        
        return array_values($rels);
      }
      else
      {
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $rel)
        {
          $rels[] = array_merge($rel, RelationshipApi::getDetails($rel['id'], $rel['category_id']));
        }
        
        return $rels;
      }
    }
    else
    {
      return $stmt->fetchAll(PDO::FETCH_ASSOC);    
    }    
  }
}