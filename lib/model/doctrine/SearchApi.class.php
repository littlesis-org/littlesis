<?php

class SearchApi
{
  static $foreignIdMap = array(
    'fedspending_id' => 'Org',
    'lda_registrant_id' => array('Org', 'Lobbyist'),
    'sec_cik' => array('BusinessPerson', 'PublicCompany'),
    'ticker' => 'PublicCompany',
    'fec_id' => 'PoliicalFundraising',
    'pres_fec_id' => 'PoliticalCandidate',
    'senate_fec_id' => 'PoliticalCandidate',
    'house_fec_id' => 'PoliticalCandidate',
    'bioguide_id' => 'ElectedRepresentative',
    'govtrack_id' => 'ElectedRepresentative',
    'crp_id' => 'ElectedRepresentative',
    'watchdog_id' => 'ElectedRepresentative',
    'pvs_id' => 'ElectedRepresentative'
  );
  

  static function getEntities($options=array())
  {
    $s = new LsSphinxClient();
    $s->SetServer('localhost', 3312);
    $s->SetMatchMode(SPH_MATCH_ANY);
    $s->SetFieldWeights(array('name' => 3, 'aliases' => 3));

    if (@$options['list_ids'])
    {
      $listIds = explode(',', $options['list_ids']);

      if (is_array($listIds) && count($listIds))
      {
        $s->setFilter('list_ids', $listIds);
      }
    }

    //no query produces no results
    if (!$query = @$options['q'])
    {
      $query = 'bleahbleahbleahbleahbleahbleahbleah';
    }

    $query = LsSphinxClient::cleanQuery($query);
    $query = $s->EscapeString($query);

    //filter by type_ids, if requested    
    if ($typeIds = @$options['type_ids'])
    {
      $s->SetFilter('type_ids', explode(',', $typeIds));
    }

    //paging
    if (!$num = @$options['num'])
    {
      $num = 20;
    }
    
    if (!$page = @$options['page'])
    {
      $page = 1;
    }
    
    $s->SetLimits(($page - 1) * $num, (int) $num);

    $result = $s->Query($query, 'entities entities-delta');
    
    if ($result === false)
    {
      throw new Exception("Sphinx search failed: " . $s->getLastError());
    }
    
    return $result;
  }
  
  
  static function getEntitiesByForeignId($idName, $idValue, $options=array())
  {
    $q = Doctrine_Query::create()
      ->select('DISTINCT e.*')
      ->from('Entity e')
      ->orderBy('e.id DESC')
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);

    $classes = (array) self::$foreignIdMap[$idName];
    $whereParts = array();
    $params = array();
        
    foreach ($classes as $class)
    {
      $lower = strtolower($class);
      $q->leftJoin('e.' . $class . ' AS ' . $lower);
      $whereParts[] = $lower . '.' . $idName . ' = ?';
      $params[] = $idValue;
    }
    
    if (count($classes))
    {
      $sql = implode(' OR ', $whereParts);
      $q->addWhere($sql, $params);
    }
    
    return $q->execute();
  }


  static function getRelationships($id1, $id2, $options=array())
  {
    $db = Doctrine_Manager::connection();
    $select = LsApi::generateSelectQuery(array('r' => 'Relationship'));
    $from = 'link l LEFT JOIN relationship r ON (r.id = l.relationship_id)';
    $where = 'l.entity1_id = ? AND l.entity2_id = ? AND r.is_deleted = 0';
    $params = array($id1, $id2);
    

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

    $paging = LsApi::getPagingFromOptions($options, $defaultNum=20, $maxNum=100);      
    $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where . ' ' . $paging;
    $stmt = $db->execute($sql, $params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  
  static function getLists($options=array())
  {
    $q = Doctrine_Query::create()
      ->select('DISTINCT l.*, COUNT(le.id) AS num_entities')
      ->from('LsList l')
      ->leftJoin('l.LsListEntity le')
      ->groupBy('l.id')
      ->orderBy('num_entities DESC')
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);

    $q = LsApi::setPagingFromOptions($q, $options, $defaultNum=100, $maxNum=100);

    if ($text = @$options['q'])
    {
      $q->addWhere('l.name LIKE ? OR l.description LIKE ?', array('%' . $text . '%', '%' . $text . '%'));
    }
    
    return $q->execute();
  }
  
  
  static function getEntitiesChains($id1, $id2, $options=array())
  {
    $db = Doctrine_Manager::connection();
    $chains = array(
      1 => array(),
      2 => array(),
      3 => array(),
      4 => array()
    );
 
    //get second degree network for entity1
    $sql = 'SELECT l1.entity2_id, l2.entity2_id ' . 
           'FROM link l1 LEFT JOIN link l2 ON (l1.entity2_id = l2.entity1_id) ' .
           'WHERE l1.entity1_id = ? AND l2.entity2_id <> ?';
    $params = array($id1, $id1);

    if ($catIds = @$options['cat_ids'])
    {
      if (count(explode(',', $catIds)) == 1)
      {
        $sql .= ' AND l1.category_id = ? AND l2.category_id = l1.category_id';
        $params[] = $catIds;
      }
      else
      {
        $sql .= ' AND l1.category_id IN (' . $catIds . ') AND l2.category_id = l1.category_id';
      }
    }
    
    $stmt = $db->execute($sql, $params);
    $network1 = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);
    $firsts1 = array_keys($network1);

    //second degree ids with the first degree ids they're connected via
    $seconds1 = array();   
    
    foreach ($network1 as $first => $seconds)
    {
      foreach (array_unique($seconds) as $second)
      {
        if (isset($seconds1[$second]))
        {
          $seconds1[$second][] = $first;
        }
        else
        {
          $seconds1[$second] = array($first);
        }
      }
    }
        

    //look for direct connection
    if (in_array($id2, $firsts1))
    {
      $chains[1][] = array($id1, $id2);
    }
    
    
    //look for second degree connection
    if (in_array($id2, array_keys($seconds1)))
    {
      foreach ($seconds1[$id2] as $first)
      {
        $chains[2][] = array($id1, $first, $id2);        
      }
    }


    //get second degree network for entity2
    $sql = 'SELECT l1.entity2_id, l2.entity2_id ' . 
           'FROM link l1 LEFT JOIN link l2 ON (l1.entity2_id = l2.entity1_id) ' .
           'WHERE l1.entity1_id = ? AND l2.entity2_id <> ?';
    $params = array($id2, $id2);

    if ($catIds = @$options['cat_ids'])
    {
      if (count(explode(',', $catIds)) == 1)
      {
        $sql .= ' AND l1.category_id = ? AND l2.category_id = l1.category_id';
        $params[] = $catIds;
      }
      else
      {
        $sql .= ' AND l1.category_id IN (' . $catIds . ') AND l2.category_id = l1.category_id';
      }
    }

    $stmt = $db->execute($sql, $params);
    $network2 = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);
    $firsts2 = array_keys($network2);

    //second degree ids with the first degree ids they're connected via
    $seconds2 = array();    
    
    foreach ($network2 as $first => $seconds)
    {
      foreach (array_unique($seconds) as $second)
      {
        if (isset($seconds2[$second]))
        {
          $seconds2[$second][] = $first;
        }
        else
        {
          $seconds2[$second] = array($first);
        }
      }
    }    


    //look for third degree connection
    $seconds = array_intersect(array_keys($seconds1), $firsts2);    

    if ($seconds)
    {
      foreach ($seconds as $second)
      {
        $firsts = $seconds1[$second];
        
        foreach ($firsts as $first)
        {
          $chains[3][] = array($id1, $first, $second, $id2);
        }
      }
    }
    
    
    //look for fourth degree connection
    $seconds = array_intersect(array_keys($seconds1), array_keys($seconds2));

    if ($seconds)
    {
      foreach ($seconds as $second)
      {
        foreach ($seconds1[$second] as $first)
        {
          foreach ($seconds2[$second] as $third)
          {
            $chains[4][] = array($id1, $first, $second, $third, $id2);
          }
        }
      }
    }

    return $chains;
  }
  
  
  static function buildRelationshipChain($ids, $categoryIds=null)
  {
    $chain = array();

    foreach ($ids as $num => $id)
    {
      if ($num == 0)
      {
        $chain[$id] = array();
      }
      else
      {
        $chain[$id] = LinkTable::getRelationshipIdsBetween($ids[$num - 1], $id, $categoryIds);
      }
    }

    return $chain;
  }
}
