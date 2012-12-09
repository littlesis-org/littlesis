<?php

class listActions extends sfActions
{
  public function checkList($request)
  {
    $this->list = Doctrine::getTable('LsList')->find($request->getParameter('id'));
    $this->forward404Unless($this->list);

    //check that the list has the given name
    $name = LsSlug::convertSlugToName($request->getParameter('slug'));
    
    if ($this->list['name'] != $name)
    {
      $this->redirect($this->list->getInternalUrl());
    }    
  }
  
  
  public function clearCache(LsList $l)
  {
    LsCache::clearListCacheById($l->id);
    LsCache::clearUserCacheById($this->getUser()->getGuardUser()->id);
  }


  public function executeRoute($request)
  {
    $directActions = array(
      'list',
      'add',
      'removeEntity'
    );

    if (in_array($target = $request->getParameter('target'), $directActions))
    {
      $this->forward('list', $target);
    }

    $this->checkList($request);

    $params = $request->getParameterHolder()->getAll();
    $target = $params['target'];
    $target = ($target == 'view') ? null : $target;
    unset($params['module']);
    unset($params['action']);
    unset($params['target']);
    unset($params['id']);
    
    $this->redirect($this->list->getInternalUrl($target, $params));
  }
  

  public function executeSlug($request)
  {
    $this->checkList($request);
    
    //check that the entity has the given name
    $name = LsSlug::convertSlugToName($request->getParameter('slug'));
    
    if ($this->list->name != $name)
    {
      $this->forward404();
    }
    
    $this->forward('list', $request->getParameter('target', 'view'));
  }

  
  public function executeView($request)
  {
    $this->checkList($request);
  }


  public function executeList($request)
  {
    
    $q = LsDoctrineQuery::create()
      ->select('l.*, COUNT(le.id) num')
      ->from('LsList l')
      ->leftJoin('l.LsListEntity le')
      ->where('l.is_network = 0')
      ->groupBy('l.id')
      ->orderBy('num DESC')
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);
      
    if (!$this->getUser()->isAuthenticated())
    {
      $q->addWhere('is_admin = ?', false);
    }
    
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);

    $this->list_pager = new LsDoctrinePager($q, $page, $num);
  }  
  
  
  public function executeAdd($request)
  {
    $this->list_form = new LsListForm;
    $this->reference_form = new ReferenceForm;
  
    if ($request->isMethod('post'))
    {
      $params = $request->getParameter('list');
      $refParams = $request->getParameter('reference');
      
      $this->list_form->bind($params);
      $this->reference_form->bind($refParams);
      
      if ($this->list_form->isValid() && $this->reference_form->isValid())
      {
        $list = new LsList;
        $list->name = $params['name'];
        $list->description = $params['description'];
        $list->is_ranked = isset($params['is_ranked']) ? true : false;

        if ($this->getUser()->hasCredential('admin'))
        {
          $list->is_admin = (isset($params['is_admin']) && $params['is_admin']) ? true : false;
          $list->is_featured = (isset($params['is_featured']) && $params['is_featured']) ? true : false;
        }

        $list->saveWithRequiredReference($refParams);

        $this->redirect($list->getInternalUrl());
      }   
    }
  }  
  
  public function executeEdit($request)
  {
    $this->checkList($request);  

    $this->checkUser($this->list, 'admin');
    
    $this->list_form = new LsListForm($this->list);
    $this->reference_form = new ReferenceForm;
    $this->reference_form->setSelectObject($this->list);
    

  

    if ($request->isMethod('post'))
    {
      $params = $request->getParameter('list');
      $refParams = $request->getParameter('reference');
      
      $this->list_form->bind($params);
      $this->reference_form->bind($refParams);

      
      if ($this->list_form->isValid() && $this->reference_form->isValid())
      {
        $this->list->name = $params['name'];
        $this->list->description = $params['description'];
        $this->list->is_ranked = (isset($params['is_ranked']) && $params['is_ranked']) ? true : false;    

        if ($this->getUser()->hasCredential('admin'))
        {
          $this->list->is_admin = (isset($params['is_admin']) && $params['is_admin']) ? true : false;
          $this->list->is_featured = (isset($params['is_featured']) && $params['is_featured']) ? true : false;
        }

        $this->list->saveWithRequiredReference($refParams);

        $this->clearCache($this->list);

        $this->redirect($this->list->getInternalUrl());
      }  
    }  
  }
  
  
  public function executeRemove($request)
  {
    if (!$request->isMethod('post'))
    {
      $this->redirect('error/invalid');
    }

    $this->checkList($request);  
    $this->checkUser($this->list, 'admin');

    //get entity ids for cache clearing
    $entityIds = LsListTable::getEntityIdsById($this->list['id']);
    
    $this->list->delete();

    $this->clearCache($this->list);

    foreach ($entityIds as $entityId)
    {
      LsCache::clearEntityCacheById($entityId);
    }
    
    $this->redirect('@homepage');
  }
  
  
  public function executeAddEntity($request)
  {
    $this->checkList($request);    
    $this->checkUser($this->list, 'admin');
  
    if ($request->hasParameter('q'))
    {
      $page = $request->getParameter('page', 1);
      $num = $request->getParameter('num', 10);


      if (!$terms = $request->getParameter('q'))
      {
        $this->results_pager = new LsDoctrinePager(array(), $page, $num);
      }
      else
      {  
        switch (sfConfig::get('app_search_engine'))
        {
          case 'sphinx':
            $this->results_pager = EntityTable::getSphinxPager($terms, $page, $num);  
            break;
            
          case 'lucene':
            $ary = EntityTable::getLuceneArray($terms, null);
            $this->results_pager = new LsDoctrinePager($ary, $page, $num);
            break;
          
          case 'mysql':
          default:
            $terms = explode(' ', $terms);    
            $q = EntityTable::getSimpleSearchQuery($terms);
            $this->results_pager = new Doctrine_Pager($q, $page, $num);
            break;
        }
      }
    }


    if ($request->isMethod('post'))
    {
      $entity = Doctrine::getTable('Entity')->find($request->getParameter('entity_id'));
      $this->forward404Unless($entity);

      //if entity is already on the list, do nothing
      $q = LsDoctrineQuery::create()
        ->from('LsListEntity le')
        ->where('le.list_id = ? AND le.entity_id = ?', array($this->list['id'], $entity['id']));
        
      if (!$q->count())
      {
        $listEntity = new LsListEntity;
        $listEntity->list_id = $this->list->id;
        $listEntity->entity_id = $entity->id;
        $listEntity->save();      
  
        $this->clearCache($this->list);
        LsCache::clearEntityCacheById($entity->id);
      }
      
      $this->redirect($this->list->getInternalUrl());
    }
  }
  
  
  public function executeSetRank($request)
  {
    
    if (!$request->isMethod('post'))
    {
      $this->redirect('error/invalid');        
    }

    $this->checkList($request);    
    $this->checkUser($this->list, 'admin');
    
    $entity = Doctrine::getTable('Entity')->find($request->getParameter('entity_id'));
    $this->forward404Unless($entity);

    $q = Doctrine_Query::create()
      ->from('LsListEntity le')
      ->where('le.list_id = ? AND le.entity_id = ?', array($this->list->id, $entity->id));
      
    if (!$listEntity = $q->fetchOne())
    {
      $this->redirect('error/internal');
    }
    
    $listEntity->rank = $request->getParameter('rank');
    $listEntity->save();

    $this->clearCache($this->list);
    LsCache::clearEntityCacheById($entity->id);

    $this->redirect($this->list->getInternalUrl());
  }
  
  
  public function executeRemoveEntity($request)
  {
    
    if (!$request->isMethod('post'))
    {
      $this->redirect('error/invalid');
    }

    $listEntity = Doctrine::getTable('LsListEntity')->find($request->getParameter('id'));
    $this->forward404Unless($listEntity);

    $list = $listEntity->LsList;
    
    $this->checkUser($list, 'admin');
    
    $listEntity->delete();

    $this->clearCache($list);
    LsCache::clearEntityCacheById($entity->id);
    
    $this->redirect($list->getInternalUrl());
  }
  
  
  public function executeModifications($request)
  {  
    $this->checkList($request);
    
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);
    $userId = $request->getParameter('user_id');

    if ($request->getParameter('group_by_user'))
    {
      $q = $this->list->getModificationsQuery()
        ->addWhere('NOT EXISTS( SELECT id FROM modification WHERE modification.user_id = m.user_id AND modification.created_at > m.created_at )');
      
      $this->modification_pager = new LsDoctrinePager($q, $page, $num);    
    }
    else
    {
      $this->modification_pager = new LsDoctrinePager(
        $this->list->getModificationsQuery($userId)->setHydrationMode(Doctrine::HYDRATE_ARRAY),
        $page,
        $num
      );
    }
  }
  
  
  public function executeEntityModifications($request)
  {
    $this->checkList($request);
    
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 10);

    //first get LsListEntities for this list
    $this->list_entities = LsDoctrineQuery::create()
      ->select('le.id, le.entity_id')
      ->from('LsListEntity le')
      ->where('le.list_id = ?', $this->list->id)
      ->andWhere('le.is_deleted IS NOT NULL')
      ->fetchAll(PDO::FETCH_KEY_PAIR);    

    //then get create and delete modifications of these LsListEntities
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT m.id FROM ls_list_entity le ' . 
           'LEFT JOIN modification m ON (m.object_model = ? AND m.object_id = le.id) ' .
           'WHERE le.list_id = ? AND (m.is_create = 1 OR m.is_delete = 1)' . 
           'ORDER BY m.id DESC';
    
    $stmt = $db->execute($sql, array('LsListEntity', $this->list->id));
    $modificationIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    //get full data structure
    $q = LsDoctrineQuery::create()
      ->from('Modification m')
      ->leftJoin('m.User u')
      ->leftJoin('u.Profile p')
      ->whereIn('m.id', $modificationIds)
      ->orderBy('m.id DESC')
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);

    $this->entity_modification_pager = new LsDoctrinePager($q, $page, $num);
  }


  public function executeComments($request)
  {
    $request->setParameter('model', 'LsList');

    $this->forward('comment', 'list');
  }
  
  
  public function executeAddComment($request)
  {
    $request->setParameter('model', 'LsList');

    $this->forward('comment', 'add');  
  }
  
  
  public function executeFindMember($request)
  {
    $this->checkList($request);

    $terms = explode(' ', $request->getParameter('member_search_terms'));
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 10);

    $q = EntityTable::getSimpleSearchQuery($terms)
      ->leftJoin('e.LsListEntity listentity')
      ->andWhere('listentity.list_id = ?', $this->list->id);
      
    $this->member_pager = new Doctrine_Pager($q, $page, $num);
  }
  
  
  public function checkUser($list, $credential)
  {
    if (!$this->getUser()->hasCredential($credential) && $list->is_admin == 1)
    {
      $this->redirect('error/invalid');
    }
  }


  public function executeMembers($request)
  {
    $this->checkList($request);
    
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 50);
    
    $q = $this->list->getListEntitiesByRankQuery();
    
    $this->list_entity_pager = new LsDoctrinePager($q, $page, $num);  
    $this->list_entity_pager->setAjax(true);
    $this->list_entity_pager->setAjaxUpdateId('member_tabs_content');
    $this->list_entity_pager->setAjaxIndicatorId('indicator');
    $this->list_entity_pager->setAjaxHash('members');

    $this->setLayout(false);
  }


  public function executeInterlocks($request)
  {
    $this->checkList($request);

    //first get person list members
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT entity_id FROM ls_list_entity WHERE list_id = ?';
    $stmt = $db->execute($sql, array($request->getParameter('id')));
    $entityIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
       
    //get entities related by position or membership
    $select = 'e.*, COUNT(DISTINCT r.entity1_id) num, GROUP_CONCAT(DISTINCT r.entity1_id) people_ids, GROUP_CONCAT(DISTINCT ed.name) types';
    $from   = 'relationship r LEFT JOIN entity e ON (e.id = r.entity2_id) ' . 
              'LEFT JOIN extension_record er ON (er.entity_id = e.id) ' .
              'LEFT JOIN extension_definition ed ON (ed.id = er.definition_id)';
    $where  = 'r.entity1_id IN (' . implode(',', $entityIds) . ') AND r.category_id IN (?, ?) AND r.is_deleted = 0';
    $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where . ' GROUP BY r.entity2_id ORDER BY num DESC';
    $stmt = $db->execute($sql, array(RelationshipTable::POSITION_CATEGORY, RelationshipTable::MEMBERSHIP_CATEGORY));
    $orgs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $businesses = array();
    $govtBodies = array();
    $otherOrgs = array();
    
    foreach ($orgs as $org)
    {
      $exts = explode(',', $org['types']);

      if (in_array('Business', $exts))
      {
        $businesses[] = $org;
      }
      elseif (in_array('GovernmentBody', $exts))
      {
        $govtBodies[] = $org;
      }
      else
      {
        $otherOrgs[] = $org;
      }      
    }

    $this->business_pager = new LsDoctrinePager($businesses, 1, 5);
    $this->business_pager->setAjax(true);
    $this->business_pager->setAjaxUpdateId('member_tabs_content');
    $this->business_pager->setAjaxIndicatorId('indicator');
    $this->business_pager->setAjaxHash('business');

    $this->government_pager = new LsDoctrinePager($govtBodies, 1, 5);
    $this->government_pager->setAjax(true);
    $this->government_pager->setAjaxUpdateId('member_tabs_content');
    $this->government_pager->setAjaxIndicatorId('indicator');
    $this->government_pager->setAjaxHash('government');

    $this->other_pager = new LsDoctrinePager($otherOrgs, 1, 5);
    $this->other_pager->setAjax(true);
    $this->other_pager->setAjaxUpdateId('member_tabs_content');
    $this->other_pager->setAjaxIndicatorId('indicator');
    $this->other_pager->setAjaxHash('otherOrgs');

    $this->setLayout(false);
  }
  
  
  public function executeBusiness($request)
  {
    $this->checkList($request);

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);

    $q = $this->list->getSecondDegreeQuery(
      array(
        RelationshipTable::POSITION_CATEGORY, 
        RelationshipTable::MEMBERSHIP_CATEGORY
      ), 
      $order=2, 
      'Business', 
      null, 
      'Person', 
      true
    );

    $this->business_pager = new LsDoctrinePager($q, $page, $num);
    $this->business_pager->setAjax(true);
    $this->business_pager->setAjaxUpdateId('member_tabs_content');
    $this->business_pager->setAjaxIndicatorId('indicator');
    $this->business_pager->setAjaxHash('business');

    $this->setLayout(false);
  }


  public function executeGovernment($request)
  {
    $this->checkList($request);

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);

    $q = $this->list->getSecondDegreeQuery(array(RelationshipTable::POSITION_CATEGORY, RelationshipTable::MEMBERSHIP_CATEGORY), $order=2, 'GovernmentBody', null, 'Person', true);

    $this->government_pager = new LsDoctrinePager($q, $page, $num);
    $this->government_pager->setAjax(true);
    $this->government_pager->setAjaxUpdateId('member_tabs_content');
    $this->government_pager->setAjaxIndicatorId('indicator');
    $this->government_pager->setAjaxHash('government');

    $this->setLayout(false);
  }
  
  
  public function executeOtherOrgs($request)
  {
    $this->checkList($request);

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);

    $q = $this->list->getSecondDegreeQuery(array(RelationshipTable::POSITION_CATEGORY, RelationshipTable::MEMBERSHIP_CATEGORY), $order=2, null, array('Business', 'GovernmentBody'), 'Person', true);

    $this->other_pager = new LsDoctrinePager($q, $page, $num);
    $this->other_pager->setAjax(true);
    $this->other_pager->setAjaxUpdateId('member_tabs_content');
    $this->other_pager->setAjaxIndicatorId('indicator');
    $this->other_pager->setAjaxHash('otherOrgs');

    $this->setLayout(false);
  }


  public function executeGiving($request)
  {
    $this->checkList($request);

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 10);

    $options = array(
      'cat_ids' => RelationshipTable::DONATION_CATEGORY,
      'order' => 1,
      'degree1_type' => 'Person',
      'page' => $page,
      'num' => $num,
      'sort' => 'amount'
    );
    
    $entities = LsListApi::getSecondDegreeNetwork($this->list['id'], $options);      
    $count = LsListApi::getSecondDegreeNetwork($this->list['id'], $options, $countOnly=true);

    //hack to get the pager working with a slice of data
    if ($page > 1)
    {
      $filler = array_fill(0, ($page - 1) * $num, null);
      $entities = array_merge($filler, $entities);
    }
  
    $this->committee_pager = new LsDoctrinePager($entities, $page, $num);
    $this->committee_pager->setNumResults($count);
    $this->committee_pager->setAjax(true);
    $this->committee_pager->setAjaxUpdateId('member_tabs_content');
    $this->committee_pager->setAjaxIndicatorId('indicator');
    $this->committee_pager->setAjaxHash('giving');

    $this->setLayout(false);
  }


  public function executeFunding($request)
  {
    $this->checkList($request);

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 10);

    $options = array(
      'cat_ids' => RelationshipTable::DONATION_CATEGORY,
      'order' => 2,
      'degree1_type' => 'Person',
      'degree2_type' => 'Person',
      'page' => $page,
      'num' => $num
    );
    
    $entities = LsListApi::getSecondDegreeNetwork($this->list['id'], $options);      
    $count = LsListApi::getSecondDegreeNetwork($this->list['id'], $options, $countOnly=true);

    //hack to get the pager working with a slice of data
    if ($page > 1)
    {
      $filler = array_fill(0, ($page - 1) * $num, null);
      $entities = array_merge($filler, $entities);
    }
  
    $this->donor_pager = new LsDoctrinePager($entities, $page, $num);
    $this->donor_pager->setNumResults($count);
    $this->donor_pager->setAjax(true);
    $this->donor_pager->setAjaxUpdateId('member_tabs_content');
    $this->donor_pager->setAjaxIndicatorId('indicator');
    $this->donor_pager->setAjaxHash('funding');

    $this->setLayout(false);
  }


  public function executeNotes($request)
  {
    $this->checkList($request);
    
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);
    
    $s = new LsSphinxClient($page, $num);
    $s->setFilter('lslist_ids', array($this->list['id']));
    $s->setFilter('visible_to_user_ids', array_unique(array(0, sfGuardUserTable::getCurrentUserId())));
    
    if ($userId = $request->getParameter('user_id'))
    {
      $s->setFilter('user_id', array($userId));
    }
    
    $this->note_pager = NoteTable::getSphinxPager($s, null, Doctrine::HYDRATE_ARRAY);
  }


  public function executeRefresh($request)
  {
    $this->checkList($request);
    LsCache::clearListCacheById($this->list['id']);
    
    $this->redirect($request->getParameter('ref', LsListTable::getInternalUrl($this->list)));
  }
  
  
  public function executeNetworkSearch($request)
  {
    $this->checkList($request);

    //need to escape vulnerable params before calling API
    LsApiRequestFilter::escapeParameters();

    $this->page = $request->getParameter('page', 1);
    $this->num = $request->getParameter('num', 20);

    $this->order = $request->getParameter('order');

    $this->cat_ids = implode(',', $request->getParameter('cat_ids', array()));

    $options = array(
      'cat_ids' => $this->cat_ids,
      'order' => $this->order,
      'page' => $this->page,
      'num' => $this->num
    );

    //get categories per form
    $this->categories = LsDoctrineQuery::create()
      ->select('c.id, c.name')
      ->from('RelationshipCategory c')
      ->orderBy('c.id')
      ->fetchAll(PDO::FETCH_KEY_PAIR);

    if ($request->getParameter('commit') == 'Search')
    {    
      $entities = LsListApi::getSecondDegreeNetwork($this->list['id'], $options);      
      $count = LsListApi::getSecondDegreeNetwork($this->list['id'], $options, $countOnly=true);

      //hack to get the pager working with a slice of data
      if ($this->page > 1)
      {
        $filler = array_fill(0, ($this->page - 1) * $this->num, null);
        $entities = array_merge($filler, $entities);
      }
  
      $this->entity_pager = new LsDoctrinePager($entities, $this->page, $this->num);
      $this->entity_pager->setNumResults($count);  
    }  
  }

  
  public function executeImages($request)
  {
    $this->checkList($request);

    $q = LsDoctrineQuery::create()
      ->from('Entity e')
      ->leftJoin('e.Person p')
      ->leftJoin('e.LsListEntity le')
      ->leftJoin('e.Image i')
      ->where('le.list_id = ?', $this->list['id'])
      ->andWhere('e.is_deleted = 0 AND e.primary_ext = ?', array('Person'))
      ->andWhere('le.is_deleted = 0')
      ->andWhere('i.is_featured = 1')
      ->andWhere('i.is_deleted = 0')
      ->groupBy('e.id')
      ->orderBy('p.name_last ASC');
      
    $this->entities = $q->fetchArray();      
  }
  
  
  public function executeAddBulk($request)
  {
    $this->checkList($request, false, false);    
        
    $this->reference_form = new ReferenceForm;
    $this->reference_form->setSelectObject($this->list);
    
    $this->csv_form = new CsvUploadForm;
      
     if ($request->isMethod('post'))
    {
      $commit = $request->getParameter('commit');

      if ($commit == 'Cancel')
      {
        $this->redirect(LsListTable::getInternalUrl($this->list));
      }

      // IF REFERENCE INFO AND FILE HAVE BEEN SUBMITTED, LOAD DATA IN

      if ($request->hasParameter('reference') && $request->hasParameter('csv'))
      {
        $csvParams = $request->getParameter('csv');
        $filePath = $request->getFilePath('csv[file]');
        $this->csv_form->bind($csvParams, $request->getFiles('csv'));
        
       
        
        $refParams = $request->getParameter('reference');
        $this->reference_form->bind($refParams);
        
        if ($this->reference_form->isValid())
        {
          if ($spreadsheetArr = LsSpreadsheet::parse($filePath))
          {         
            $names = $spreadsheetArr['rows'];
            if (!in_array('name',$spreadsheetArr['headers']))
            {
              $request->setError('csv', 'The file you uploaded could not be parsed properly because there is no "name" column.');
              return;
            }
          }
          else
          {
            $request->setError('csv', 'The file you uploaded could not be parsed properly.');
            return;
          }   
          if ($this->ref_id = $refParams['existing_source'])
          {
            $ref = Doctrine::getTable('Reference')->find($this->ref_id);
            $url = $ref->source;
          }
          else
          {
            $ref = new Reference;
            $ref->object_model = 'LsList';
            $ref->object_id = $this->list->id;
            $ref->source = $refParams['source'];
            $ref->name = $refParams['name'];
            $ref->source_detail = $refParams['source_detail'];
            $ref->publication_date = $refParams['publication_date'];
            $ref->save();

            $this->ref_id = $ref->id;
          }
          
          $this->default_type = $request->getParameter('default_type');
          if (!$this->default_type)
          {
            $request->setError('csv','You need to choose a default type.');
            return;
          }
          
          $this->extensions = ExtensionDefinitionTable::getByTier(2, $this->default_type);
          $extensions_arr = array();
          foreach($this->extensions as $ext)
          {
            $extensions_arr[] = $ext->name;
          }
          
          $this->matches = array();
    
          if (isset($names) && count($names) > 0)
          {        
            for ($i = 0; $i < count($names); $i++)
            {
              if (isset($names[$i]['name']) && trim($names[$i]['name']) != '')
              {
                $name = $names[$i]['name'];
                $name_terms = $name;
                if ($this->default_type == 'Person')
                {
                  $name_parts = preg_split('/\s+/',$name);
                  if (count($name_parts) > 1)
                  {
                    $name_terms = PersonTable::nameSearch($name, true);
                  }
                  $terms = $name_terms;
                  $primary_ext = "Person";
                }
                else if ($this->default_type == 'Org')
                {
                  $name_terms = OrgTable::nameSearch($name);
                  $terms = $name_terms;
                  $primary_ext = "Org";
                }
                else
                {
                  $terms = $name_terms;
                  $primary_ext = null;
                }
                $pager = EntityTable::getSphinxPager($terms, $page=1, $num=20, $listIds=null, $aliases=true, $primary_ext); 
                $match = array('name' => $name);
                $match['search_results'] = $pager->execute();
                $match['blurb'] = isset($names[$i]['blurb']) ? $names[$i]['blurb'] : null;
                $match['rank'] = isset($names[$i]['rank']) ? $names[$i]['rank'] : null;
                $match['types'] = array();
                if(isset($names[$i]['types']))
                {
                  $types = explode(',',$names[$i]['types']);
                  $types = array_map('trim',$types);
                  foreach($types as $type)
                  {
                    if(in_array($type,$extensions_arr))
                    {
                      $match['types'][] = $type;
                    }
                  }
                }
                $this->matches[] = $match;
              }
            }
          }    
        }
      }
      // REFERENCE HAS ALREADY BEEN ADDED, ADD ENTITIES TO LIST
      else if ($request->hasParameter('ref_id'))
      { 
        $this->ref_id = $this->getRequestParameter('ref_id');  
        $entity_ids = array();
        $default_type = $this->getRequestParameter('default_type');
        for ($i = 0; $i < $this->getRequestParameter('count'); $i++)
        {
          if ($entity_id = $request->getParameter('entity_' . $i))
          {
            $selected_entity_id = null;
            if ($entity_id == 'new')
            {
              $name = $request->getParameter('new_name_' . $i);
              if ($default_type == 'Person')
              {
                $new_entity = PersonTable::parseFlatName($name);
              }
              else
              {
                $new_entity = new Entity;
                $new_entity->addExtension('Org');
                $new_entity->name = trim($name);
              }
              if ($types = $request->getParameter('new_extensions_' . $i))
              {
                foreach($types as $type)
                {
                  $new_entity->addExtension($type);
                }
              }
              $new_entity->save();
              $new_entity->blurb = $request->getParameter('new_blurb_' . $i);

              $ref = Doctrine::getTable('Reference')->find($request->getParameter('ref_id'));
              $new_entity->addReference($ref->source, null, null, $ref->name);
              
              $new_entity->save();
              $selected_entity_id = $new_entity->id;
            }
            else if ($entity_id > 0)
            {
              $selected_entity_id = $entity_id;
            }
            if ($selected_entity_id)
            {
              $q = LsDoctrineQuery::create()
              ->from('LsListEntity le')
              ->where('le.list_id = ? AND le.entity_id = ?', array($this->list['id'], $selected_entity_id));
              
              if (!$q->count())
              {
                
                $ls_list_entity = new LsListEntity;
                $ls_list_entity->list_id = $this->list->id;
                $ls_list_entity->entity_id = $selected_entity_id;
                $ls_list_entity->rank = $request->getParameter('entity_' . $i . '_rank');
                $ls_list_entity->save();
                
                LsCache::clearEntityCacheById($selected_entity_id);
              }
            }
          }
        }
        $this->clearCache($this->list);

        $this->redirect($this->list->getInternalUrl());
      }
      else
      {
        $request->setError('name', 'The name you entered is invalid');
      }
    }    
  }


  public function executeReferences($request)
  {
    $this->checkList($request);

    $request->setParameter('model', 'LsList');
    $request->setParameter('id', $this->list['id']);

    $this->forward('reference', 'list');
  }
  
  
  public function executeConnectTo($request)
  {
    $this->checkList($request);

    $entityId = $request->getParameter('entity_id');
    $num = $request->getParameter('num', 40);
    $page = $request->getParameter('page', 1);

    if ($entityId && ($this->entity = Doctrine::getTable('Entity')->find($entityId)))
    {
      $db = Doctrine_Manager::connection();
      $q = LsDoctrineQuery::create()
        ->select('e.*, GROUP_CONCAT(DISTINCT l.relationship_id) as relationship_ids')
        ->from('Entity e')
        ->leftJoin('e.LsListEntity le')
        ->leftJoin('e.Link l')
        ->where('le.list_id = ?', $this->list['id'])
        ->andWhere('l.entity2_id = ?', $entityId)
        ->groupBy('l.entity1_id');
        
      if ($catId = $request->getParameter('cat_id'))
      {
        $q->andWhere('l.category_id = ?', $catId);        
      }
      
      if ($isCurrent = $request->getParameter('is_current'))
      {
        $q->andWhere('l.is_current = ?', $isCurrent);
      }

      if ($primaryExt = $request->getParameter('primary_ext'))
      {
        $q->andWhere('e.primary_ext = ?', $primaryExt);
      }
      
      if ($matches = $q->fetchArray())
      {
        $this->matches_pager = new LsDoctrinePager($matches, $page, $num);
      }
    }
    else
    {
      //form submission, display matching persons
      if ($request->hasParameter('q'))
      {  
        if (!$terms = $request->getParameter('q'))
        {
          $this->entity_pager = new LsDoctrinePager(array(), $page, $num);
        }
        else
        {  
          $this->entity_pager = EntityTable::getSphinxPager($terms, $page, $num);  
        }
      }
    }
  }
  
  //modeled on entity/LobbyingArmy for lists with less than 500 entities
  public function executePictures($request)
  {
    $this->checkList($request);
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT e.id, e.name, e.blurb, e.primary_ext, i.filename, IF(i.filename IS NOT NULL, 1, 0) AS has_image ' .
           'FROM ls_list_entity le ' .
           'LEFT JOIN entity e ON (e.id = le.entity_id) ' .
           'LEFT JOIN person p ON (p.entity_id = e.id) ' .
           'LEFT JOIN image i ON (i.entity_id = e.id AND i.is_featured = 1 AND i.is_deleted = 0) ' .
           'WHERE le.list_id = ? AND le.is_deleted = 0 ' .
           'AND e.is_deleted = 0 ' .
           'GROUP BY e.id ' .
           'ORDER BY has_image DESC, p.name_last ASC';
    $stmt = $db->execute($sql, array($this->list->id));
    $this->entities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($this->entities) > 500) $this->entities = null;
  }
  
  public function executeMatchRelated($request)
  {  
    $this->checkList($request);
		
		$db = Doctrine_Manager::connection();
			
		$sql = 'SELECT e.* FROM os_entity_transaction et LEFT JOIN entity e ON (et.entity_id = e.id) LEFT JOIN ls_list_entity le on le.entity_id = e.id WHERE e.is_deleted=0 AND le.is_deleted =0 AND le.list_id = ? AND et.reviewed_at IS NULL AND et.locked_at IS NULL GROUP BY e.id LIMIT 100'; 
		
		$stmt = $db->execute($sql, array($this->list['id']));
		$this->relatedIds = $stmt->fetchAll(PDO::FETCH_COLUMN);   
		if (count($this->relatedIds))
		{
    	$nextEntity = Doctrine::getTable('Entity')->find($this->relatedIds[0]);
    	$this->getUser()->setAttribute('os_related_ids', $this->relatedIds);
			$this->redirect(EntityTable::getInternalUrl($nextEntity, 'matchDonations'));
		}
		else $this->redirect(EntityTable::getInternalUrl($this->entity));
    
  }
}