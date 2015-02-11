<?php 

class EntityApi
{
  static function get($id, $includeDeleted=false)
  {
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT ' . LsApi::generateSelectQuery(array('e' => 'Entity')) . ' FROM entity e WHERE e.id = ? AND e.is_deleted ';
    $sql .= $includeDeleted ? 'IS NOT NULL' : '= 0';
    $stmt = $db->execute($sql, array($id));
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }


  static function getDetails($id)
  {
    $entity = array();
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT ed.name FROM extension_definition ed ' .
           'LEFT JOIN extension_record er ON (er.definition_id = ed.id) ' .
           'WHERE er.entity_id = ?';
           
    $stmt = $db->execute($sql, array($id));
    $extAry = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $entity['types'] = implode(',', $extAry);
    $extsWithFields = array_intersect($extAry, ExtensionDefinitionTable::$extensionNamesWithFields);

    //get fields and values for each extension
    foreach ($extsWithFields as $ext)
    {
      $sql = 'SELECT * FROM ' . Doctrine_Inflector::tableize($ext) . ' WHERE entity_id = ?';      
      $stmt = $db->execute($sql, array($id));

      $extData = $stmt->fetch(PDO::FETCH_ASSOC);
      unset($extData['id'], $extData['entity_id']);

      $entity = array_merge($entity, $extData);
    }
    
    return $entity;
  }


  static function getAliases($id, $includePrimary=true)
  {
    $db = Doctrine_Manager::connection();    
    $sql = 'SELECT a.name FROM alias a WHERE a.entity_id = ? AND a.context IS NULL';
    $params = array($id);
    
    if (!$includePrimary)
    {
      $sql .= ' AND a.is_primary = ?';
      $params[] = false;
    }
    
    $stmt = $db->execute($sql, $params);

    return $stmt->fetchAll(PDO::FETCH_COLUMN);    
  }


  static function getRelationships($id, $options=array())
  {
    $select = LsApi::generateSelectQuery(array('r' => 'Relationship'));
    $from = 'relationship r USE INDEX(entity1_id_idx, entity2_id_idx)';
    $where = 'r.is_deleted = 0';

    //limit by entity order
    if (@$options['order'])
    {
      $where .= ' AND entity' . $options['order'] . '_id = ?';
      $params = array($id);
    }
    else
    {
      $where .= ' AND (entity1_id = ? OR entity2_id = ?)';
      $params = array($id, $id);
    }
    
    //limit by category_id
    if (@$options['cat_ids'])
    {
      $catIds = explode(',', $options['cat_ids']);
      
      if (count($catIds) == 1)
      {
        $where .= ' AND r.category_id = ?';
        $params[] = $catIds[0];
      }
      elseif (count($catIds) > 1)
      {
        $where .=  ' AND r.category_id IN (' . $options['cat_ids'] . ')';
      }
    }
    
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where;
    $stmt = $db->execute($sql, $params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  static function getPersonDonationData($id, $start_cycle, $end_cycle)
  {
    $db = Doctrine_Manager::connection();
    
    $select = "SELECT e.id as donor_id, e.name as donor_name, e.primary_ext as donor_ext, " .
              "e2.id as recipient_id, e2.name as recipient_name, e2.primary_ext as recipient_ext, " .
              "f.amount as amt, f.crp_cycle, p2.party_id ";
    
    $from = "FROM entity e LEFT JOIN relationship r ON r.entity1_id = e.id " . 
            "LEFT JOIN fec_filing f ON f.relationship_id = r.id " . 
            "LEFT JOIN entity e2 ON e2.id = r.entity2_id " . 
            "LEFT JOIN person p2 ON p2.entity_id = e2.id ";
    
    $where = "WHERE r.category_id = 5 AND r.is_deleted=0 AND f.crp_cycle IS NOT NULL " . 
             "AND e.id = ? AND f.crp_cycle >= ? AND f.crp_cycle <= ? ";
    
    $sql = $select . $from . $where . "GROUP BY f.id";
    $stmt = $db->execute($sql, array($id, $start_cycle, $end_cycle));

    return $stmt->fetchAll();
  }

  static function getOrgDonationData($id, $start_cycle, $end_cycle)
  {
    $db = Doctrine_Manager::connection();

    $select = "SELECT e.id as donor_id, e.name as donor_name, e.primary_ext as donor_ext, " .
              "e2.id as recipient_id, e2.name as recipient_name, e2.primary_ext as recipient_ext, " .
              "r2.is_current, p.is_board, p.is_executive, f.amount as amt, f.crp_cycle, p2.party_id ";
    
    $from = "FROM relationship r LEFT JOIN relationship r2 ON r2.entity1_id = r.entity1_id " . 
            "LEFT JOIN entity e ON e.id = r.entity1_id " . 
            "LEFT JOIN fec_filing f ON f.relationship_id = r.id " . 
            "LEFT JOIN position p ON p.relationship_id = r2.id " . 
            "LEFT JOIN entity e2 ON e2.id = r.entity2_id " . 
            "LEFT JOIN person p2 ON p2.entity_id = e2.id ";
    
    $where = "WHERE r.category_id = 5 AND r2.category_id IN (1,3) AND r.is_deleted=0 AND r2.is_deleted=0 " . 
             "AND r2.entity2_id = ? AND f.crp_cycle IS NOT NULL AND f.crp_cycle >= ? AND f.crp_cycle <= ? ";
    
    $sql = $select . $from . $where . 'GROUP BY f.id';
    $stmt = $db->execute($sql, array($id, $start_cycle, $end_cycle));

    return $stmt->fetchAll();
  }

  static function getOrgPartyMap($org_ids)
  {
    $map = array();

    if (count($org_ids))
    {
      $db = Doctrine_Manager::connection();
      $sql = "SELECT entity_id, fec_id FROM political_fundraising WHERE entity_id IN(" . join(',', $org_ids) . ")";
      $stmt = $db->execute($sql);
      $entity_fec_map = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

      if (count($entity_fec_map))
      {
        $databaseManager = sfContext::getInstance()->getDatabaseManager();
        $rawDb = $databaseManager->getDatabase('raw');
        $rawDb = Doctrine_Manager::connection($rawDb->getParameter('dsn'));  
        $sql = "SELECT committee_id, party FROM os_committee " . 
               "WHERE committee_id IN(" . join(',', array_fill(0, count($entity_fec_map), '?')) . ")";

        $stmt = $rawDb->execute($sql, array_values($entity_fec_map));
        $fec_party_map = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $db = Doctrine_Manager::connection($databaseManager->getDatabase('main')->getParameter('dsn'));

        foreach ($org_ids as $org_id)
        {
          $map[$org_id] = @$fec_party_map[$entity_fec_map[$org_id]];
        }
      }
    }

    return $map;
  }

  static function getDonationSummary($id, $options=array())
  {
    $blank = array(
      'person_recipients' => array(),
      'org_recipients' => array(),
      'donors' => array(),
      'rep_amts' => array(),
      'dem_amts' => array(),
      'other_amts' => array(),
      'total' => 0,
      'rep_total' => 0,
      'dem_total' => 0,
      'other_total' => 0,
      'cycles' => array()
    );

    if (!$start_cycle = @$options['start_cycle']) $start_cycle = '1990';
    if (!$end_cycle = @$options['end_cycle']) $end_cycle = '2012';

    if (!$ext = @$options['type'])
    {
      $db = Doctrine_Manager::connection();
      $sql = "SELECT primary_ext FROM entity WHERE id = ?";
      $stmt = $db->execute($sql, array($id));
      $ext = $stmt->fetch(PDO::FETCH_COLUMN);
    }

    if ($ext == 'Person')
    {
      $donations = self::getPersonDonationData($id, $start_cycle, $end_cycle);
    }
    else if ($ext == 'Org')
    {
      $donations = self::getOrgDonationData($id, $start_cycle, $end_cycle);
    }
    else
    {
      return $blank;
    }

    if (count($donations) == 0)
    {
      return $blank;
    }

    $cycles = array_map('strval', range(1990, 2020, 2));
    $filter = function($ary) use($start_cycle, $end_cycle) { return $ary >= $start_cycle && $ary <= $end_cycle; };
    $cycles = array_filter($cycles, $filter);
    $cycleAmts = $repAmts = $demAmts = $otherAmts = array_fill_keys($cycles, 0);
    $recipients = array();
    $donors = array();
    $total = 0;
    $repTotal = 0;
    $demTotal = 0;
    $otherTotal = 0;
    $numDonations = 0;
    $parties = array(12886 => 'D', 12901 => 'R');
    $party = null;
    $entity_fec_map = array();
    $fec_party_map = array();

    $org_donations = array_filter($donations, function($d) { return $d['recipient_ext'] == 'Org'; });
    $org_ids = array_map(function($d) { return $d['recipient_id']; }, $org_donations);
    $org_party_map = self::getOrgPartyMap($org_ids);

    foreach ($donations as $d)
    {
      if ($d['recipient_ext'] == 'Person')
      {
        $party = @$parties[$d['party_id']];
      }
      else
      {
        $party = @$org_party_map[$d['recipient_id']];
      }

      $amt = intval($d['amt']);
      if ($amt > 0) $numDonations++;

      $recipientType = strtolower($d['recipient_ext']) . 'Data';
      
      if (!isset($recipients[$recipientType][$d['recipient_id']]))
      {
        $url = EntityTable::getUri(array('id' => $d['recipient_id'], 'name' => $d['recipient_name'], 'primary_ext' => $d['recipient_ext']));
        $recipients[$recipientType][$d['recipient_id']] = array(
          'name' => $d['recipient_name'], 
          'url' => $url, 
          'amount' => $amt, 
          'donor_count' => 1,
          'party' => $party,
          'donor_ids' =>array($d['donor_id'])
        );
      }
      else
      {
        $recipients[$recipientType][$d['recipient_id']]['amount'] += $amt; 
        
        if(!in_array($d['donor_id'], $recipients[$recipientType][$d['recipient_id']]['donor_ids']))
        {
          $recipients[$recipientType][$d['recipient_id']]['donor_ids'][] = $d['donor_id'];
          $recipients[$recipientType][$d['recipient_id']]['donor_count']++;
        }
      }

      if (!isset($donors[$d['donor_id']]))
      {
        $url = EntityTable::getUri(array('id' => $d['donor_id'], 'name' => $d['donor_name'], 'primary_ext' => $d['donor_ext']));
        $donors[$d['donor_id']] = array(
          'name' => $d['donor_name'],
          'url' => $url, 
          'amount' => $amt,
          'recipient_count' => 1,
          'recipient_ids' => array($d['recipient_id'])
        );
      }
      else
      {
        $donors[$d['donor_id']]['amount'] += $amt; 

        if(!in_array($d['recipient_id'],$donors[$d['donor_id']]['recipient_ids']))
        {
          $donors[$d['donor_id']]['recipient_ids'][] = $d['recipient_id'];
          $donors[$d['donor_id']]['recipient_count']++;
        }
      }

      $cycleAmts[$d['crp_cycle']] += $amt;
      $total += $amt;

      if ($party == 'D')
      {
        $demAmts[$d['crp_cycle']] += $amt;
        $demTotal += $amt;
      }
      else if ($party == 'R')
      {
        $repAmts[$d['crp_cycle']] += $amt;
        $repTotal += $amt;
      }
      else
      {
        $otherAmts[$d['crp_cycle']] += $amt;
        $otherTotal += $amt;
      }
    }

    $personRecipients = $recipients['personData'];
    $orgRecipients = $recipients['orgData'];
    $amountCompareDesc = function($a, $b) { return $b['amount'] - $a['amount']; };    
    uasort($personRecipients, $amountCompareDesc);
    uasort($orgRecipients, $amountCompareDesc);   
    uasort($donors, $amountCompareDesc);

    if ($numDonations > 0)
    {
      $avgDonation = $total/$numDonations;
    }

    if ($repTotal > 0 || $demTotal > 0)
    {
      $demPct = LsNumber::makeReadable(($demTotal/($repTotal+$demTotal)) * 100, null, 1, "%");
      $repPct = LsNumber::makeReadable(($repTotal/($repTotal+$demTotal)) * 100, null, 1, "%");        
    }

    return array(
      'person_recipients' => $personRecipients,
      'org_recipients' => $orgRecipients,
      'donors' => $donors,
      'rep_amts' => $repAmts,
      'dem_amts' => $demAmts,
      'other_amts' => $otherAmts,
      'total' => $total,
      'rep_total' => $repTotal,
      'dem_total' => $demTotal,
      'other_total' => $otherTotal,
      'cycles' => $cycles
    );
  }

  static function getRelated($id, $options=array())
  {
    $db = Doctrine_Manager::connection();
    $selectTables = array('r' => 'Relationship', 'p' => 'Position', 'e1' => 'Entity', 'e2' => 'Entity');
    $select = LsApi::generateSelectQuery($selectTables);
    $select .= ', GROUP_CONCAT(DISTINCT ed1.name) AS exts1, GROUP_CONCAT(DISTINCT ed2.name) AS exts2';
    $from = 'relationship r LEFT JOIN entity e1 ON (r.entity1_id = e1.id) LEFT JOIN entity e2 ON (r.entity2_id = e2.id) ' .
            'LEFT JOIN extension_record er1 ON (er1.entity_id = e1.id) LEFT JOIN extension_record er2 ON (er2.entity_id = e2.id) ' .
            'LEFT JOIN extension_definition ed1 ON (ed1.id = er1.definition_id) ' .
            'LEFT JOIN extension_definition ed2 ON (ed2.id = er2.definition_id) ' . 
            'LEFT JOIN position p ON (p.relationship_id = r.id)';
    $where = 'r.is_deleted = 0 AND e1.is_deleted = 0 AND e2.is_deleted = 0';
    
    if ($o = @$options['order'])
    {
      $where = 'r.entity' . $o . '_id = ? AND ' . $where;
      $params = array($id);
    }
    else
    {
      $where = '(r.entity1_id = ? OR r.entity2_id = ?) AND ' . $where;
      $params = array($id, $id);
    }
    
    if ($catIds = @$options['cat_ids'])
    {
      if (!is_array($catIds))
      {
        $catIds = explode(',', $catIds);
      }
    }

    //limit by is_current
    if (isset($options['is_current']))
    {
      $where .= ' AND r.is_current = ?';
      $params[] = $options['is_current'];
    }
    
    $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where . ' GROUP BY r.id';
    $stmt = $db->execute($sql, $params);
    $rows = $stmt->fetchAll(PDO::FETCH_NUM);


    //prepare return containers
    $results = array();
    $rels = array();
    $relMap = array_merge(LsApi::$responseFields['Relationship'], LsApi::$responseFields['Position']);
    $entityMap = LsApi::$responseFields['Entity'];


    foreach ($rows as $row)
    {
      //divide numerically-indexed row into relevant parts
      $rel = array_combine(array_values($relMap), array_slice($row, 0, count($relMap)));

      //limit by category ids
      if ($catIds && !in_array($rel['category_id'], $catIds))
      {
        continue;
      }

      $relatedOrder = ($id == $rel['entity1_id']) ? 2 : 1;
      $relatedOffset = count($relMap) + (($relatedOrder == 2) ? count($entityMap) : 0);
      $relatedEntity = array_combine(array_values($entityMap), array_slice($row, $relatedOffset, count($entityMap)));
      $relatedEntity['types'] = $row[count($relMap) + 2*count($entityMap) + (($relatedOrder == 1) ? 0 : 1)];

      $thisOrder = ($relatedOrder % 2) + 1;
      $thisOffset = count($relMap) + (($thisOrder == 2) ? count($entityMap) : 0);
      $thisEntity = array_combine(array_values($entityMap), array_slice($row, $thisOffset, count($entityMap)));
      $thisEntity['types'] = $row[count($relMap) + 2*count($entityMap) + (($thisOrder == 1) ? 0 : 1)];
      

      if (@$options['sort'] == 'relationship')
      {
        $rel['Entity' . $relatedOrder] = $relatedEntity;
        $rel['Entity' . $thisOrder] = $thisEntity;
        $results[] = $rel;
      }
      elseif (@$options['sort'] == 'category')
      {
        $catId = $rel['category_id'];
        
        if (!isset($results[$catId]))
        {
          $results[$catId] = array();
        }
        
        if (isset($results[$catId][$relatedEntity['id']]))
        {
          $results[$catId][$relatedEntity['id']]['Relationships'][] = $rel;
        }
        else
        {
          $relatedEntity['Relationships'] = array($rel);
          $results[$catId][$relatedEntity['id']] = $relatedEntity;        
        }      
      }
      else
      {
        if (isset($results[$relatedEntity['id']]))
        {
          $results[$relatedEntity['id']]['Relationships'][] = $rel;
        }
        else
        {
          $relatedEntity['Relationships'] = array($rel);
          $results[$relatedEntity['id']] = $relatedEntity;
        }
      }
    }    


    if (@$options['sort'] == 'category')
    {
      foreach ($results as $cat => $ary)
      {
        uasort($ary, array('self', 'compareRelationshipCount'));
        $results[$cat] = $ary;
      }
    }
    elseif (@$options['sort'] != 'relationship')
    {
      uasort($results, array('self', 'compareRelationshipCount'));
    }  
    

    return $results;
  }


  static function getLeadership($id, $options=array())
  {
    $results = array();

    //first get board member entity IDs, then full data structure
    $db = Doctrine_Manager::connection();
    $params = array($id);
    $where = 'r.entity2_id = ? AND p.is_board = 1';

    if (isset($options['is_current']))
    {
      $where .= ' AND r.is_current = ?';
      $params[] = $options['is_current'];
    }

    $sql = 'SELECT DISTINCT entity1_id FROM relationship r LEFT JOIN position p ON (r.id = p.relationship_id) WHERE ' . $where;
    $stmt = $db->execute($sql, $params);
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!count($ids))
    {
      return array();
    }
    
    
    $selectTables = array('r' => 'Relationship', 'e1' => 'Entity');
    $select = LsApi::generateSelectQuery($selectTables);
    $from = 'relationship r LEFT JOIN entity e1 ON (e1.id = r.entity1_id)';
    $where = 'r.entity2_id = ? AND r.entity1_id IN (' . implode(',', $ids) . ') AND e1.is_deleted <> 1';
    $params = array($id);

    $db = Doctrine_Manager::connection();
    $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where;
    $stmt = $db->execute($sql, $params);

    $relMap = LsApi::$responseFields['Relationship'];
    $entityMap = LsApi::$responseFields['Entity'];

    foreach ($stmt->fetchAll(PDO::FETCH_NUM) as $row)
    {
      //divide numerically-indexed row into relevant parts
      $rel = array_combine(array_values($relMap), array_slice($row, 0, count($relMap)));
      $entity = array_combine(array_values($entityMap), array_slice($row, count($relMap)));
      
      if (isset($results[$entity['id']]))
      {
        $results[$entity['id']]['Relationships'][] = $rel;
      }
      else
      {
        $entity['Relationships'] = array($rel);
        $results[$entity['id']] = $entity;
      }
    }
    
    return $results;
  }


  static function getOrgs($id, $options)
  {
    $results = array();

    //first get board member entity IDs, then full data structure
    $db = Doctrine_Manager::connection();
    $params = array($id);
    $where = 'r.entity1_id = ? AND (p.is_board = 1 OR p.is_executive = 1)';

    if (isset($options['is_current']))
    {
      $where .= ' AND r.is_current = ?';
      $params[] = $options['is_current'];
    }

    $sql = 'SELECT DISTINCT entity2_id FROM relationship r LEFT JOIN position p ON (r.id = p.relationship_id) WHERE ' . $where;
    $stmt = $db->execute($sql, $params);
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!count($ids))
    {
      return array();
    }


    $selectTables = array('r' => 'Relationship', 'e2' => 'Entity');
    $select = LsApi::generateSelectQuery($selectTables);
    $select .= ', CONCAT(\',\', GROUP_CONCAT(DISTINCT ed.name), \',\') AS types';
    $from = 'relationship r ' . 
            'LEFT JOIN entity e2 ON (e2.id = r.entity2_id) ' . 
            'LEFT JOIN extension_record er ON (er.entity_id = e2.id) ' . 
            'LEFT JOIN extension_definition ed ON (ed.id = er.definition_id)';
    $where = 'r.entity1_id = ? AND r.entity2_id IN (' . implode(',', $ids) . ')';
    $params = array($id);

    $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where . ' GROUP BY r.id';

    //limit by type
    if (@$options['type'])
    {
      $sql .= ' HACING types LIKE ?';
      $params[] = '%,' . $options['type'] . ',%';
    }

    $db = Doctrine_Manager::connection();
    $stmt = $db->execute($sql, $params);

    $relMap = LsApi::$responseFields['Relationship'];
    $entityMap = LsApi::$responseFields['Entity'];

    foreach ($stmt->fetchAll(PDO::FETCH_NUM) as $row)
    {
      //divide numerically-indexed row into relevant parts
      $rel = array_combine(array_values($relMap), array_slice($row, 0, count($relMap)));
      $entity = array_combine(array_values($entityMap), array_slice($row, count($relMap), count($entityMap)));
      
      if (isset($results[$entity['id']]))
      {
        $results[$entity['id']]['Relationships'][] = $rel;
      }
      else
      {
        $entity['Relationships'] = array($rel);
        $results[$entity['id']] = $entity;
      }
    }
    
    return $results;
  }


  static function getLeadershipWithOrgs($id, $options=array())
  {
    $results = array();

    //first get board members and executives
    $db = Doctrine_Manager::connection();
    $params = array($id);
    $where = 'r.entity2_id = ? AND (p.is_board = 1 OR p.is_executive = 1)';

    if (isset($options['is_current']))
    {
      $where .= ' AND r.is_current = ?';
      $params[] = $options['is_current'];
    }

    $select = 'e.id, e.name, p2.gender_id, MAX(p.is_board) is_board, MAX(p.is_executive) is_executive, GROUP_CONCAT(DISTINCT r.description1) titles';
    $from = 'relationship r LEFT JOIN entity e ON (e.id = r.entity1_id) ' . 
            'LEFT JOIN position p ON (r.id = p.relationship_id) ' .
            'LEFT JOIN person p2 ON (p2.entity_id = e.id)';
    $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where . ' GROUP BY r.entity1_id';
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

    //now get the board members' companies
    $selectTables = array('e' => 'Entity');
    $select = 'e.id, e.name, r.entity1_id, MAX(p.is_board) is_board, MAX(p.is_executive) is_executive, GROUP_CONCAT(DISTINCT r.description1) titles';
    $from = 'relationship r LEFT JOIN entity e ON (e.id = r.entity2_id) ' .
            'LEFT JOIN position p ON (p.relationship_id = r.id)';
    $where = 'r.entity1_id IN (' . implode(', ', $entityIds) . ') AND r.entity2_id <> ? AND (p.is_board = 1 OR p.is_executive = 1)';
    $params = array($id); 

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


  static function getSecondDegreeNetwork($id, $options=array(), $countOnly=false)
  {
    $db = Doctrine_Manager::connection();

    //THIS SECTION IS DISABLED FOR NOW -- QUERY USING LINK TABLE BELOW
    if (FALSE && @$options['order1'] && @$options['order2'])
    {
      $order1 = $options['order1'];
      $order2 = $options['order2'];

      $select = 'r2.entity' . (3 - $order2) . '_id AS degree2_id, GROUP_CONCAT(DISTINCT r1.entity' . (3 - $order1) . '_id) AS degree1_ids, COUNT(DISTINCT r1.entity' . (3 - $order1) . '_id) AS num';
      $from = 'relationship r1 USE INDEX (entity1_id_idx, entity2_id_idx) ' .
              'LEFT JOIN relationship r2 USE INDEX (entity1_id_idx, entity2_id_idx) ' . 
              'ON (r1.entity' . (3 - $order1) . '_id = r2.entity' . $order2 . '_id)';
      $where = 'r1.entity' . $order1 . '_id = ? AND r2.entity' . (3 - $order2) . '_id <> ? AND r1.is_deleted = 0 AND r2.is_deleted = 0';
      $group = 'r2.entity' . (3 - $order2) . '_id';      
      $params = array($id, $id);

      //limit by cat1_ids
      if ($cat1Ids = @$options['cat1_ids'])
      {
        if (count(explode(',', $cat1Ids)) == 1)
        {
          $where .= ' AND r1.category_id = ?';
          $params[] = $cat1Ids;
        }
        else
        {
          $where .= ' AND r1.category_id IN (' . $cat1Ids . ')';
        }
      }
  
      //limit by cat2_ids
      if ($cat2Ids = @$options['cat2_ids'])
      {
        if (count(explode(',', $cat2Ids)) == 1)
        {
          $where .= ' AND r2.category_id = ?';
          $params[] = $cat2Ids;
        }
        else
        {
          $where .= ' AND r2.category_id IN (' . $cat2Ids . ')';
        }
      }


      if ($countOnly)
      {
        $sql = 'SELECT COUNT(DISTINCT r2.entity' . (3 - $order2) . '_id) FROM ' . $from . ' WHERE ' . $where;
      }
      else
      {
        $paging = LsApi::getPagingFromOptions($options, $defaultNum=20, $maxNum=100);      
        $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where . ' GROUP BY ' . $group . ' ORDER BY num DESC ' . $paging;
      }

      $stmt = $db->execute($sql, $params);


      if ($countOnly)
      {
        return $stmt->fetchColumn();
      }
      else
      {
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      }      
    }      
    else
    {
      $select = 'l2.entity2_id AS degree2_id, GROUP_CONCAT(DISTINCT l2.entity1_id) AS degree1_ids, COUNT(DISTINCT l2.entity1_id) AS num';
      $from = 'link l1 LEFT JOIN link l2 ON (l2.entity1_id = l1.entity2_id)';
      $where = 'l1.entity1_id = ? AND l2.entity2_id <> ?';

      if ($mapId = $options['map_id']) 
      {
        $sql = 'SELECT * FROM network_map WHERE id = ? AND is_deleted = 0';
        $stmt = $db->execute($sql, array($mapId));
        $map = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($map) 
        {
          $entityIds = explode(',', $map['entity_ids']);

          if (count($entityIds) > 0)
          {
            $isInt = function($int) {
              return ($int + 0 > 0);
            };

            $entityIds = array_filter($entityIds, $isInt);
            $where .= ' AND l2.entity2_id IN (' . implode(',', $entityIds) . ')';
          }
        }
      }

      $group = 'l2.entity2_id';
      $params = array($id, $id);

      //limit by relationship order, if specified
      if (@$order1 = $options['order1'])
      {
        $isReverse = ($order1 == 2);
        $where .= ' AND l1.is_reverse = ' . (int) $isReverse;
      }

      if (@$order2 = $options['order2'])
      {
        $isReverse = ($order2 == 2);
        $where .= ' AND l2.is_reverse = ' . (int) $isReverse;
      }
  
      //limit by cat1_ids
      if ($cat1Ids = @$options['cat1_ids'])
      {
        if (count(explode(',', $cat1Ids)) == 1)
        {
          $where .= ' AND l1.category_id = ?';
          $params[] = $cat1Ids;
        }
        else
        {
          $where .= ' AND l1.category_id IN (' . $cat1Ids . ')';
        }
      } 
			      
      //limit by cat2_ids
      if ($cat2Ids = @$options['cat2_ids'])
      {
        if (count(explode(',', $cat2Ids)) == 1)
        {
          $where .= ' AND l2.category_id = ?';
          $params[] = $cat2Ids;
        }
        else
        {
          $where .= ' AND l2.category_id IN (' . $cat2Ids . ')';
        }
      }
      
      //limit by ext2_ids
      if ($ext2Ids = @$options['ext2_ids'])
      {
        $from .= ' LEFT JOIN entity e on e.id = l2.entity2_id LEFT JOIN extension_record er on er.entity_id = e.id';
        if (count(explode(',', $ext2Ids)) == 1)
        {
          $where .= ' AND er.definition_id = ?';
          $params[] = $ext2Ids;
        }
        else
        {
          $where .= ' AND er.definition_id IN (' . $ext2Ids . ')';
        }
      }
      
      if ($past1 = @$options['past1'])
      {
        $from .= ' LEFT JOIN relationship r1 on r1.id = l1.relationship_id';
        $where .= ' AND (r1.is_current = 1 OR r1.is_current IS NULL) AND r1.end_date is null';
      }

            
      if ($past2 = @$options['past2'])
      {
        $from .= ' LEFT JOIN relationship r2 on r2.id = l2.relationship_id';
        $where .= ' AND (r2.is_current = 1 OR r2.is_current IS NULL) AND r2.end_date is null';
      }
      
      if ($countOnly)
      {
        $sql = 'SELECT COUNT(DISTINCT l2.entity2_id) FROM ' . $from . ' WHERE ' . $where;
      }
      else
      {
        $paging = LsApi::getPagingFromOptions($options, $defaultNum=20, $maxNum=100);      
        $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where . ' GROUP BY ' . $group . ' ORDER BY num DESC ' . $paging;
      }
      
      $stmt = $db->execute($sql, $params);


      if ($countOnly)
      {
        return $stmt->fetchColumn();
      }
      else
      {
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      }
    }


    $entities = array();

    foreach ($rows as $row)
    {
      $entity = self::get($row['degree2_id']);
      $entity['degree1_num'] = $row['num'];
      $entity['degree1_ids'] = $row['degree1_ids'];
      $entities[] = $entity;
    }

    return $entities;


    /* OTHER USABLE CODE

    //first get ids of all directly related entities, limiting by order or category
    $from = 'relationship r USE INDEX (entity1_id_idx, entity2_id_idx)';
    
    //limit by order1
    if (in_array($order = (int) @$options['order1'], array(1, 2)))
    {
      $select = 'entity' . (3 - $order) . '_id';
      $where = 'entity' . $order . '_id = ?';
      $params = array($id);
    }
    else
    {
      $select = 'IF(r.entity1_id = ?, r.entity2_id, r.entity1_id) AS related_id';
      $where = '(entity1_id = ? OR entity2_id = ?)';
      $params = array($id, $id, $id);
    }
    
    //limit by cat1_ids
    if (@$options['cat1_ids'])
    {
      if (count(explode(',', $options['cat1_ids'])) == 1)
      {
        $where .= ' AND category_id = ?';
        $params[] = $options['cat1_ids'];
      }
      else
      {
        $where .= ' AND category_id IN (' . $options['cat1_ids'] . ')';
      }
    }
    
    
    $sql = 'SELECT DISTINCT ' . $select . ' FROM ' . $from . ' WHERE ' . $where;
    $stmt = $db->execute($sql, $params);
    $relatedIds = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_UNIQUE, 0);    

    $results = array();


    foreach ($relatedIds as $relatedId)
    {
      $select = LsApi::generateSelectQuery(array('e1' => 'Entity', 'e2' => 'Entity'));
      $from = 'relationship r USE INDEX (entity1_id_idx, entity2_id_idx) ' .
              'LEFT JOIN entity e1 ON e1.id = r.entity1_id ' . 
              'LEFT JOIN entity e2 ON e2.id = r.entity2_id';              

      //limit by order2
      if (in_array($order = (int) @$options['order2'], array(1, 2)))
      {
        $where = 'entity' . $order . '_id = ?';
        $params = array($relatedId);
      }
      else
      {
        $where = '(entity1_id = ? OR entity2_id = ?)';
        $params = array($relatedId, $relatedId);
      }
      
      //limit by cat2_ids
      if ($cat2Ids = @$options['cat2_ids'])
      {
        if (count(explode(',', $cat2Ids)) == 1)
        {
          $where .= ' AND category_id = ?';
          $params[] = $cat2Ids;
        }
        else
        {
          $where .= ' AND category_id IN (' . $cat2Ids . ')';
        }
      }

      
      $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where;
      $stmt = $db->execute($sql, $params);
      $rows = $stmt->fetchAll(PDO::FETCH_NUM);

      $entityMap = LsApi::$responseFields['Entity'];
      
      foreach ($rows as $row)
      {
        //skip if relationship with original entity
        if (($row[0] == $id) || ($row[count($entityMap)] == $id))
        {
          continue;
        }

        //divide numerically-indexed row into relevant parts
        $entity1 = array_combine(array_values($entityMap), array_slice($row, 0, count($entityMap)));
        $entity2 = array_combine(array_values($entityMap), array_slice($row, count($entityMap), count($entityMap)));
  
        list($degree1, $degree2) = in_array($entity1['id'], $relatedIds) ? array($entity1, $entity2) : array($entity2, $entity1);

        //populate results array
        if (isset($results[$degree2['id']]))
        {
          $results[$degree2['id']]['Degree1'][$degree1['id']] = $degree1;
        }
        else
        {
          $degree2['Degree1'] = array($degree1['id'] => $degree1);
          $results[$degree2['id']] = $degree2;
        }
      }
    }
    
    return $results;
    
    OTHER USABLE CODE */
  }


  static function getSecondDegreeNetworkFromFirstDegrees($id, $degree1_ids, $options=array(), $countOnly=false)
  {
    $select = 'l.entity2_id AS degree2_id, GROUP_CONCAT(DISTINCT l.entity1_id) AS degree1_ids, COUNT(DISTINCT l.entity1_id) AS num';
    $from = 'link l';
    $where = 'l.entity1_id IN (' . $degree1_ids . ') AND l.entity2_id <> ?';
    $group = 'l.entity2_id';
    $params = array($id, $id);

    //limit by relationship order, if specified
    if (@$order = $options['order'])
    {
      $isReverse = ($order == 2);
      $where .= ' AND l.is_reverse = ' . (int) $isReverse;
    }

    //limit by cat1_ids
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

    if ($countOnly)
    {
      $sql = 'SELECT COUNT(DISTINCT l.entity2_id) FROM ' . $from . ' WHERE ' . $where;
    }
    else
    {
      $paging = LsApi::getPagingFromOptions($options, $defaultNum=20, $maxNum=100);      
      $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where . ' GROUP BY ' . $group . ' ORDER BY num DESC ' . $paging;
    }
    
    $stmt = $db->execute($sql, $params);


    if ($countOnly)
    {
      return $stmt->fetchColumn();
    }
    else
    {
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    $entities = array();

    foreach ($rows as $row)
    {
      $entity = self::get($row['degree2_id']);
      $entity['degree1_num'] = $row['num'];
      $entity['degree1_ids'] = $row['degree1_ids'];
      $entities[] = $entity;
    }

    return $entities;  
  }



  static function getLists($id)
  {
    $db = Doctrine_Manager::connection();
    $select = LsApi::generateSelectQuery(array('l' => 'LsList'));
    $from = 'ls_list l LEFT JOIN ls_list_entity le ON (le.list_id = l.id)';
    $where = 'le.entity_id = ? AND l.is_admin = ?';
    $params = array($id, false);
    $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where;
    $stmt = $db->execute($sql, $params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  
  static function getReferences($id)
  {
    $db = Doctrine_Manager::connection();
    $select = LsApi::generateSelectQuery(array('r' => 'Reference'));
    $sql = 'SELECT ' . $select . ' FROM reference r WHERE r.object_model = ? AND r.object_id = ?';
    $stmt = $db->execute($sql, array('Entity', $id));
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);  
  }


  static function getRelationshipReferences($id, $options=array())
  {
    $db = Doctrine_Manager::connection();

    $rels = self::getRelationships($id, $options);
    $relIds = array();
    $ret = array();

    foreach ($rels as $rel)
    {
      $ret[$rel['id']] = $rel;
    }

    $select = LsApi::generateSelectQuery(array('r' => 'Reference')) . ', r.object_id AS relationship_id';
    $sql = 'SELECT ' . $select . ' FROM reference r WHERE object_model = ? AND object_id IN (' . implode(',', array_keys($ret)) . ')';
    $stmt = $db->execute($sql, array('Relationship'));
    $refs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($refs as $ref)
    {
      $relId = $ref['relationship_id'];
      unset($ref['relationship_id']);
      
      if (isset($ret[$relId]['References']))
      {
        $ret[$relId]['References'][] = $ref;
      }
      else
      {
        $ret[$relId]['References'] = array($ref);
      }    
    }

    
    return $ret;


    $db = Doctrine_Manager::connection();
    $select = LsApi::generateSelectQuery(array('r' => 'Relationship', 'ref' => 'Reference'));
    $from = 'relationship r LEFT JOIN reference ref ON (ref.object_model = ? AND ref.object_id = r.id)';
    $where = '(r.entity1_id = ? OR r.entity2_id = ?) AND r.is_deleted = 0';
    $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where;
    $stmt = $db->execute($sql, array('Relationship', $id, $id));
    $rows = $stmt->fetchAll(PDO::FETCH_NUM);  
    $relMap = LsApi::$responseFields['Relationship'];
    $refMap = LsApi::$responseFields['Reference'];
    
    $refs = array();
    
    foreach ($rows as $row)
    {
      $rel = array_combine(array_keys($relMap), array_slice($row, 0, count($relMap)));
      $ref = array_combine(array_keys($refMap), array_slice($row, count($relMap)));      
      $ref['Relationship'] = $rel;
      $refs[] = $ref;
    }
    
    return $refs;
  }
  
  
  static function getChildOrgs($id)
  {
    $db = Doctrine_Manager::connection();
    $select = LsApi::generateSelectQuery(array('e' => 'Entity'));
    $sql = 'SELECT ' . $select . ' FROM entity e WHERE e.parent_id = ?';
    $stmt = $db->execute($sql, array($id));
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  
  static function getImages($id, $options=array())
  {
    $db = Doctrine_Manager::connection();
    $select = 'id, title, caption, is_featured, filename, url';
    $sql = 'SELECT ' . $select . ' FROM image i WHERE i.entity_id = ? AND i.is_deleted = 0';
    $stmt = $db->execute($sql, array($id));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);  
    $images = array();
    $size = in_array(@$options['size'], array('small', 'profile', 'large')) ? $options['size'] : 'profile';
    
    foreach ($rows as $row)
    {
      if (sfConfig::get('app_amazon_enable_s3_assets')) 
      {
        $base = sfConfig::get('app_amazon_s3_base') . '/' . sfConfig::get('app_amazon_s3_bucket');
      }
      else
      {
        $base = 'http://littlesis.org';
      }

      $row['uri'] = $base . '/images/' . $size . '/' . $row['filename'];
      $row['source'] = $row['url'];
      unset($row['url']);
      unset($row['filename']);
      
      $images[] = $row;
    }

    return $images;
  }
  
  
  static function getImage($id, $options=array())
  {
    $db = Doctrine_Manager::connection();
    $select = 'id, title, caption, is_featured, filename, url';
    $sql = 'SELECT ' . $select . ' FROM image i WHERE i.entity_id = ? AND i.is_featured = ? AND i.is_deleted = 0';
    $stmt = $db->execute($sql, array($id, true));

    if ($image = $stmt->fetch(PDO::FETCH_ASSOC))
    {
      if ($image['source'])
      {
        return $image['source'];
      }
      else
      {
        $size = in_array(@$options['size'], array('small', 'profile', 'large')) ? $options['size'] : 'profile';
        return 'http://littlesis.org/images/' . $size . '/' . $image['filename'];
      }
    }
    
    return null;
  }


  static function compareRelationshipCount($ary1, $ary2)
  {
    $diff = count($ary2['Relationships']) - count($ary1['Relationships']);

    if ($diff == 0)
    {
      return 0;
    }
    elseif ($diff > 0)
    {
      return 1;
    }
    else
    {
      return -1;
    }
  }
  
  
  static function getUri($id, $format='xml')
  {
    return 'http://api.littlesis.org/entity/' . $id . '.' . $format;
  }

  
  static function addUris($entity)
  {
    if ($entity['id'] && $entity['primary_type'] && $entity['name'])
    {
      $entity['uri'] = EntityTable::getUri($entity);    
      $entity['api_uri'] = self::getUri($entity['id']);
    }
    else
    {
      $entity['uri'] = null;
      $entity['api_uri'] = null;
    }

    return $entity;
  }


  static function getFields($id)
  {
    $db = Doctrine_Manager::connection();
    $sql = "SELECT f.name, ef.value FROM entity_fields ef LEFT JOIN fields f on (f.id = ef.field_id) " . 
           "WHERE ef.entity_id = ? AND ef.is_admin = false";
    $stmt = $db->execute($sql, array($id));
    $fields = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    return $fields;
  }
}