<?php

/**
 * search actions.
 *
 * @package    ls
 * @subpackage search
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class searchActions extends sfActions
{
  public function preExecute()
  {
    //setup response format
    $request = $this->getRequest();
    $this->format = $request->getParameter('format', 'xml');        
    $this->getResponse()->setContentType( ($this->format == 'json') ? 'application/json' : 'application/xml' );
    $this->setLayout( ($this->format == 'json') ? 'json' : 'xml' );  
  }
  

  public function getParams(Array $acceptedKeys)
  {
    $ret = array();
    $params = $this->getRequest()->getParameterHolder()->getAll();
    
    foreach ($acceptedKeys as $key)
    {
      if (@$params[$key]) { $ret[$key] = $params[$key]; }
    }

    $this->getResponse()->setSlot('params', $ret);
    
    return $ret;
  }


  public function executeEntities($request)
  {
    $options = $this->getParams(array('q', 'type_ids', 'num', 'page'));
    $result = SearchApi::getEntities($options);    
    $this->entities = array();
    
    if ($result['total_found'] > 0 && isset($result['matches']))
    {
      $db = Doctrine_Manager::connection();
      
      $ids = array_keys($result['matches']);
      $tmp = array();

      $select = LsApi::generateSelectQuery(array('e' => 'Entity'));
      $from = 'entity e';
      $where = 'e.id IN (' . implode(',', $ids) . ') AND e.is_deleted = 0';

      $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where . ' ORDER BY FIELD(e.id, ' . implode(',', $ids) . ')';
      $stmt = $db->execute($sql);
      $this->entities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $this->getResponse()->setSlot('total', $result['total_found']);
    
    return 'Xml';
  }
  
  
  public function executeEntitiesWithOrgs($request)
  {
    $options = $this->getParams(array('q', 'type_ids', 'num', 'page', 'is_current'));
    $result = SearchApi::getEntities($options);    
    $this->entities = array();
    
    if ($result['total_found'] > 0 && isset($result['matches']))
    {
      $ids = array_keys($result['matches']);

      $this->entities = BatchApi::getTheyRulePersons($ids, $options);
    }
    
    $this->getResponse()->setSlot('total', $result['total_found']);
    
    return 'Xml';  
  }
  
  
  public function executeLookupEntities($request)
  {
    $idName = $request->getParameter('id_name');
    $idValue = $request->getParameter('id_value');
    $this->forward404Unless($idValue && in_array($idName, array_keys(SearchApi::$foreignIdMap)));
    
    $options = $this->getParams(array('page', 'num'));
    $this->entities = SearchApi::getEntitiesByForeignId($idName, $idValue, $options);
    return 'Xml';
  }
  

  public function executeRelationships($request)
  {
    $options = $this->getParams(array('num', 'page', 'cat_ids'));

    $entity1Id = $request->getParameter('entity1_id');
    $entity2Id = $request->getParameter('entity2_id');

    $this->entity1 = EntityApi::get($entity1Id);
    $this->entity2 = EntityApi::get($entity2Id);

    $this->rels = SearchApi::getRelationships($entity1Id, $entity2Id, $options);
    return 'Xml';
  }
  
  
  public function executeLists($request)
  {
    $options = $this->getParams(array('q', 'num', 'page'));
    $this->lists = SearchApi::getLists($options);  
    return 'Xml';
  }
  
  
  public function executeEntitiesChains($request)
  {
    $options = $this->getParams(array('cat_ids', 'page'));

    $id1 = $request->getParameter('id1');
    $id2 = $request->getParameter('id2');

    if (!isset($options['cat_ids']))
    {
      $options['cat_ids'] = '1';
    }

    $this->chains = SearchApi::getEntitiesChains($id1, $id2, $options);
    $this->chain = null;
    
    $page = isset($options['page']) ? $options['page'] : 1;
    $count = 1;

    foreach ($this->chains as $degree => $ary)
    {
      foreach ($ary as $ids)
      {
        if ($count == $page)
        {
          $this->chain = SearchApi::buildRelationshipChain($ids, $options['cat_ids']);
          break 2;
        }

        $count++;        
      }
    }

    if ($this->chain)
    {
      $this->entities = array();
  
      foreach ($this->chain as $id => $rels)
      {
        $this->entities[$id] = EntityApi::get($id);
        $this->entities[$id]['relationship_ids'] = implode(',', $rels);
      }
    }

    return 'Xml';
  }
}
