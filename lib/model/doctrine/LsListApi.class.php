<?php

class LsListApi
{
  static function get($id)
  {
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT ' . LsApi::generateSelectQuery(array('l' => 'LsList')) . ' FROM ls_list l WHERE l.id = ?';
    $stmt = $db->execute($sql, array($id));
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
  
  
  static function getEntities($id, $options=array())
  {
    $db = Doctrine_Manager::connection();
    $select = LsApi::generateSelectQuery(array('e' => 'Entity')) . ', le.rank';
    $from = 'entity e LEFT JOIN ls_list_entity le ON (le.entity_id = e.id)';
    $where = 'le.list_id = ? AND le.is_deleted = 0 AND e.is_deleted = 0';

    $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where . ' ORDER BY rank, id';
    $stmt = $db->execute($sql, array($id));
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  

  static function getEntityIds($id, $options=array())
  {
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT le.entity_id FROM entity e LEFT JOIN ls_list_entity le ON (le.entity_id = e.id) ' .
           'WHERE le.list_id = ? AND le.is_deleted = 0 AND e.is_deleted = 0';
    $stmt = $db->execute($sql, array($id));
    
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }


  static function getNetworkLinks($id, $options=array())
  {
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT DISTINCT l.relationship_id, le.entity_id AS entity1_id, l.entity2_id, l.category_id ' .
           'FROM ls_list_entity le ' . 
           'LEFT JOIN entity e1 ON (e1.id = le.entity_id) ' .
           'LEFT JOIN couple c1 ON (c1.entity_id = e1.id) ' .
           'LEFT JOIN link l ON (l.entity1_id IN (le.entity_id, c1.partner1_id, c1.partner2_id)) ' .
           'LEFT JOIN entity e2 ON (e2.id = l.entity2_id) ' . 
           'LEFT JOIN relationship r ON (r.id = l.relationship_id) ' .
           'WHERE le.list_id = ? AND le.is_deleted = 0 AND e1.is_deleted = 0 AND e2.is_deleted = 0 AND r.is_deleted = 0';
    $stmt = $db->execute($sql, array($id));

    return $stmt->fetchAll(PDO::FETCH_NUM);
  }


  static function getSecondDegreeNetwork($id, $options=array(), $countOnly=false)
  {
    $db = Doctrine_Manager::connection();
    $select = LsApi::generateSelectQuery(array('e2' => 'Entity')) . ', GROUP_CONCAT(DISTINCT l.entity1_id) degree1_ids, COUNT(DISTINCT l.entity1_id) degree1_num, SUM(r.amount) degree1_total';
    $from = 'ls_list_entity le ' .
            'LEFT JOIN link l ON (l.entity1_id = le.entity_id) ' .
            'LEFT JOIN relationship r ON (r.id = l.relationship_id) ' .
            'LEFT JOIN entity e1 ON (e1.id = l.entity1_id) ' .
            'LEFT JOIN entity e2 ON (e2.id = l.entity2_id)';

    if ($order = @$options['order'])
    {
      $isReverse = (int) ($order == 2);
      $where = 'le.list_id = ? AND le.is_deleted = 0 AND l.is_reverse = ? AND e1.is_deleted = 0 AND e2.is_deleted = 0';
      $params = array($id, $isReverse);
    }
    else
    {
      $where = 'le.list_id = ? AND le.is_deleted = 0 AND e1.is_deleted = 0 AND e2.is_deleted = 0';
      $params = array($id);
    }
    
    if ($catIds = @$options['cat_ids'])
    {
      if (count(explode(',', $catIds)) == 1)
      {
        $where .= ' AND l.category_id = ?';
        $params[] = $catIds;
      }
      else
      {
        $where .= ' AND l.category_id IN (' . $catIds . ')';
      }    
    }
    
    if ($degree1Type = @$options['degree1_type'])
    {
      $where .= ' AND e1.primary_ext = ?';
      $params[] = $degree1Type;
    }

    if ($degree2Type = @$options['degree2_type'])
    {
      $where .= ' AND e2.primary_ext = ?';
      $params[] = $degree2Type;
    }

    if ($countOnly)
    {
      $sql = 'SELECT COUNT(DISTINCT l.entity2_id) FROM ' . $from . ' WHERE ' . $where;
    }
    else
    {
      $paging = LsApi::getPagingFromOptions($options, $defaultNum=10, $maxNum=20);
      $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where . ' GROUP BY e2.id ORDER BY ' . (@$options['sort'] == 'amount' ? 'degree1_total DESC, degree1_num DESC' : 'degree1_num DESC, degree1_total DESC') . ' ' . $paging;
    }
    
    $stmt = $db->execute($sql, $params);
    
    if ($countOnly)
    {
      return $stmt->fetchColumn();
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  
  static function getEntitiesWithOrgs($id, $options=array())
  {
    $results = array();

    //first get list members
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT e.id, e.name, p.gender_id ' . 
           'FROM ls_list_entity le LEFT JOIN entity e ON (e.id = le.entity_id) ' . 
           'LEFT JOIN person p ON (p.entity_id = e.id) ' .
           'WHERE le.list_id = ? AND le.is_deleted = 0 AND e.primary_ext = ? AND e.is_deleted = 0';
    $params = array($id, 'Person');
    $stmt = $db->execute($sql, $params);
    
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
  
  
  static function getUri($id, $format='xml')
  {
    return 'http://api.littlesis.org/list/' . $id . '.' . $format;
  }
  
  
  static function addUris($list)
  {
    if (!$list['id'] || !$list['name'])
    {
      return null;
    }
    
    $list['uri'] = LsListTable::getUri($list);
    $list['api_uri'] = self::getUri($list['id']);
    
    return $list;
  }

  static function getImages($id, $options=array())
  {
    $db = Doctrine_Manager::connection();

    if (isset($options['caption']))
    {
      $sql = 'SELECT le.entity_id, i.filename FROM ls_list_entity le ' . 
             'JOIN image i ON (le.entity_id = i.entity_id AND i.is_deleted = 0) ' .
             'WHERE le.list_id = ? AND le.is_deleted = 0 AND i.caption LIKE ?';
      $stmt = $db->execute($sql, array($id, $options['caption']));
    } else {
      $sql = 'SELECT le.entity_id, i.filename FROM ls_list_entity le ' . 
             'JOIN image i ON (le.entity_id = i.entity_id AND i.is_featured = 1 AND i.is_deleted = 0) ' .
             'WHERE le.list_id = ? AND le.is_deleted = 0 AND (i.caption IS NULL OR i.caption NOT LIKE "street view:%")';
      $stmt = $db->execute($sql, array($id));
    }

    $rows = $stmt->fetchAll();

    return array_map(function($row) {
      return array('id' => intval($row['entity_id']), 'url' => ImageTable::generateS3Url('profile/' . $row['filename']));
    }, $rows);
  }

  static function getSearchData($id)
  {
    $db = Doctrine_Manager::connection();
    $sql = "SELECT e.id, e.name, e.blurb AS description, e.primary_ext AS primary_type, GROUP_CONCAT(DISTINCT a.name SEPARATOR ';') AS aliases FROM ls_list_entity le " . 
       'JOIN entity e ON (le.entity_id = e.id) ' .
       'LEFT JOIN alias a ON (a.entity_id = e.id) ' .
       'WHERE le.list_id = ? AND le.is_deleted = 0 AND e.is_deleted = 0 ' . 
       'GROUP BY e.id';
    $stmt = $db->execute($sql, array($id));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(function($row) {
      $row['aliases'] = explode(';', $row['aliases']);
      $row['url'] = EntityTable::getUri($row);
      return $row;
    }, $rows);
  }
}