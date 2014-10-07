<?php

class entityComponents extends sfComponents
{
  public function getEntityAndImage($db)
  {
    $select = 'e.*, i.filename AS image_path';
    $from = 'e LEFT JOIN image i ON (i.entity_id = e.id and i.is_featured = 1)';
    $where = 'e.id = ? AND e.is_deleted = 0';
    $sql = 'SELECT ' . $select . ' FROM entity ' . $from . ' WHERE ' . $where;
    $stmt = $db->execute($sql, array($this->id));
    $this->entity = $stmt->fetch(PDO::FETCH_ASSOC);  
  }

  public function getRelatedsAndCount($db, $primary_ext=null)
  {
    $params = array($this->id);
    $select = LsApi::generateSelectQuery(array('e' => 'Entity')) . ', COUNT(l.id) AS num';
    $from = 'link l LEFT JOIN entity e ON (l.entity2_id = e.id)';
    $where = 'l.entity1_id = ?';

    if ($primary_ext) { $where .= ' AND e.primary_ext = ?'; array_push($params, $primary_ext); }

    $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where . ' GROUP BY l.entity2_id ORDER BY num DESC';
    $stmt = $db->execute($sql, $params);

    $this->relationship_count = 0;
    $this->relateds = array();

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row)
    {
      $this->relationship_count += $row['num'];
      $this->relateds[] = $row;
    }  
  }

  public function getConnections($db, $num=5, $primary_ext=null)
  {
    $params = array($this->id);
    $select = LsApi::generateSelectQuery(array('e' => 'Entity')) . ', COUNT(l.id) AS num';
    $from = 'link l LEFT JOIN entity e ON (l.entity2_id = e.id)';
    $where = 'l.entity1_id = ?';

    if ($primary_ext) 
    { 
      $where .= ' AND e.primary_ext = ?'; 
      array_push($params, $primary_ext); 
    }

    $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where . ' GROUP BY l.entity2_id ORDER BY num DESC LIMIT ' . $num;
    $stmt = $db->execute($sql, $params);
    $this->connections = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getInterlocks($num=10)
  {
    $order1 = ($this->entity['primary_ext'] == 'Person') ? 1 : 2;
    $order2 = ($this->entity['primary_ext'] == 'Person') ? 2 : 1;

    $options = array(
      'cat1_ids' => RelationshipTable::POSITION_CATEGORY . ',' . RelationshipTable::MEMBERSHIP_CATEGORY,
      'order1' => $order1,
      'cat2_ids' => RelationshipTable::POSITION_CATEGORY . ',' . RelationshipTable::MEMBERSHIP_CATEGORY,
      'order2' => $order2,
      'page' => 1,
      'num' => $num
    );
    
    $this->interlocks = EntityApi::getSecondDegreeNetwork($this->entity['id'], $options);      
  }

  public function executeMini()
  {
    $db = Doctrine_Manager::connection();

    //get entity and image
    $this->getEntityAndImage($db);

    //get related entities and relationship count
    $this->getRelatedsAndCount($db);
          
    $this->sample_relateds = array_slice($this->relateds, 0, 3);
  }


  public function executeCarousel()
  {
    $db = Doctrine_Manager::connection();

    //get entity and image
    $this->getEntityAndImage($db);

    //get direct connections
    $primary_ext = $this->entity['primary_ext'] == 'Person' ? 'Org' : 'Person';
    $this->getConnections($db, 5, $primary_ext);
        
    //get interlocks
    $this->getInterlocks(5);
  }
  

  public function executeSimilarEntities()
  {
    $db = Doctrine_Manager::connection();
    
    if ($this->entity['primary_ext'] == 'Person')
    {
      $this->similar_entities = EntityTable::getSimilarEntitiesQuery($this->entity, $looseMatch=true)
        ->setHydrationMode(Doctrine::HYDRATE_ARRAY)
        ->limit(5)
        ->execute();  
    }
    else
    {
      $name = OrgTable::stripName($this->entity['name']);
      $terms = preg_split('#\s+#', $name);

      $filtered_terms = array();

      foreach ($terms as $term)
      {
        if (strlen($term) > 1 && strpos($term, '&') === false)
        {
          $filtered_terms[] = $term;
        }
      }

      $filtered_terms = array_map(array('LsSphinxClient', 'cleanForQuery'), $filtered_terms);
      $filtered_terms = array_slice($filtered_terms, 0, 2);


      $this->similar_entities = array();

      if (count($filtered_terms))
      {
        $similar = EntityTable::getSphinxPager(join(' ', $filtered_terms), $page=1, $num=3, $listIds=null, $aliases=true, $primary_ext="Org")->execute();

        foreach ($similar as $entity)
        {
          if ($entity['id'] != $this->entity['id'])
          {
            $this->similar_entities[] = $entity;
          }
        }
      }
    }      
  }


  public function executeWatchers()
  {
    if ($this->getUser()->hasCredential('editor'))
    {
      $this->watchers = $this->entity->getWatchersQuery()->limit(6)->execute();
    }
  }
  
  
  public function executePossibleDuplicates()
  {
    if ($this->entity['primary_ext'] == 'Person')
    {
      $db = Doctrine_Manager::connection();
      $sql = 'SELECT e.* FROM person p LEFT JOIN entity e ON (e.id = p.entity_id) WHERE p.name_first = ? AND p.name_last = ? AND e.id <> ? AND e.is_deleted = 0';
      $params = array($this->entity['name_first'], $this->entity['name_last'], $this->entity['id']);
      
      if ($this->entity['name_middle'])
      {
        $sql .= ' AND (p.name_middle IS NULL OR p.name_middle LIKE ?)';
        $params[] = $this->entity['name_middle'] . '%';
      }
      
      $stmt = $db->execute($sql, $params);
      $this->possible_duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }  
  }


  public function executeRelationships()
  {
    $this->rels = EntityApi::getRelated($this->entity['id'], array('sort' => 'relationship'));

    if ($this->entity['primary_ext'] == 'Org')
    {
      $q = LsDoctrineQuery::create()
        ->from('Entity e')
        ->where('e.parent_id = ?', $this->entity['id']);
        
      $this->children_pager = new LsDoctrinePager($q, $page=1, $num=5);
    }  
  }
  
  
  public function executeRelationshipSection()
  {
    $ret = array();

    foreach ($this->rels as $rel)
    {
      //filter by category ID
      if (!in_array($rel['category_id'], $this->category_ids))
      {
        continue;
      }

      $baseOrder = ($this->entity['id'] == $rel['entity1_id']) ? 1 : 2;
      $relatedOrder = ($baseOrder % 2) + 1;

      //filter by order
      if ($this->order && ($this->order != $baseOrder))
      {
        continue;
      }

      $relatedEntity = $rel['Entity' . $relatedOrder];
      $relatedExtensions = explode(',', $rel['Entity' . $relatedOrder]['types']);
      
      //filter by required extensions
      if ($this->extensions && count(array_diff($this->extensions, $relatedExtensions)))
      {
        continue;
      }
      
      //filter by disallowed extensions
      if ($this->exclude_extensions && count(array_intersect($this->exclude_extensions, $relatedExtensions)))
      {
        continue;
      }
      
      //filtering done, now we package it all up
      if (!isset($ret[$relatedEntity['id']]))
      {
        $relatedEntity['Relationships'] = array($rel);
        $ret[$relatedEntity['id']] = $relatedEntity;
      }      
      else
      {
        $ret[$relatedEntity['id']]['Relationships'][] = $rel;
      }
    }

    //SORTING
    if ($this->order_by_num)
    {
      uasort($ret, array('EntityTable', 'relatedEntityCmp'));
      $ret = array_reverse($ret, true);
    }
    elseif ($this->order_by_amount)
    {
      uasort($ret, array('EntityTable', 'relatedEntityAmountCmp'));
      $ret = array_reverse($ret, true);
    }
    
    if (count($ret))
    {
      $this->pager = new LsDoctrinePager($ret, $page=1, $num=10);
    }
  }
  
  
  public function executeInterlocks()
  {
    $order1 = ($this->entity['primary_ext'] == 'Person') ? 1 : 2;
    $order2 = ($this->entity['primary_ext'] == 'Person') ? 2 : 1;

    $options = array(
      'cat1_ids' => RelationshipTable::POSITION_CATEGORY . ',' . RelationshipTable::MEMBERSHIP_CATEGORY,
      'order1' => $order1,
      'cat2_ids' => RelationshipTable::POSITION_CATEGORY . ',' . RelationshipTable::MEMBERSHIP_CATEGORY,
      'order2' => $order2,
      'page' => $this->page,
      'num' => $this->num,
      'map_id' => $this->map_id
    );

    $entities = EntityApi::getSecondDegreeNetwork($this->entity['id'], $options);      
    $count = EntityApi::getSecondDegreeNetwork($this->entity['id'], $options, $countOnly=true);

    //hack to get the pager working with a slice of data
    if ($this->page > 1)
    {
      $filler = array_fill(0, ($this->page - 1) * $this->num, null);
      $entities = array_merge($filler, $entities);
    }

    $this->entity_pager = new LsDoctrinePager($entities, $this->page, $this->num);
    $this->entity_pager->setNumResults($count);
  }
  
  
  public function executeGiving()
  {
    if ($this->entity['primary_ext'] == 'Person')
    {
      $options = array(
        'cat1_ids' => RelationshipTable::DONATION_CATEGORY,
        'order1' => 1,
        'cat2_ids' => RelationshipTable::DONATION_CATEGORY,
        'order2' => 2,
        'page' => $this->page,
        'num' => $this->num
      );
      
      $entities = EntityApi::getSecondDegreeNetwork($this->entity['id'], $options);      
      $count = EntityApi::getSecondDegreeNetwork($this->entity['id'], $options, $countOnly=true);

      //hack to get the pager working with a slice of data
      if ($this->page > 1)
      {
        $filler = array_fill(0, ($this->page - 1) * $this->num, null);
        $entities = array_merge($filler, $entities);
      }

      $this->donor_pager = new LsDoctrinePager($entities, $this->page, $this->num);
      $this->donor_pager->setNumResults($count);      
    }
    else
    {
      //write custom queries to get total amounts per recipient

      //first get everyone with position or membership in this org
      $db = Doctrine_Manager::connection();
      $sql = 'SELECT DISTINCT r.entity1_id FROM relationship r ' .
             'WHERE r.entity2_id = ? AND r.category_id IN (1, 3) AND r.is_deleted = 0';
      $stmt = $db->execute($sql, array($this->entity['id']));
      $relatedIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

      if (count($relatedIds))
      {      
        //now get recipients, totals, and degree1_ids
        $select = 'e.*, r.entity2_id AS degree2_id, GROUP_CONCAT(DISTINCT r.entity1_id) AS degree1_ids, COUNT(DISTINCT r.entity1_id) AS num, SUM(r.amount) AS total';
        $from = 'relationship r FORCE INDEX (entity1_category_idx) LEFT JOIN entity e ON (e.id = r.entity2_id)';
        $where = 'r.entity1_id IN (' . implode(',', $relatedIds) . ') AND r.category_id = ? AND r.is_deleted = 0';
        $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where . ' GROUP BY r.entity2_id ORDER BY total DESC';
        $stmt = $db->execute($sql, array(RelationshipTable::DONATION_CATEGORY));
        $entities = $stmt->fetchAll(PDO::FETCH_ASSOC);
      }
      else
      {
        $entities = array();
      }

      //use pager to limit shown results
      $this->person_recipient_pager = new LsDoctrinePager($entities, $this->page, $this->num);
    }
  }
  
  public function executePolitical()
  {
    $db = Doctrine_Manager::connection();
    $this->all_cycles = array('1990','1992','1994','1996','1998','2000','2002','2004','2006','2008','2010','2012');
    if (!$this->start_cycle)
    {
      $this->start_cycle = $this->request->getParameter('start_cycle','1990');
      $this->end_cycle = $this->request->getParameter('end_cycle','2012');
    }
    
    if ($this->entity['primary_ext'] == 'Person')
    {
    	$db = Doctrine_Manager::connection();
      
      $select = 'SELECT e.id as donor_id,e.name as donor_name,e.primary_ext as donor_ext,e2.id as recipient_id,e2.name as recipient_name,e2.primary_ext as recipient_ext,f.amount as amt,crp_cycle,p2.party_id AS party_id ';
      
      $from = 'FROM entity e LEFT JOIN relationship r ON r.entity1_id = e.id LEFT JOIN fec_filing f ON f.relationship_id = r.id LEFT JOIN entity e2 ON e2.id = r.entity2_id LEFT JOIN person p2 ON p2.entity_id = e2.id ';
      
      $where = 'WHERE r.category_id = 5 AND r.is_deleted=0 AND crp_cycle IS NOT NULL AND e.id = ? AND crp_cycle >= ? AND crp_cycle <= ? ';
      
      $sql = $select . $from . $where . 'group by f.id';
      $stmt = $db->execute($sql, array($this->entity['id'], $this->start_cycle,$this->end_cycle));
  	}
  	else
    {
    	
      $select = 'SELECT e.id as donor_id,e.name as donor_name,e.primary_ext as donor_ext,e2.id as recipient_id,e2.name as recipient_name,e2.primary_ext as recipient_ext,r2.is_current,p.is_board,p.is_executive,f.amount as amt,crp_cycle,p2.party_id AS party_id ';
      
      $from = 'FROM relationship r LEFT JOIN relationship r2 ON r2.entity1_id = r.entity1_id LEFT JOIN entity e ON e.id = r.entity1_id LEFT JOIN fec_filing f ON f.relationship_id = r.id LEFT JOIN position p ON p.relationship_id = r2.id LEFT JOIN entity e2 ON e2.id = r.entity2_id LEFT JOIN person p2 ON p2.entity_id = e2.id ';
      
      $where = 'WHERE r.category_id = 5 AND r2.category_id IN (1,3) AND r.is_deleted=0 AND r2.is_deleted=0 AND r2.entity2_id = ? AND crp_cycle IS NOT NULL AND crp_cycle >= ? AND crp_cycle <= ? ';
      
      $sql = $select . $from . $where . 'group by f.id';
      $stmt = $db->execute($sql, array($this->entity['id'], $this->start_cycle,$this->end_cycle));
    }
    
    $giving_data = $stmt->fetchAll(); 
    $this->cycles = array();
    $cyc = $this->start_cycle;
    while ($cyc <= $this->end_cycle)
    {
      $this->cycles[] = $cyc;
      $cyc +=2;
    }

    $this->cycleAmts = $this->repAmts = $this->demAmts = array_fill_keys($this->cycles,0);

    $recipients = array();
    $donors = array();
    $this->total = 0;
    $this->repTotal = 0;
    $this->demTotal = 0;
    $other_total = 0;
    $this->numDonations = 0;
    $parties = array(12886 => 'D',12901 => 'R');
    $party = null;
    foreach($giving_data as $gd)
    {
      if ($gd['party_id']) $party = $parties[$gd['party_id']];
      if ($gd['amt'] > 0)
      {
        $this->numDonations++;
      }
      $recType = strtolower($gd['recipient_ext']) . 'Data';
      if (!isset($recipients[$recType][$gd['recipient_id']]))
      {
        $url = 'http://littlesis.org/' . strtolower($gd['recipient_ext']) . '/' . $gd['recipient_id'] . '/' . LsSlug::convertNameToSlug($gd['recipient_name']);
        $recipients[$recType][$gd['recipient_id']] = array('recipient_name' => $gd['recipient_name'],'recipient_url' => $url, 'recipient_amount' => $gd['amt'],'donor_count' => 1,'party' => $party,'donor_ids' =>array($gd['donor_id']));
      }
      else
      {
        $recipients[$recType][$gd['recipient_id']]['recipient_amount'] += $gd['amt']; 
        if(!in_array($gd['donor_id'],$recipients[$recType][$gd['recipient_id']]['donor_ids']))
        {
          $recipients[$recType][$gd['recipient_id']]['donor_ids'][] = $gd['donor_id'];
          $recipients[$recType][$gd['recipient_id']]['donor_count'] ++;
        }
      }
      if (!isset($donors[$gd['donor_id']]))
      {
        $url = 'http://littlesis.org/' . strtolower($gd['donor_ext']) . '/' . $gd['donor_id'] . '/' . LsSlug::convertNameToSlug($gd['donor_name']);
        $donors[$gd['donor_id']] = array('donor_name' => $gd['donor_name'],'donor_url' => $url, 'donor_amount' => $gd['amt'],'recipient_count' => 1,'recipient_ids' =>array($gd['recipient_id']));
      }
      else
      {
        $donors[$gd['donor_id']]['donor_amount'] += $gd['amt']; 
        if(!in_array($gd['recipient_id'],$donors[$gd['donor_id']]['recipient_ids']))
        {
          $donors[$gd['donor_id']]['recipient_ids'][] = $gd['recipient_id'];
          $donors[$gd['donor_id']]['recipient_count'] ++;
        }
      }
      $this->cycleAmts[$gd['crp_cycle']] += $gd['amt'];
      $this->total += $gd['amt'];
      if($gd['party_id'] == 12886)
      {
        $this->demAmts[$gd['crp_cycle']] += $gd['amt'];
        $this->demTotal += $gd['amt'];
      }
      else if ($gd['party_id'] == 12901)
      {
        $this->repAmts[$gd['crp_cycle']] += $gd['amt'];
        $this->repTotal += $gd['amt'];
      }
      else $other_total += $gd['amt'];
    }
    $this->personRecipients = LsArray::aasort($recipients['personData'],'recipient_amount',1);
    $this->orgRecipients = $orgs = LsArray::aasort($recipients['orgData'],'recipient_amount',1);
    $this->donors = LsArray::aasort($donors,'donor_amount',1);
    if ($this->numDonations > 0)
    {
      $this->avgDonation = $this->total/$this->numDonations;
    }
    $arr = array('politicians supported' => $this->personRecipients,'PACs/political orgs supported' => $this->orgRecipients, 'top donors' => $this->donors);
    $this->links = array();
    foreach($arr as $ak => $av)
    {
      if (count($av))
      {
        $this->links = $ak;
      }
    }
    if ($this->total > 0)
    {
      if ($this->repTotal > 0 || $this->demTotal > 0)
      {
        $this->demPct = LsNumber::makeReadable(($this->demTotal/($this->repTotal+$this->demTotal))*100,null,1,"%");
        $this->repPct = LsNumber::makeReadable(($this->repTotal/($this->repTotal+$this->demTotal))*100,null,1,"%");
        $lean_party = $this->repPct . " of campaign contributions were to Republicans";
        if ($this->demPct > $this->repPct) $lean_party = $this->demPct . " of campaign contributions were to Democrats";
        if (count($this->personRecipients) > 3)
        {
          $codes = array(70 => 'Strongly Republican', 55 => 'Republican',45 => 'Split Republican/Democratic',30 => 'Democratic', 0 => 'Strongly Democratic');
          foreach($codes as $ck => $cv)
          {
            if ($this->repPct >= $ck)
            {
              $this->orientation = $cv . '; ' . $lean_party;
              break;
            }
          }
        }
        else $this->orientation = "Not enough data to make determination";
      }
    }
    else $this->stats = null;
  
  }

  public function executeSchools()
  {
    $this->entity = Doctrine::getTable('Entity')->find($this->entity['id']);

    $q = $this->entity->getPersonSchoolsQuery();
    
    $this->school_pager = new LsDoctrinePager($q, $this->page, $this->num);
  }

  
  public function executeFindConnections()
  {
    $request = $this->getRequest();

    if ($id2 = $request->getParameter('id2'))
    {      
      if (!$this->entity2 = EntityApi::get($id2))
      {
        $this->forward404();
      }

      $page = $this->page;
      $num = $request->getParameter('num', 10);

      $options = array('cat_ids' => $request->getParameter('cat_ids', '1'));

      //get all chains
      $chains = SearchApi::getEntitiesChains($this->entity['id'], $id2, $options);      

      $offset = ($page - 1) * $num;

      $flat_chains = array();

      foreach ($chains as $degree => $ary)
      {
        foreach ($ary as $ids)
        {
          $flat_chains[]= $ids;
        }
      }

      $page_chains = array_slice($flat_chains, $offset, $num);
      $full_chains = array();

      foreach ($page_chains as $ids)
      {
        $full = array();
        $chain = SearchApi::buildRelationshipChain($ids, explode(',', $options['cat_ids']));

        foreach ($chain as $id => $rels)
        {
          $entity = EntityApi::get($id);
          $entity['Relationships'] = count($rels) ? BatchApi::getRelationships($rels, array()) : array();
          $full[]= $entity;
        }

        $full_chains[] = $full;
      }

      // foreach ($page_chains as $degree => $ary)
      // {
      //   foreach ($ary as $ids)
      //   {
      //     if ($count == $page)
      //     {
      //       $chain = SearchApi::buildRelationshipChain($ids, explode(',', $options['cat_ids']));
      //       break 2;
      //     }
  
      //     $count++;        
      //   }
      // }

      // count total number of chains
      // $total = 0;
      
      // foreach ($chains as $degree => $ary)
      // {
      //   $total += count($ary);
      // }

      $total = count($flat_chains);
      
      $chainAry = array_fill(0, $total, null);
      array_splice($chainAry, $offset, $num, $full_chains);

      $this->chain_pager = new LsDoctrinePager($chainAry, $page, $num);        

      // get entities for chain  
      // if ($chain)
      // {
      //   $this->entities = array();
    
      //   foreach ($chain as $id => $rels)
      //   {
      //     $entity = EntityApi::get($id);
      //     $entity['Relationships'] = count($rels) ? BatchApi::getRelationships($rels, array()) : array();
      //     $this->entities[] = $entity;
      //   }

      //   $chainAry = array_fill(0, $total, null);
      //   $chainAry[$page-1] = $this->entities;

      //   $this->chain_pager = new LsDoctrinePager($chainAry, $page, $num);        
      // }
    }
    else
    {
      //form submission, display matching persons
      if ($request->hasParameter('q'))
      {
        $num = $request->getParameter('num', 10);
        $page = $request->getParameter('page', 1);
  
        if (!$terms = $request->getParameter('q'))
        {
          $this->entity_pager = new LsDoctrinePager(array(), $page, $num);
        }
        else
        {  
          switch (sfConfig::get('app_search_engine'))
          {
            case 'sphinx':
              $this->entity_pager = EntityTable::getSphinxPager($terms, $page, $num);  
              break;
              
            case 'lucene':
              $ary = EntityTable::getLuceneArray($terms, null);
              $this->entity_pager = new LsDoctrinePager($ary, $page, $num);
              break;
            
            case 'mysql':
            default:
              $terms = explode(' ', $terms);    
              $q = EntityTable::getSimpleSearchQuery($terms);
              $this->entity_pager = new Doctrine_Pager($q, $page, $num);
              break;
          }
        }
      }
    }      
  }
}