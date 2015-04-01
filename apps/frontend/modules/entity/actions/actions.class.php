<?php

class entityActions extends sfActions
{
  public function checkEntity($request, $includeDeleted=false, $returnArray=true)
  {
    if ($returnArray)
    {
      $this->entity = EntityApi::get($request->getParameter('id'), $includeDeleted);
      $this->forward404Unless($this->entity);        
      $this->entity = EntityTable::loadPrimaryExtensionFields($this->entity);
    }
    else
    {
      $q = LsDoctrineQuery::create()
        ->from('Entity e')
        ->where('e.id = ?', $request->getParameter('id'));
      
      if ($includeDeleted)
      {
        $q->andWhere('e.is_deleted IS NOT NULL');
      }
      
      $this->entity = $q->fetchOne();        
      $this->forward404Unless($this->entity);        
    }
    //check that the entity has the given name
    $slug = $request->getParameter('slug');
    $primarySlug = LsSlug::convertNameToSlug($this->entity['name']);
    
    //if the name isn't primary, we redirect to the url with the primary name
    if ($slug != $primarySlug)
    {
      $params = $request->getParameterHolder()->getAll();

      if ($params['target'] == 'view')
      {
        $params['target'] = null;
      }

      $url = EntityTable::generateRoute($this->entity, $params['target'], $params);
      $this->redirect($url);
    }
  }


  public function clearCache($e)
  {
    LsCache::clearEntityCacheById($e['id']);
    LsCache::clearUserCacheById($this->getUser()->getGuardUser()->id);
  }


  public function logRecentView()
  {
    if ($this->getUser()->isAuthenticated())
    {
      $entityIds = (array) $this->getUser()->getAttribute('viewed_entity_ids');
      array_unshift($entityIds, $this->entity['id']);      
      $entityIds = array_merge(array_unique(array_slice($entityIds, 0, 10)));
      $this->getUser()->setAttribute('viewed_entity_ids', $entityIds);
    }
  }


  //this action catches requests to internal URLS like 'entity/view?id=13312&slug=Chuck_Grassley'
  //and redirects it to the proper page, eg 'person/13312/Chuck_Grassley'
  public function executeRoute($request)
  {
    $directActions = array(
      'addPerson',
      'addOrg',
      'image',
      'cropImage',
      'editImage',
      'removeImage',
      'featureImage',
      'address',
      'editAddress',
      'removeAddress',
      'editPhone',
      'removePhone',
      'editEmail',
      'removeEmail',
      'addAlias',
      'removeAlias',
      'makePrimaryAlias',
      'addressModifications',
      'imageModifications',
      'removeList',
      'theyRuleGenders',
      'findKeys',
      'addKey',
      'removeKey',
      'editBlurbInline'
    );
    if (in_array($target = $request->getParameter('target'), $directActions))
    {
      $this->forward('entity', $target);
    }

    $this->checkEntity($request);

    $params = $request->getParameterHolder()->getAll();

    $target = $params['target'];
    $target = ($target == 'view') ? null : $target;
    unset($params['module']);
    unset($params['action']);
    unset($params['target']);
    unset($params['id']);
    
    $this->redirect(EntityTable::getInternalUrl($this->entity, $target, $params));
  }


  public function executePerson($request)
  {
    if ($id = $request->getParameter('id'))
    {
      //id given, we check that the id exists
      $this->checkEntity($request);
      $this->forward404Unless($this->entity['primary_ext'] == 'Person');
      
      //check that the entity has the given name
      $name = LsSlug::convertSlugToName($request->getParameter('slug'));
      
      //if the name isn't primary, we redirect to the url with the primary name
      if ($this->entity['name'] != $name)
      {
        $params = $request->getParameterHolder()->getAll();

        if ($params['target'] == 'view')
        {
          $params['target'] = null;
        }

        $url = EntityTable::generateRoute($this->entity, $params['target'], $params);
        $this->redirect($url);
      }
    }
    else
    {
      //id not given, so we search for the name
      $entities = EntityTable::getBySlug($request->getParameter('slug'), 'Person', $useAliases=true);
      
      switch (count($entities))
      {
        case 0:
          $this->forward404();
          break;

        case 1:          
          $this->entity = $entities[0];
          $request->setParameter('id', $this->entity->id);
          break;

        default:
          $request->setParameter('extension', 'Person');
          $this->forward('entity', 'disambiguation');
          break;
      }      
    }

    //add this entity to recently viewed list
    if ($this->getUser()->isAuthenticated())
    {
      $entityIds = (array) $this->getUser()->getAttribute('viewed_entity_ids');
      array_unshift($entityIds, $this->entity['id']);      
      $entityIds = array_merge(array_unique(array_slice($entityIds, 0, 10)));
      $this->getUser()->setAttribute('viewed_entity_ids', $entityIds);
    }

    $target = $request->getParameter('target', 'view');

    if ($target == 'veiw')
    {
      $this->setTemplate('viewSuccess');   
    }
    else
    {
      $this->forward('entity', $request->getParameter('target', 'view'));
    }
  }


  public function executeOrg($request)
  {
    if ($id = $request->getParameter('id'))
    {
      //id given, we check that the id exists
      $this->checkEntity($request);
      $this->forward404Unless($this->entity['primary_ext'] == 'Org');
      
      //check that the entity has the given name
      $name = LsSlug::convertSlugToName($request->getParameter('slug'));
      
      //if the name isn't primary, we redirect to the url with the primary name
      if ($this->entity->rawGet('name') != $name)
      {
        $params = $request->getParameterHolder()->getAll();

        if ($params['target'] == 'view')
        {
          $params['target'] = null;
        }

        $url = EntityTable::generateRoute($this->entity, $params['target'], $params);
        $this->redirect($url);
      }
    }
    else
    {
      $entities = EntityTable::getBySlug($request->getParameter('slug'), 'Org', $useAliases=true);
      
      switch ($q->count())
      {
        case 0:
          $this->forward404();
          break;
        case 1:
          $this->entity = $entities[0];
          $request->setParameter('id', $this->entity->id);
          break;
        default:
          $request->setParameter('extension', 'Org');
          $this->forward('entity', 'disambiguation');
          break;
      }      
    }
    
  
    $this->forward('entity', $request->getParameter('target', 'view'));
  }
  
  
  public function executeDisambiguation($request)
  {
    die("Disambiguation page here");
  }
  

  public function executeView($request)
  {
    $this->checkEntity($request);  

    if ($this->entity['primary_ext'] == 'Couple')
    {
      list($partner1_id, $partner2_id) = EntityTable::getPartnerIds($this->entity['id']);
      $this->entity['partner1'] = Doctrine::getTable('Entity')->find($partner1_id);
      $this->entity['partner2'] = Doctrine::getTable('Entity')->find($partner2_id);
    }

    $this->logRecentView();

    $this->tab_name = 'relationships';
  }
  
  public function executeEdit($request)
  {
    $id = $request->getParameter('id') ? $request->getParameter('id') : $request->getParameter('entity[id]');
    $this->entity = Doctrine::getTable('Entity')->find($id);
    $this->forward404Unless($this->entity);


    //get networks
    $this->all_network_ids = EntityTable::getNetworkIdsById($id);
    $this->submitted_network_ids = $this->all_network_ids;
    $homeNetworkId = $this->getUser()->getGuardUser()->Profile->home_network_id;

    $this->show_networks = true;
    
    if ($this->getUser()->hasCredential('admin'))
    {
      $permittedNetworkIds = LsListTable::getAllNetworkIds();
    }
    else
    {
      $permittedNetworkIds = array_unique(array(LsListTable::US_NETWORK_ID, $homeNetworkId));     
    }

    $this->permitted_networks = LsDoctrineQuery::create()
      ->from('LsList l')
      ->whereIn('l.id', $permittedNetworkIds)
      ->execute();    

    $otherNetworkIds = array_diff($this->all_network_ids, $permittedNetworkIds);
    $this->other_networks = !count($otherNetworkIds) ? array() : LsDoctrineQuery::create()
      ->from('LsList l')
      ->whereIn('l.id', $otherNetworkIds)
      ->execute();    


    //get extensions and definitions
    $primary = $this->entity->getPrimaryExtension();
    $this->tier2_defs = ExtensionDefinitionTable::getByTier(2, $primary);
    $this->tier3_defs = ExtensionDefinitionTable::getByTier(3, $primary);

    $this->entity_exts = $this->entity->getExtensions();
    $this->entity_exts_display = $this->entity->getExtensionsForDisplay(true);


    //create entity form
    $this->entity_form = new EntityForm($this->entity);


    //create primary extension form    
    $primaryFormClass = $primary . 'Form';
    $this->primary_ext_form = new $primaryFormClass($this->entity->getPrimaryExtensionObject(), null, false);
    
    
    //create all other extension forms
    $this->other_ext_forms = array();
    $exts = ExtensionDefinitionTable::getNamesByTier(array(2, 3), $primary, $havingFields=true);
    $objects = $this->entity->getExtensionObjects();
    
    foreach ($exts as $ext)
    {
      if ($ext != 'PublicCompany')
        continue;

      $class = $ext . 'Form';
      
      if (isset($objects[$ext]))
      {
        $object = $objects[$ext];
      }
      else
      {
        $object = new $ext;
      }
      
      $this->other_ext_forms[$ext] = new $class($object, null, false);
    }


    //create reference form
    $this->reference_form = new ReferenceForm;
    $this->reference_form->setSelectObject($this->entity);


    //form submission
    if ($request->isMethod('post'))
    {
      $this->entity_form->bind($request->getParameter('entity'));
      $this->primary_ext_form->bind($request->getParameter('entity'));

      $new_exts = array_keys((array) $request->getParameter('extensions'));
      $old_exts = $this->entity->getExtensions(true);
      $all_exts = array_merge($new_exts, $old_exts);

      foreach ($all_exts as $ext)
      {
        if ($ext != 'PublicCompany')
          continue;

        if (in_array($ext, ExtensionDefinitionTable::$extensionNamesWithFields))
        {
          $this->other_ext_forms[$ext]->bind($request->getParameter('entity'));
        }
      }

      $networkIds = $request->getParameter('network_ids', array());
      $this->submitted_network_ids = $networkIds;
  
      $this->reference_form->bind($request->getParameter('reference'));


      if ($this->entity_form->isValid() && $this->reference_form->isValid())
      {
        //only edit networks if user's home network is not the United States
        if ($this->show_networks)
        {
          //save network changes
          if (!$networkIds)
          {
            $request->setError('network_ids', 'You must select at least one network');
            return sfView::SUCCESS;
          }
  
          $networksToRemove = array_diff($this->all_network_ids, $networkIds);
          $networksToAdd = array_diff($networkIds, $this->all_network_ids);
          
          foreach ($networksToRemove as $networkId)
          {
            LsListEntity::removeByListIdAndEntityId($networkId, $this->entity['id']);
          }
          
          foreach ($networksToAdd as $networkId)
          {
            LsListEntity::addByListIdAndEntityId($networkId, $this->entity['id']);
          }
        }        

        //save extension changes
        $exts_to_add = array_diff($new_exts, $old_exts);
        $exts_to_remove = array_diff($old_exts, $new_exts);

        foreach ($exts_to_add as $ext)
        {
          $this->entity->addExtension($ext);
        }

        foreach ($exts_to_remove as $ext)
        {
          $this->entity->removeExtension($ext);
        }


        //save entity and reference
        $this->entity_form->updateObject();
        $this->entity->saveWithRequiredReference($request->getParameter('reference'));

        //clear cache
        $this->clearCache($this->entity);

        //redirect to view
        $this->redirect($this->entity->getInternalUrl());
      }
    }
  }
  
  
  public function executeRemove($request)
  {
    $this->checkEntity($request, false, false);   //need an object for all the deletion logic
  
    //get related ids for cache clearing
    $entityIds = EntityTable::getRelatedEntityIdsById($this->entity['id']);
    $listIds = EntityTable::getListIdsById($this->entity['id']);
  
    $this->entity->delete();

    $this->clearCache($this->entity);

    foreach ($entityIds as $entityId)
    {
      LsCache::clearEntityCacheById($entityId);
    }
    
    foreach ($listIds as $listId)
    {
      LsCache::clearListCacheById($listId);
    }
    
    $this->redirect('@homepage');
  }
  

  public function addEntity($request, $primary)
  {
    if (!in_array($primary, array('Person', 'Org')))
    {
      throw new Exception("Invalid primary extension: " . $primary);
    }

    
    $this->header = 'Add New ' . ($primary == 'Person' ? 'Person' : 'Organization');


    //create new entity and extension
    $this->entity = new Entity;
    $this->entity->addExtension($primary);
    

    //get extensions and definitions
    $this->tier2_defs = ExtensionDefinitionTable::getByTier(2, $primary);
    $this->tier3_defs = ExtensionDefinitionTable::getByTier(3, $primary);


    //create entity form
    $this->entity_form = new EntityForm($this->entity);


    //create primary extension form    
    $primaryFormClass = $primary . 'Form';
    $this->primary_ext_form = new $primaryFormClass($this->entity->getPrimaryExtensionObject(), null, false);
    
    
    //create all other extension forms
    $this->other_ext_forms = array();
    $exts = ExtensionDefinitionTable::getNamesByTier(array(2, 3), $primary, $havingFields=true);
    
    foreach ($exts as $ext)
    {
      $class = $ext . 'Form';
      $object = new $ext;
      
      $this->other_ext_forms[$ext] = new $class($object, null, false);
    }


    $this->reference_form = new ReferenceForm;

    
    //form submission
    if ($request->isMethod('post'))
    {
      $this->entity_form->bind($request->getParameter('entity'));
      $this->primary_ext_form->bind($request->getParameter('entity'));

      $new_exts = array_keys((array) $request->getParameter('extensions'));

      $this->reference_form->bind($request->getParameter('reference'));


      foreach ($exts as $ext)
      {
        $this->other_ext_forms[$ext]->bind($request->getParameter('entity'));
      }

      if ($this->entity_form->isValid() && $this->reference_form->isValid())
      {
        $db = Doctrine_Manager::connection();

        try
        {
          $db->beginTransaction();

          //save extensions
          foreach ($new_exts as $ext)
          {
            $this->entity->addExtension($ext);
          }
  
          //save entity and reference
          $this->entity_form->updateObject();
          $this->entity->saveWithRequiredReference($request->getParameter('reference'));
  
          //create primary alias
          $a = new Alias;
          $a->Entity = $this->entity;
          $a->name = $this->entity->name;
          $a->is_primary = true;
          $a->save();
          
          $db->commit();
        }
        catch (Exception $e)
        {
          $db->rollback();
          throw $e;
        }
          
        //redirect to view
        $this->redirect($this->entity->getInternalUrl());
      }
    }

    $this->setTemplate('addEntity');
  }

  
  public function executeAddPerson($request)
  {
    $this->entity_form = new EntityAddForm();
    $this->entity_form->setNameHelp('example: <em>Jesse L Jackson, Jr</em>');

    //show network options if user's home network is not the US
    $homeNetworkId = sfGuardUserTable::getHomeNetworkId();
    $networkIds = array_unique(array(LsListTable::US_NETWORK_ID, $homeNetworkId));

    if (count($networkIds) > 1)
    {
      $this->networks = LsDoctrineQuery::create()
        ->from('LsList l')
        ->whereIn('l.id', $networkIds)
        ->execute();    
    }

    $this->tier2_defs = ExtensionDefinitionTable::getByTier(2, 'Person');
    $this->tier3_defs = ExtensionDefinitionTable::getByTier(3, 'Person');

    if ($request->isMethod('post'))
    {
      $params = $request->getParameter('entity');
      $this->entity_form->bind($params);
      
      if ($this->entity_form->isValid())
      {
        $entity = PersonTable::parseFlatName($params['name']);
        
        if (!$entity->name_last)
        {
          $validatorSchema = $this->entity_form->getValidatorSchema();
          $this->entity_form->getErrorSchema()->addError(new sfValidatorError($validatorSchema['name'], 'invalid'), 'name');

          return sfView::SUCCESS;
        }

        //set blurb
        $entity->blurb = $params['blurb'];


        //add extensions
        $extensions = array();
  
        if ($request->getParameter('extensions'))
        {
          $extensions = array_keys($request->getParameter('extensions'));
        }
      
        $allowedExtensions = ExtensionDefinitionTable::getNamesByParent('Person');
        $extensions = array_intersect($extensions, $allowedExtensions);        

        foreach ($extensions as $extension)
        {
          $entity->addExtension($extension);
        }

        //get networks to add entity to
        $networkIds = $request->getParameter('network_ids', array(sfGuardUserTable::getHomeNetworkId()));

        //save and redirect to edit page
        $entity->save(null, true, $networkIds);
        
        $this->redirect($entity->getInternalUrl('edit'));
      }
    }
  }
  
  
  public function executeAddOrg($request)
  {
    $this->entity_form = new EntityAddForm;
    $this->entity_form->setNameHelp('example: <em>Goldman Sachs Group</em>');

    //show network options if user's home network is not the US
    $homeNetworkId = sfGuardUserTable::getHomeNetworkId();
    $networkIds = array_unique(array(LsListTable::US_NETWORK_ID, $homeNetworkId));

    if (count($networkIds) > 1)
    {
      $this->networks = LsDoctrineQuery::create()
        ->from('LsList l')
        ->whereIn('l.id', $networkIds)
        ->execute();    
    }
  
    $this->tier2_defs = ExtensionDefinitionTable::getByTier(2, 'Org');
    $this->tier3_defs = ExtensionDefinitionTable::getByTier(3, 'Org');

    if ($request->isMethod('post'))
    {
      $params = $request->getParameter('entity');
      $this->entity_form->bind($params);
      
      if ($this->entity_form->isValid())
      {
        //set name & blurb
        $entity = new Entity;
        $entity->addExtension('Org');
        $entity->name = $params['name'];
        $entity->blurb = $params['blurb'];


        //add extensions
        $extensions = array();
  
        if ($request->getParameter('extensions'))
        {
          $extensions = array_keys($request->getParameter('extensions'));
        }
      
        $allowedExtensions = ExtensionDefinitionTable::getNamesByParent('Org');
        $extensions = array_intersect($extensions, $allowedExtensions);        

        foreach ($extensions as $extension)
        {
          $entity->addExtension($extension);
        }

        //get networks to add entity to
        $networkIds = $request->getParameter('network_ids', array(sfGuardUserTable::getHomeNetworkId()));

        //save and redirect to edit page
        $entity->save(null, true, $networkIds);
        
        $this->redirect($entity->getInternalUrl('edit'));
      }
    }
  }
  
  
  public function executeMerge($request)
  {
    $this->checkEntity($request, false, false);
    
    
    //user has clicked the merge buttom
    if ($request->isMethod('post'))
    {
      $entityToKeep = Doctrine::getTable('Entity')->find($request->getParameter('keep_id'));
      $this->forward404Unless($entityToKeep);
      
      //get related entities for cache clearing
      $relatedEntities = EntityApi::getRelated($this->entity['id']);
      
      $mergedEntity = EntityTable::mergeAll($entityToKeep, $this->entity);

      $this->entity->setMerge(true);

      //clear relations so merged relations aren't deleted
      $this->entity->clearRelated();
      $this->entity->delete();

      $this->clearCache($this->entity);
      $this->clearCache($mergedEntity);

      foreach (array_keys($relatedEntities) as $relatedId)
      {
        LsCache::clearEntityCacheById($relatedId);
      }

      $this->getUser()->setFlash('alert', 'Succesfully merged.');
      $this->redirect($mergedEntity->getInternalUrl());    
    }


    $this->similar_entities = $this->entity->getSimilarEntitiesQuery($looseMatch=true)->execute();

    if ($request->hasParameter('q'))
    {

      $num = $request->getParameter('num', 10);
      $page = $request->getParameter('page', 1);
  
      //form submission, display matching persons
      if (!$terms = $request->getParameter('q'))
      {
        $this->match_pager = new LsDoctrinePager(array(), $page, $num);
      }
      else
      {
        switch (sfConfig::get('app_search_engine'))
        {
          case 'sphinx':
            $this->match_pager = EntityTable::getSphinxPager($terms, $page, $num);  
            break;
            
          case 'lucene':
            $ary = EntityTable::getLuceneArray($terms, null);
            $this->match_pager = new LsDoctrinePager($ary, $page, $num);
            break;
          
          case 'mysql':
          default:
            $terms = explode(' ', $terms);    
            $q = EntityTable::getSimpleSearchQuery($terms);
            $this->match_pager = new Doctrine_Pager($q, $page, $num);
            break;
        }
      }    
    }
  }


  public function executeRelationships($request)
  {
    $this->checkEntity($request);
    $this->rels = EntityApi::getRelated($this->entity['id'], array('sort' => 'relationship'));
  }


  public function pagerAction($request, $entity, $apiOptions, $title, $pointer, $includeTypes=null, $excludeTypes=null, $orderByAmount=false, $topLeadership=null)
  {
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);


    if (!is_null($topLeadership))
    {
      //get ids of board and execs
      $db = Doctrine_Manager::connection();
      $sql = 'SELECT DISTINCT r.entity1_id ' . 
             'FROM relationship r LEFT JOIN position p ON (p.relationship_id = r.id) ' .
             'WHERE r.entity2_id = ? AND r.category_id = 1 AND r.is_deleted = 0 ' .
             'AND (p.is_board = 1 OR p.is_executive = 1)';
      $stmt = $db->execute($sql, array($entity['id']));
      $topLeadershipIds = $stmt->fetchAll(PDO::FETCH_COLUMN);      
    }

		    
    if ($search = $request->getParameter('search'))
    {
    	$apiOptions['search'] = $search;
    }
    
    $rels = EntityApi::getRelated($entity['id'], $apiOptions);

    if ($includeTypes || $excludeTypes || !is_null($topLeadership))
    {
      $newRels = array();
    
      foreach ($rels as $id => $relatedEntity)
      {
        $types = explode(',', $relatedEntity['types']);

        //filter by required extensions
        if (isset($includeTypes) && $includeTypes && count(array_diff($includeTypes, $types)))
        {
          continue;
        }
        
        //filter by disallowed extensions
        if (isset($excludeTypes) && $excludeTypes && count(array_intersect($excludeTypes, $types)))
        {
          continue;
        }
        
        if (!is_null($topLeadership))
        {
          if ($topLeadership && !in_array($relatedEntity['id'], $topLeadershipIds))
          {
            continue;
          }
          
          if (!$topLeadership && in_array($relatedEntity['id'], $topLeadershipIds))
          {
            continue;
          }
        }
        
        $newRels[$id] = $relatedEntity;
      }

      $rels = $newRels;
    }
    
    if ($orderByAmount)
    {
      uasort($rels, array('EntityTable', 'relatedEntityAmountCmp'));
      $rels = array_reverse($rels, true);    
    }
    else
    {
      uasort($rels, array('EntityTable', 'relatedEntityCmp'));
      $rels = array_reverse($rels, true);
    }

    $this->pager = new LsDoctrinePager($rels, $page, $num);
    $this->title = $title;
    $this->pointer = $pointer;

    if ($request->isXmlHttpRequest())
    {
      $this->pager->setAjax(true);
      $this->pager->setAjaxUpdateId('relationship_tabs_content');
      $this->pager->setAjaxIndicatorId('indicator');
      $this->pager->setAjaxHash($this->getModuleName());

      $this->setLayout(false);
      return $this->renderPartial('relationshipSection');      
    }
    else
    {
      $this->relationship_pager = true;
      $this->setTemplate('view');
    }
  }


  public function executeFamily($request)
  {
    $this->checkEntity($request);
    $this->forward404Unless($this->entity['primary_ext'] == 'Person');

    $options = array(
      'cat_ids' => RelationshipTable::FAMILY_CATEGORY
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Family',
      'People in the same family as ' . $this->entity['name']
    );
  }


  public function executeFriends($request)
  {
    $this->checkEntity($request);
    $this->forward404Unless($this->entity['primary_ext'] == 'Person');

    $options = array(
      'cat_ids' => array(RelationshipTable::SOCIAL_CATEGORY, RelationshipTable::PROFESSIONAL_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Friends',
      'People with close social ties to ' . $this->entity['name']
    );
  }


  public function executeGovernment($request)
  {
    $this->checkEntity($request);
    $this->forward404Unless($this->entity['primary_ext'] == 'Person');

    $options = array(
      'order' => 1,
      'cat_ids' => array(RelationshipTable::POSITION_CATEGORY, RelationshipTable::MEMBERSHIP_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Government Positions',
      'Govt agencies ' . $this->entity['name'] . ' has served in',
      array('GovernmentBody')
    );
  }


  public function executeBusiness($request)
  {
    $this->checkEntity($request);
    $this->forward404Unless($this->entity['primary_ext'] == 'Person');

    $options = array(
      'order' => 1,
      'cat_ids' => array(RelationshipTable::POSITION_CATEGORY, RelationshipTable::MEMBERSHIP_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Business Positions',
      'Companies ' . $this->entity['name'] . ' has had a position in',
      array('Business')
    );
  }


  public function executeOtherPositions($request)
  {
    $this->checkEntity($request);
    $this->forward404Unless($this->entity['primary_ext'] == 'Person');

    $options = array(
      'order' => 1,
      'cat_ids' => array(RelationshipTable::POSITION_CATEGORY, RelationshipTable::MEMBERSHIP_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Other Positions & Memberships',
      'Positions & memberships ' . $this->entity['name'] . ' has had outside of business & govt agencies',
      null,
      array('GovernmentBody', 'Business', 'Person')
    );
  }
  

  public function executeEducation($request)
  {
    $this->checkEntity($request);
    $this->forward404Unless($this->entity['primary_ext'] == 'Person');

    $options = array(
      'order' => 1,
      'cat_ids' => array(RelationshipTable::EDUCATION_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Education',
      'Schools ' . $this->entity['name'] . ' has attended'
    );
  }
  
  
  public function executeStudents($request)
  {
    $this->checkEntity($request);
    $this->forward404Unless($this->entity['primary_ext'] == 'Org');

    $options = array(
      'order' => 2,
      'cat_ids' => array(RelationshipTable::EDUCATION_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Students',
      'People who have attended ' . $this->entity['name']
    );
  }  
  
  
  public function executeFundraising($request)
  {
    $this->checkEntity($request, false, false);
    $this->forward404Unless($this->entity->getPrimaryExtension() == 'Person');

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);
    
    $entity_relationships = $this->entity->getEntitiesWithRelationships(
      array(RelationshipTable::DONATION_CATEGORY),
      null,
      $orderBynum=false,
      $order=2
    );
    
    $fundraising_relationships = EntityTable::filterEntitiesWithRelationships(
      $entity_relationships,
      array(RelationshipTable::DONATION_CATEGORY),
      2,
      array('PoliticalFundraising')
    );
    
    $this->fundraising_pager = new LsDoctrinePager($fundraising_relationships, $page, $num);
    $this->fundraising_pager->setAjax(true);
    $this->fundraising_pager->setAjaxUpdateId('relationship_tabs_content');
    $this->fundraising_pager->setAjaxIndicatorId('indicator');
    $this->fundraising_pager->setAjaxHash('fundraising');
  }


  public function executePoliticalDonors($request)
  {
    $this->checkEntity($request, false, false);
    $this->forward404Unless($this->entity->getPrimaryExtension() == 'Person');

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);

    $this->entity_relationships = $this->entity->getEntitiesWithRelationships(
      array(RelationshipTable::DONATION_CATEGORY), 
      null, 
      $orderByNum=false,
      2
    );
    
    $this->political_donor_pager = new LsDoctrinePager(
      $this->entity->getIndirectDonorsQuery(null, 'PoliticalFundraising'),
      $page,
      $num
    );
    $this->political_donor_pager->setAjax(true);
    $this->political_donor_pager->setAjaxUpdateId('relationship_tabs_content');
    $this->political_donor_pager->setAjaxIndicatorId('indicator');
    $this->political_donor_pager->setAjaxHash('politicalDonors');


    $committeesAry = EntityTable::filterEntitiesWithRelationships(
      $this->entity_relationships,
      array(RelationshipTable::DONATION_CATEGORY),
      2,
      array('PoliticalFundraising'),
      null,
      null,
      false
    );
    
    $this->fundraising_committee_ids = array_keys($committeesAry);
  }  
  

  public function executeLeadership($request)
  {
    $this->checkEntity($request);
    $this->forward404Unless($this->entity['primary_ext'] == 'Org');

    $options = array(
      'order' => 2,
      'cat_ids' => array(RelationshipTable::POSITION_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Leadership & Staff',
      'People who have official positions in ' . $this->entity['name']
    );
  }


  public function executeBoard($request)
  {
    $this->checkEntity($request);
    $this->forward404Unless($this->entity['primary_ext'] == 'Org');

    $options = array(
      'order' => 2,
      'cat_ids' => array(RelationshipTable::POSITION_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Board Members and Executives',
      'Board members and executives in ' . $this->entity['name'],
      null,
      null,
      false,
      $topLeadership=true
    );
  }


  public function executeMembers($request)
  {
    $this->checkEntity($request);
    $this->forward404Unless($this->entity['primary_ext'] == 'Org');

    $options = array(
      'order' => 2,
      'cat_ids' => array(RelationshipTable::MEMBERSHIP_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Members',
      'Members of ' . $this->entity['name'] . ' without official positions'
    );
  }

  
  public function executeMemberships($request)
  {
    $this->checkEntity($request);
    $this->forward404Unless($this->entity['primary_ext'] == 'Org');

    $options = array(
      'order' => 1,
      'cat_ids' => array(RelationshipTable::MEMBERSHIP_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Memberships',
      $this->entity['name'] . ' belongs to these umbrella groups'
    );
  }


  public function executeOwners($request)
  {
    $this->checkEntity($request);
    $this->forward404Unless($this->entity['primary_ext'] == 'Org');

    $options = array(
      'order' => 2,
      'cat_ids' => array(RelationshipTable::OWNERSHIP_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Owners',
      'People and orgs with ownership in ' . $this->entity['name']
    );
  }
  

  public function executeHoldings($request)
  {
    $this->checkEntity($request);

    $options = array(
      'order' => 1,
      'cat_ids' => array(RelationshipTable::OWNERSHIP_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Holdings',
      'Orgs that ' . $this->entity['name'] . ' owns at least a piece of'
    );
  }
  

  public function executeTransactions($request)
  {
    $this->checkEntity($request);

    $options = array(
      'cat_ids' => array(RelationshipTable::TRANSACTION_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Services/Transactions',
      'People and orgs ' . $this->entity['name'] . ' has done business with',
      null,
      null,
      $orderByAmount=true
    );
  }
  
  
  public function executeDonors($request)
  {
    $this->checkEntity($request);

    $options = array(
      'order' => 2,
      'cat_ids' => array(RelationshipTable::DONATION_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Donors',
      'People and orgs who have donated to ' . $this->entity['name']  . 'directly',
      null,
      array('PoliticalFundraising'),
      $orderByAmount=true
    );
  }


  public function executeRecipients($request)
  {
    $this->checkEntity($request);

    $options = array(
      'order' => 1,
      'cat_ids' => array(RelationshipTable::DONATION_CATEGORY)
    );


    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Recipients',
      'People and orgs ' . $this->entity['name'] . ' has donated to directly',
      null,
      null,
      $orderByAmount=true
    );
  }


  public function executeLobbying($request)
  {
    $this->checkEntity($request, false, false);

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);
    
    $entity_relationships = $this->entity->getEntitiesWithRelationships(
      array(RelationshipTable::LOBBYING_CATEGORY),
      null,
      $orderBynum=false
    );
    
    $lobbying_relationships = EntityTable::filterEntitiesWithRelationships(
      $entity_relationships,
      array(RelationshipTable::LOBBYING_CATEGORY)
    );
    
    $this->lobbying_pager = new LsDoctrinePager($lobbying_relationships, $page, $num);
    $this->lobbying_pager->setAjax(true);
    $this->lobbying_pager->setAjaxUpdateId('relationship_tabs_content');
    $this->lobbying_pager->setAjaxIndicatorId('indicator');
    $this->lobbying_pager->setAjaxHash('lobbying');
  }


  public function executeLobbiedBy($request)
  {
    $this->checkEntity($request);

    $options = array(
      'order' => 2,
      'cat_ids' => array(RelationshipTable::LOBBYING_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Lobbied By',
      $this->entity['name'] . ' has been lobbied by:',
      null,
      null,
      $orderByAmount=true
    );
  }


  public function executeLobbyingTargets($request)
  {
    $this->checkEntity($request);

    $options = array(
      'order' => 1,
      'cat_ids' => array(RelationshipTable::LOBBYING_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Lobbying Targets',
      'Officials and agencies ' . $this->entity['name'] . ' has lobbied',
      null,
      null,
      $orderByAmount=true
    );
  }


  public function executeOffice($request)
  {
    $this->checkEntity($request);
    $this->forward404Unless($this->entity['primary_ext'] == 'Person');

    $options = array(
      'order' => 2,
      'cat_ids' => array(RelationshipTable::POSITION_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Office/Staff',
      'People who have worked for ' . $this->entity['name'] . ' directly'
    );
  }


  public function executeOfficeOf($request)
  {
    $this->checkEntity($request);
    $this->forward404Unless($this->entity['primary_ext'] == 'Person');

    $options = array(
      'order' => 1,
      'cat_ids' => array(RelationshipTable::POSITION_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'In the Office Of',
      'People ' . $this->entity['name'] . ' has worked for directly',
      array('Person')
    );
  }
  
  public function executeChildOrgs($request)
  {
    $this->checkEntity($request);
    $this->forward404Unless($this->entity['primary_ext'] == 'Org');

    $options = array(
      'order' => 2,
      'cat_ids' => array(RelationshipTable::HIERARCHY_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Child Organizations',
      'Organizations that are a part of ' . $this->entity['name'],
      array('Org')
    );
  }
 
  public function executeParentOrgs($request)
  {
    $this->checkEntity($request);
    $this->forward404Unless($this->entity['primary_ext'] == 'Org');

    $options = array(
      'order' => 1,
      'cat_ids' => array(RelationshipTable::HIERARCHY_CATEGORY)
    );

    return $this->pagerAction(
      $request,
      $this->entity,
      $options,
      'Parent Organizations',
      'Organizations that ' . $this->entity['name'] . ' are a part of',
      array('Org')
    );
  }

  public function executeUploadImage($request)
  {
    $this->checkEntity($request);

    $params = $request->getParameter('image');
    $this->upload_form = new ImageUploadForm();  
    $this->has_image = EntityTable::hasProfileImage($this->entity);  

    if ($request->isMethod('post'))
    {
      $db = Doctrine_Manager::connection();

      $this->upload_form->bind($params, $request->getFiles('image'));
      
      if ($this->upload_form->isValid())
      {
        try
        {
          $db->beginTransaction();
  

          $files = $request->getFiles('image');

          //set filename and path based on upload type
          if (isset($files['file']['size']) && $files['file']['size'])
          {
            $path = $request->getFilePath('image');
            $path = $path['file'];
            $originalFilename = $request->getFileName('image');
            $originalFilename = $originalFilename['file'];
          }
          else
          {
            $path = $params['url'];
            $pathParts = explode('?', basename($path));
            $originalFilename = $pathParts[0];
          }
  
  
          //if image files can't be created, assume remote url was bad
          if (!$filename = ImageTable::createFiles($path, $originalFilename))
          {
            $validatorSchema = $this->upload_form->getValidatorSchema();
            $this->upload_form->getErrorSchema()->addError(new sfValidatorError($validatorSchema['url'], 'invalid'));

            return sfView::SUCCESS;
          }
  
          //create image
          $image = new Image;
          $image->entity_id = $this->entity['id'];
          $image->filename = $filename;
          $image->title = $params['title'];
          $image->caption = $params['caption'];
          $image->url = $params['url'];

          if (!$this->has_image)
          {
            $image->is_featured = true;
          }
          elseif (isset($params['is_featured']))
          {
            $db = Doctrine_Manager::connection();
            $sql = 'UPDATE image SET is_featured = 0 WHERE entity_id = ?';
            $stmt = $db->execute($sql, array($this->entity['id']));

            $image->is_featured = true;            
          }
          else
          {
            $image->is_featured = 0;
          }
                    
          $image->is_free = isset($params['is_free']) ? true : null;
          $image->save();
  
  
          //if featured, unfeature any other images
          if (isset($params['featured']) && $profileImage = EntityTable::getProfileImageById($entity))
          {
            $profileImage->is_featured = false;
            $profileImage->save();
          }
          
          $db->commit();
        }
        catch (Exception $e)
        {
          $db->rollback();
          throw $e;
        }

        $this->clearCache($this->entity);
  
        $this->redirect($image->url ? EntityTable::getInternalUrl($this->entity, 'images') : 'entity/image?id=' . $image->id);
      }
    }    
  }
  
  
  public function executeImage($request)
  {
    $this->image = Doctrine::getTable('Image')->find($request->getParameter('id'));
    $this->forward404Unless($this->image);
    
    $this->entity = $this->image->Entity;
  }


  public function executeCropImage($request)
  {
    $imageId = $request->getParameter('id');
    $this->redirect("http://" . $_SERVER['HTTP_HOST'] . "/images/" . $imageId . "/crop");
  }
  
  
  public function executeImages($request)
  {
    $this->checkEntity($request, false, false);

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 10);
    
    $q = LsDoctrineQuery::create()
      ->from('Image i')
      ->where('i.entity_id = ?', $this->entity->id)
      ->orderBy('i.updated_at DESC');
      
    $this->image_pager = new LsDoctrinePager($q, $page, $num);  
  }


  public function executeEditImage($request)
  {
    $params = $request->getParameter('image');
    $this->image = Doctrine::getTable('Image')->find($request->getParameter('id'));
    $this->forward404Unless($this->image);
    
    $this->entity = $this->image->Entity;
    $this->edit_form = new ImageEditForm($this->image);
    
    if ($request->isMethod('post'))
    {
      $this->edit_form->bind($params);
      
      if ($this->edit_form->isValid())
      {  
        $db = Doctrine_Manager::connection();

        try
        {
          $db->beginTransaction();

          $this->image->title = $params['title'];
          $this->image->caption = $params['caption'];
          $this->image->is_free = isset($params['is_free']) ? true : false;
                  
          if (isset($params['is_featured']))
          {
            if ($featuredImage = $this->entity->getProfileImage())
            {
              $featuredImage->is_featured = false;
              $featuredImage->save();
            }
            
            $this->image->is_featured = true;
            
            $this->clearCache($this->entity);
          }
  
          $this->image->save();
    
          $db->commit();
        }
        catch (Exception $e)
        {
          $db->rollback();
          throw $e;
        }
    
        $this->redirect($this->entity->getInternalUrl('images'));
      }
    }              
  }

  
  public function executeRemoveImage($request)
  {
    $this->image = Doctrine::getTable('Image')->find($request->getParameter('id'));
    $this->forward404Unless($this->image);
    
    if ($request->isMethod('post'))
    {
      $entity = $this->image->Entity;
      
      if ($this->image->is_featured)
      {
        $q = LsDoctrineQuery::create()
          ->from('Image i')
          ->where('i.entity_id = ?', $entity->id)
          ->andWhere('i.id <> ?', $this->image->id);
          
        if ($newFeatured = $q->fetchOne())
        {
          $newFeatured->is_featured = true;
          $newFeatured->save();
        }
        
        $this->clearCache($entity);
      }
      
      $this->image->delete();
      
      $this->redirect($entity->getInternalUrl());
    }
  }
  
  
  public function executeFeatureImage($request)
  {
    $this->image = Doctrine::getTable('Image')->find($request->getParameter('id'));
    $this->forward404Unless($this->image);
    
    $entity = $this->image->Entity;    
    $db = Doctrine_Manager::connection();

    try
    {
      $db->beginTransaction();  

      if ($image = $entity->getProfileImage())
      {
        $image->is_featured = null;
        $image->save();
      }

      $this->image->is_featured = true;
      $this->image->save();
      
      $db->commit();
    }
    catch (Exception $e)
    {
      $db->rollback();
      throw $e;
    }
    
    $this->clearCache($entity);
    
    $this->redirect($entity->getInternalUrl('images'));
  }
  
  
  public function executeEditContact($request)
  {
    $this->checkEntity($request, false, false);

    if ($this->entity['primary_ext'] == 'Person')
		{
			$permitted = $this->getUser()->hasCredential('contacter') || $this->getUser()->hasCredential('admin');
			$this->forward404Unless($permitted);
		}
  }
  
  
  public function executeAddAddress($request)
  {
    $this->checkEntity($request, false, false);
    
    $this->address_form = new AddressForm;
    $this->reference_form = new ReferenceForm;
    $this->reference_form->setSelectObject($this->entity,false,true);
    
    if ($request->isMethod('post'))
    {
      $params = $request->getParameter('address');
      $refParams = $request->getParameter('reference');

      $this->address_form->bind($params);
      $this->reference_form->bind($refParams);
      
      
      if ($this->address_form->isValid() && $this->reference_form->isValid())
      {
        $this->address_form->updateObject();
        $address = $this->address_form->getObject();
        
        if ($address = $this->entity->addAddress($address))
        {
          //save entity and reference
          $address->saveWithRequiredReference($refParams);
          
          $this->clearCache($this->entity);

          $this->redirect('entity/address?id=' . $address->id);
        }
        else
        {
          $validator = new sfValidatorString(array(), array(
            'invalid' => 'This ' . strtolower($this->entity->getPrimaryExtension()) . ' already has an address with the same coordinates'
          ));
          $this->address_form->getErrorSchema()->addError(new sfValidatorError($validator, 'invalid'));
        }
      }
    }
  }


  public function executeAddresses($request)
  {
    $this->checkEntity($request);

    $this->addresses = LsDoctrineQuery::create()
      ->from('Address a')
      ->leftJoin('a.State s')
      ->where('a.entity_id = ?', $this->entity['id'])
      ->andWhere('a.is_deleted = 0')
      ->orderBy('s.abbreviation, a.city, a.postal')
      ->execute();
  }
  
  
  public function executeAddress($request)
  {
    $this->address = Doctrine::getTable('Address')->find($request->getParameter('id'));
    $this->forward404Unless($this->address);
    $this->entity = $this->address->Entity;
    $this->forward404Unless($this->entity);
    
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);
    
    if ($this->address->latitude && $this->address->longitude)
    {
      $q = LsDoctrineQuery::create()
        ->from('Entity e')
        ->leftJoin('e.Address a')
        ->andWhere('a.latitude = ? AND a.longitude = ?', array($this->address->latitude, $this->address->longitude))
        ->andWhere('e.id <> ?', $this->entity->id)
        ->setHydrationMode(Doctrine::HYDRATE_ARRAY);
        
      $this->neighbors_pager = new LsDoctrinePager($q, $page, $num);
      
      $this->nearby_address_pager = new LsDoctrinePager(
        $this->address->getNearbyAddressesQuery(0.1, true),
        $page,
        $num
      );
      
    }
  }

  
  public function executeEditAddress($request)
  {
    $this->address = Doctrine::getTable('Address')->find($request->getParameter('id'));
    $this->forward404Unless($this->address);
    $this->entity = $this->address->Entity;
    $this->forward404Unless($this->entity);
        
    $this->address_form = new AddressForm($this->address);
    $this->reference_form = new ReferenceForm;
    $this->reference_form->setSelectObject($this->entity,false,true);


    if ($request->isMethod('post'))
    {
      $params = $request->getParameter('address');
      $refParams = $request->getParameter('reference');
      
      $this->address_form->bind($params);
      $this->reference_form->bind($refParams);

      
      if ($this->address_form->isValid() && $this->reference_form->isValid())
      {
        $this->address_form->updateObject();
        $address = $this->address_form->getObject();

        //standardize
        $address = AddressTable::standardize($address);

        //make sure edited address isn't a duplicate
        if ($address->latitude && $address->longitude)
        {
          $q = AddressTable::getByCoordsQuery($address->longitude, $address->latitude, $this->entity);
          $q->addWhere('a.entity_id <> ?', $this->entity->id);
          
          if (!$q->count())
          {
            //save address and reference
            $address->saveWithRequiredReference($refParams);
            
            $this->clearCache($this->entity);
            
            $this->redirect('entity/address?id=' . $address->id);            
          }
          else
          {
            $validator = new sfValidatorString(array(), array(
              'invalid' => 'This ' . strtolower($this->entity->getPrimaryExtension()) . ' already has an address with the same coordinates'
            ));
            $this->address_form->getErrorSchema()->addError(new sfValidatorError($validator, 'invalid'));            
          }
        }
        else
        {
          $address->save();

          $this->clearCache($this->entity);

          $this->redirect('entity/address?id=' . $address->id);
        }
      }
    }
  }
  
  
  public function executeRemoveAddress($request)
  {
    $this->address = Doctrine::getTable('Address')->find($request->getParameter('id'));
    $this->forward404Unless($this->address);
    $this->entity = $this->address->Entity;
    $this->forward404Unless($this->entity);

    if ($request->isMethod('post'))
    {
      $this->address->delete();
      
      $this->clearCache($this->entity);
      
      $this->redirect($this->entity->getInternalUrl('editContact'));
    }
  }
  
  
  public function executeAddPhone($request)
  {
    $this->checkEntity($request, false, false);
     
    $this->phone_form = new PhoneForm(new Phone);
    $this->reference_form = new ReferenceForm;
    $this->reference_form->setSelectObject($this->entity,false,true);

    if ($request->isMethod('post'))
    {
      $params = $request->getParameter('phone');
      $refParams = $request->getParameter('reference');

      $this->phone_form->bind($params);
      $this->reference_form->bind($refParams);
      
      if ($this->phone_form->isValid() && $this->reference_form->isValid())
      {
        $this->phone_form->updateObject();
        $phone = $this->phone_form->getObject();
        
        if ($phone = $this->entity->addPhone($phone->number))
        {
          //save phone and reference
          $phone->saveWithRequiredReference($refParams);

          $this->clearCache($this->entity);

          $this->redirect($this->entity->getInternalUrl('editContact'));
        }
        else
        {
          $validator = new sfValidatorString(array(), array(
            'invalid' => 'This ' . strtolower($this->entity->getPrimaryExtension()) . ' already has that number'
          ));
          $this->phone_form->getErrorSchema()->addError(new sfValidatorError($validator, 'invalid'));
        }
      }
    }  
  }
  
  
  public function executeEditPhone($request)
  {
    $this->phone = Doctrine::getTable('Phone')->find($request->getParameter('id'));
    $this->forward404Unless($this->phone);
    $this->entity = $this->phone->Entity;
    $this->forward404Unless($this->entity);
        
    $this->phone_form = new PhoneForm($this->phone);
    $this->reference_form = new ReferenceForm;
    $this->reference_form->setSelectObject($this->entity,false,true);


    if ($request->isMethod('post'))
    {
      $params = $request->getParameter('phone');
      $refParams = $request->getParameter('reference');
      
      $this->phone_form->bind($params);
      $this->reference_form->bind($refParams);
      
      if ($this->phone_form->isValid() && $this->reference_form->isValid())
      {
        //make sure number is unique
        $unique = true;
        $cleanNumber = PhoneTable::formatForDb($params['number']);

        foreach ($this->entity->Phone as $existingPhone)
        {
          if ($existingPhone->id != $this->phone->id && $existingPhone->number == $cleanNumber)
          {
            $unique = false;
            break;
          }        
        }
        
        if ($unique)
        {
          $this->phone->number = $cleanNumber;
          $this->phone->type = $params['type'];

          //save phone and reference
          $this->phone->saveWithRequiredReference($refParams);
          
          $this->clearCache($this->entity);
          
          $this->redirect($this->entity->getInternalUrl('editContact'));
        }
        else
        {
          $validator = new sfValidatorString(array(), array(
            'invalid' => 'This ' . strtolower($this->entity->getPrimaryExtension()) . ' already has that ' . $phone->type . ' number'
          ));
          $this->phone_form->getErrorSchema()->addError(new sfValidatorError($validator, 'invalid'));        
        }
      }  
    }
  }
  
  
  public function executeRemovePhone($request)
  {
    $this->phone = Doctrine::getTable('Phone')->find($request->getParameter('id'));
    $this->forward404Unless($this->phone);
    $this->entity = $this->phone->Entity;
    $this->forward404Unless($this->entity);
  
    if ($request->isMethod('post'))
    {
      $this->phone->delete();
      
      $this->clearCache($this->entity);
      
      $this->redirect($this->entity->getInternalUrl('editContact'));
    }
  }


  public function executeAddEmail($request)
  {
    $this->checkEntity($request, false, false);
     
    $this->email_form = new EmailForm(new Email);
    $this->reference_form = new ReferenceForm;
    $this->reference_form->setSelectObject($this->entity,false,true);
    

    if ($request->isMethod('post'))
    {
      $params = $request->getParameter('email');
      $refParams = $request->getParameter('reference');

      $this->email_form->bind($params);
      $this->reference_form->bind($refParams);
      
      
      if ($this->email_form->isValid() && $this->reference_form->isValid())
      {
        $this->email_form->updateObject();
        $email = $this->email_form->getObject();
        
        if ($email = $this->entity->addEmail($params['address']))
        {
          $email->saveWithRequiredReference($refParams);

          $this->clearCache($this->entity);
          
          $this->redirect($this->entity->getInternalUrl('editContact'));
        }
        else
        {
          $validator = new sfValidatorString(array(), array(
            'invalid' => 'This ' . strtolower($this->entity->getPrimaryExtension()) . ' already has that email'
          ));
          $this->email_form->getErrorSchema()->addError(new sfValidatorError($validator, 'invalid'));
        }
      }
    }  
  }
  
  
  public function executeEditEmail($request)
  {
    $this->email = Doctrine::getTable('Email')->find($request->getParameter('id'));
    $this->forward404Unless($this->email);
    $this->entity = $this->email->Entity;
    $this->forward404Unless($this->entity);
        
    $this->email_form = new EmailForm($this->email);
    $this->reference_form = new ReferenceForm;
    $this->reference_form->setSelectObject($this->entity,false,true);


    if ($request->isMethod('post'))
    {
      $params = $request->getParameter('email');
      $refParams = $request->getParameter('reference');
      
      $this->email_form->bind($params);
      $this->reference_form->bind($refParams);

      
      if ($this->email_form->isValid() && $this->reference_form->isValid())
      {
        //make sure number is unique
        $unique = true;

        foreach ($this->entity->Email as $existingEmail)
        {
          if ($existingEmail->id != $this->email->id && $existingEmail->address == $params['address'])
          {
            $unique = false;
            break;
          }        
        }
        
        if ($unique)
        {
          $this->email->address = $params['address'];

          $this->email->saveWithRequiredReference($refParams);

          $this->clearCache($this->entity);
          
          $this->redirect($this->entity->getInternalUrl('editContact'));
        }
        else
        {
          $validator = new sfValidatorString(array(), array(
            'invalid' => 'This ' . strtolower($this->entity->getPrimaryExtension()) . ' already has that ' . $email->type . ' address'
          ));
          $this->email_form->getErrorSchema()->addError(new sfValidatorError($validator, 'invalid'));        
        }
      }  
    }
  }
  
  
  public function executeRemoveEmail($request)
  {
    $this->email = Doctrine::getTable('Email')->find($request->getParameter('id'));
    $this->forward404Unless($this->email);
    $this->entity = $this->email->Entity;
    $this->forward404Unless($this->entity);
  
    if ($request->isMethod('post'))
    {
      $this->email->delete();

      $this->clearCache($this->entity);
      
      $this->redirect($this->entity->getInternalUrl('editContact'));
    }
  }
  
  
  public function executeInterlocks($request)
  {
    $this->checkEntity($request);
    $this->logRecentView();

    $this->page = $request->getParameter('page', 1);
    $this->num = $request->getParameter('num', 20);
    $this->mapId = $request->getParameter('map_id', null);

    if ($request->isXmlHttpRequest())
    {
      return $this->renderComponent('entity', 'interlocks', array(
        'entity' => $this->entity,
        'page' => $this->page,
        'num' => $this->num,
        'mapId' => $this->mapId
      ));
    }

    $this->tab_name = 'interlocks';

    $this->setTemplate('view');
  }
  
  
  public function executeSchools($request)
  {
    $this->checkEntity($request);

    if ($this->entity['primary_ext'] != 'Org')
    {
      $this->forward('error', 'invalid');
    }

    $this->logRecentView();

    $this->page = $request->getParameter('page', 1);
    $this->num = $request->getParameter('num', 10);

    if ($request->isXmlHttpRequest())
    {
      return $this->renderComponent('entity', 'schools', array(
        'entity' => $this->entity,
        'page' => $this->page,
        'num' => $this->num
      ));
    }

    $this->tab_name = 'schools';

    $this->setTemplate('view');
  }
  

  public function executeNetworkSearch($request)
  {
    $this->checkEntity($request);

    //need to escape vulnerable params before calling API
    LsApiRequestFilter::escapeParameters();

    $this->page = $request->getParameter('page', 1);
    $this->num = $request->getParameter('num', 20);

    $this->order1 = $request->getParameter('order1');
    $this->order2 = $request->getParameter('order2');
    
    $this->past1 = $request->getParameter('past1');
    $this->past2 = $request->getParameter('past2');

    $this->cat1_ids = implode(',', $request->getParameter('cat1_ids', array()));
    $this->cat2_ids = implode(',', $request->getParameter('cat2_ids', array()));
    
    $this->ext2_ids = implode(',', $request->getParameter('ext2_ids', array()));

    $options = array(
      'cat1_ids' => $this->cat1_ids,
      'order1' => $this->order1,
      'cat2_ids' => $this->cat2_ids,
      'ext2_ids' => $this->ext2_ids,
      'past1' => $this->past1,
      'past2' => $this->past2,
      'order2' => $this->order2,
      'page' => $this->page,
      'num' => $this->num
    );

    //get categories per form
    $this->categories = LsDoctrineQuery::create()
      ->select('c.id, c.name')
      ->from('RelationshipCategory c')
      ->orderBy('c.id')
      ->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $this->extensions = array();
    $this->extensions['primary'] = ExtensionDefinitionTable::getByTier(1);
    $this->extensions['person'] = ExtensionDefinitionTable::getByTier(array(2,3), "Person");
    $this->extensions['org'] = ExtensionDefinitionTable::getByTier(array(2,3), "Org");

    if ($request->getParameter('commit') == 'Search')
    {    
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
  }

  
  public function executeGiving($request)
  {
    $this->checkEntity($request);
    $this->logRecentView();

    $this->page = $request->getParameter('page', 1);
    $this->num = $request->getParameter('num', 10);

    if ($request->isXmlHttpRequest())
    {
      return $this->renderComponent('entity', 'giving', array(
        'entity' => $this->entity,
        'page' => $this->page,
        'num' => $this->num
      ));
    }

    $this->tab_name = 'giving';

    $this->setTemplate('view');
  }
  

  public function executeFunding($request)
  {
    $this->checkEntity($request, false, false);
    
    if ($this->entity->hasExtension('PoliticalFundraising'))
    {
      $page = $request->getParameter('page', 1);
      $num = $request->getParameter('num', 10);

      $q = $this->entity->getDonorRecipientsQuery(array('PoliticalFundraising'));
      $this->recipient_pager = new LsDoctrinePager($q, $page, $num);

      $this->recipient_pager->setAjax(true);
      $this->recipient_pager->setAjaxUpdateId('relationship_tabs_content');
      $this->recipient_pager->setAjaxIndicatorId('indicator');
      $this->recipient_pager->setAjaxHash('funding');
    }
  }

  
  public function executeReferences($request)
  {
    $this->checkEntity($request);

    $request->setParameter('model', 'Entity');
    $request->setParameter('id', $this->entity['id']);

    $this->forward('reference', 'list');
  }


  public function executeAddRelationship($request)
  {
    $this->checkEntity($request, false, false);


    //for entity creation form, show network options if user's home network is not the US
    $homeNetworkId = sfGuardUserTable::getHomeNetworkId();
    $networkIds = array_unique(array(LsListTable::US_NETWORK_ID, $homeNetworkId));

    if (count($networkIds) > 1)
    {
      $this->networks = LsDoctrineQuery::create()
        ->from('LsList l')
        ->whereIn('l.id', $networkIds)
        ->execute();    
    }
    

    if ($request->isMethod('post'))
    {
      $primary = $request->getParameter('primary');
      $primary = $primary[0];
      $name = $request->getParameter('name');

      if (!$name)
      {
        $request->setError('name', 'You must enter a name');
      }

      if ($primary == 'Person')
      {
        $entity = PersonTable::parseFlatName($name);        

        if ($name && !$entity->name_last)
        {
          $request->setError('name', 'The name you entered is invalid');
        }
      }
      elseif ($primary == 'Org')
      {
        $entity = new Entity;
        $entity->addExtension('Org');
        $entity->name = $name;
      }
      else
      {
        $request->setError('primary', 'You must select a type');
      }
      
      if (!$request->hasErrors())
      {
        //set blurb
        $entity->blurb = $request->getParameter('blurb');


        //add extensions
        $extensions = array();
  
        if ($request->getParameter('extensions'))
        {
          $extensions = array_keys($request->getParameter('extensions'));
        }
      
        $allowedExtensions = ExtensionDefinitionTable::getNamesByParent($primary);
        $extensions = array_intersect($extensions, $allowedExtensions);        

        foreach ($extensions as $extension)
        {
          $entity->addExtension($extension);
        }


        //get networks to add entity to
        $networkIds = $request->getParameter('network_ids', array(sfGuardUserTable::getHomeNetworkId()));

        //save and redirect to edit page
        $entity->save(null, true, $networkIds);
        
        $this->redirect($this->entity->getInternalUrl('addRelationshipCategory', array('entity2_id' => $entity->id)));
      }
    }


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


      $this->primary_exts = array('Person', 'Org');
      $this->tier2_defs = array();
      $this->tier3_defs = array();
        
      foreach ($this->primary_exts as $primaryExt)
      {
        $this->tier2_defs[$primaryExt] = ExtensionDefinitionTable::getByTier(2, $primaryExt);
        $this->tier3_defs[$primaryExt] = ExtensionDefinitionTable::getByTier(3, $primaryExt);
      }
    }
  }


  public function executeAddRelationshipCategory($request)
  {
    $this->checkEntity($request, false, false);
    
    $this->entity2 = Doctrine::getTable('Entity')->find($request->getParameter('entity2_id'));
    $this->forward404Unless($this->entity2);

    $ext1 = $this->entity->getPrimaryExtension();
    $ext2 = $this->entity2->getPrimaryExtension();
    
    
    $this->categories = RelationshipCategoryTable::getByExtensionsQuery($ext1, $ext2)->execute();  

    $this->reference_form = new ReferenceForm;
    $rel = new Relationship;
    $rel->Entity1 = $this->entity;
    $rel->Entity2 = $this->entity2;
    $this->reference_form->setSelectObject($rel);
    
    
    if ($request->isMethod('post'))
    {
      $refParams = $request->getParameter('reference');
      $this->reference_form->bind($refParams);


      if ($this->reference_form->isValid())
      {
        //lobbying will need to be handled seperately
        if ($request->getParameter('category_id') == RelationshipTable::LOBBYING_CATEGORY)
        {
          switch ($request->getParameter('lobbying_scenario'))
          {
            case 'direct':
              $r = new Relationship;
              $r->Entity1 = $this->entity;
              $r->Entity2 = $this->entity2;
              $r->setCategory('Lobbying');              
              break;
              
            case 'direct_reverse':
              $r = new Relationship;
              $r->Entity1 = $this->entity2;
              $r->Entity2 = $this->entity;
              $r->setCategory('Lobbying');              
              break;
            
            case 'service':
              $r = new Relationship;
              $r->Entity1 = $this->entity;
              $r->Entity2 = $this->entity2;
              $r->setCategory('Transaction');
              $r->description1 = 'Lobbying Client';
              $r->description2 = $this->entity2['primary_ext'] == 'Org' ? 'Lobbying Firm' : 'Lobbyist';
              break;
              
            case 'service_reverse':
              $r = new Relationship;
              $r->Entity1 = $this->entity2;
              $r->Entity2 = $this->entity;
              $r->setCategory('Transaction');
              $r->description1 = 'Lobbying Client';
              $r->description2 = $this->entity['primary_ext'] == 'Org' ? 'Lobbying Firm' : 'Lobbyist';
              break;
              
            default:
              $request->setError('lobbying_scenario', "You must pick a type of lobbying relationship to create");
              return sfView::SUCCESS;
              break;            
          }

          $r->saveWithRequiredReference($refParams);

          LsCache::clearRelationshipCacheById($r->id);

          $this->redirect($r->getInternalUrl('edit', array('ref' => 'auto')));
        }


        if ($category = Doctrine::getTable('RelationshipCategory')->find($request->getParameter('category_id')))
        {
          //save relationship and reference
          $r = new Relationship;
          $r->Entity1 = $this->entity;
          $r->Entity2 = $this->entity2;
          $r->setCategory($category->name);

          $r->switchEntityOrderIfNecessary();

          $r->saveWithRequiredReference($refParams);

          LsCache::clearRelationshipCacheById($r->id);
            
          //redirect to edit page
          $this->redirect($r->getInternalUrl('edit', array('ref' => 'auto')));
        }
        else
        {
          $request->setError('category_id', "You must select a category");
        }
      }
      else
      {
        if (!$categoryId = $request->getParameter('category_id'))
        {
          $request->setError('category_id', "You must select a category");        
        }
      }
    }
  }
  
  
  public function executeModifications($request)
  {
    $this->checkEntity($request, $includeDeleted=true, false);
    
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);
    $userId = $request->getParameter('user_id');

    if ($request->getParameter('group_by_user'))
    {
      $q = $this->entity->getModificationsQuery()
        ->addWhere('NOT EXISTS( SELECT id FROM modification WHERE modification.user_id = m.user_id AND modification.created_at > m.created_at )');
      
      $this->modification_pager = new LsDoctrinePager($q, $page, $num);    
    }
    else
    {
      $this->modification_pager = new LsDoctrinePager(
        $this->entity->getModificationsQuery($userId)->setHydrationMode(Doctrine::HYDRATE_ARRAY),
        $page,
        $num
      );
    }    
  }
  
  public function executeTypeModifications($request)
  {
    $this->checkEntity($request, $includeDeleted=true, false);
    
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);
 
    $q = LsDoctrineQuery::create()
      ->from('Modification m')
      ->leftJoin('m.Field f')
      ->where('m.object_model = ?', 'ExtensionRecord')
      ->addWhere('f.field_name = ?', 'definition_id')
      ->addWhere('(m.is_create = ? AND EXISTS( SELECT id FROM modification_field mf WHERE mf.modification_id = m.id AND mf.field_name = ? AND mf.new_value = ? )) OR (m.is_delete = ? AND EXISTS( SELECT id FROM modification_field mf WHERE mf.modification_id = m.id AND mf.field_name = ? AND mf.old_value = ? ))', array(true, 'entity_id', $this->entity->id, true, 'entity_id', $this->entity->id))
      ->orderBy('m.id DESC');

    $this->ext_modifications = $q->execute();
  }


  public function executeContactModifications($request)
  {
    $this->checkEntity($request, $includeDeleted=true, false);

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);
    
    $this->addresses = LsDoctrineQuery::create()
      ->from('Address a')
      ->where('a.entity_id = ? AND a.is_deleted IS NOT NULL', $this->entity->id)
      ->orderBy('a.updated_at DESC')
      ->execute();
    
    $q = LsDoctrineQuery::create()
      ->from('Modification m')
      ->leftJoin('m.User u')
      ->leftJoin('m.Field f')
      ->where('f.field_name <> ?', 'entity_id')
      ->addWhere('(m.object_model = ? AND EXISTS( SELECT id FROM phone p WHERE p.id = m.object_id AND p.entity_id = ?) OR (m.object_model = ? AND EXISTS( SELECT id FROM email e WHERE e.id = m.object_id AND e.entity_id = ?)))', array('Phone', $this->entity->id, 'Email', $this->entity->id))
      ->orderBy('m.id DESC');
      
    $this->contact_modification_pager = new LsDoctrinePager($q, $page, $num);
  }


  public function executeAddressModifications($request)
  {
    $request->setParameter('object_model', 'Address');
    $request->setParameter('object_id', $request->getParameter('id'));
    
    $this->forward('modification', 'list');
  }
  
  
  public function executeImageModifications($request)
  {
    $request->setParameter('object_model', 'Image');
    $request->setParameter('object_id', $request->getParameter('id'));
    
    $this->forward('modification', 'list');  
  }


  public function executeImagesModifications($request)
  {
    $this->checkEntity($request, $includeDeleted=true, false);

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);

    $this->images = LsDoctrineQuery::create()
      ->from('Image i')
      ->leftJoin('i.LastUser u')
      ->leftJoin('u.Profile p')
      ->where('i.entity_id = ? AND i.is_deleted IS NOT NULL', $this->entity->id)
      ->orderBy('i.updated_at DESC')
      ->execute();  
  }
  
  
  public function executeTagModifications($request)
  {
    $this->checkEntity($request, $includeDeleted=true, false);

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);

    $q = $this->entity->getTagModificationsQuery();
    $this->tag_modification_pager = new LsDoctrinePager($q, $page, $num);
  }
  

  public function executeRelationshipModifications($request)
  {
    $this->checkEntity($request, $includeDeleted=true, false);

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 10);
    

    //first get relationship ids, existing and deleted
    $q = LsDoctrineQuery::create()
      ->select('r.id')
      ->from('Relationship r')
      ->where('r.entity1_id = ? OR r.entity2_id = ?', array($this->entity->id, $this->entity->id))
      ->addWhere('r.is_deleted IS NOT NULL');
      
    if (count($results = $q->fetchArray()))
    {    
      $ids = array();
  
      foreach ($results as $result)
      {
        $ids[] = $result['id'];
      }
      
      sort($ids);
  
      $q = LsDoctrineQuery::create()
        ->from('Modification m')
        ->leftJoin('m.Field f')
        ->leftJoin('m.User u')
        ->leftJoin('u.Profile p')
        ->where('m.object_model = ?', 'Relationship')
        ->andWhereIn('m.object_id', $ids)
        ->andWhere('m.is_create = ? OR m.is_delete = ? OR m.is_merge = ?', array(true, true, true))
        ->orderBy('m.id DESC')
        ->setHydrationMode(Doctrine::HYDRATE_ARRAY);
    }
    else
    {
      $q = array();
    }
      
    $this->modification_pager = new LsDoctrinePager($q, $page, $num);  
  }
  
  
  public function executeEditAliases($request)
  {
    $this->checkEntity($request, false, false);
    
    $q = LsDoctrineQuery::create()
      ->from('Alias a')
      ->where('a.entity_id = ?', $this->entity->id)
      ->orderBy('a.is_primary DESC');
      
    if (!$this->getUser()->hasCredential('admin'))
    {
      $q->andWhere('a.context IS NULL');
    }
    
    $this->aliases = $q->execute();
  }


  public function executeAddAlias($request)
  {
    if (!$request->isMethod('post'))
    {
      $this->forward('error', 'invalid');
    }


    $this->checkEntity($request, false, false);
    
    if (!$name = $request->getParameter('alias'))
    {
      $request->setError('alias', 'Alias is required');      

      $this->setTemplate('editAliases');      

      return sfView::SUCCESS;
    }
      
    $alias = new Alias;
    $alias->entity_id = $this->entity->id;
    $alias->name = $name;
    if ($context = $request->getParameter('context'))
    {
      $alias->context = $context;
    }
    $alias->save();

    $this->clearCache($this->entity);
    
    $this->redirect($this->entity->getInternalUrl('editAliases'));
  }
  
  
  public function executeRemoveAlias($request)
  {
    if (!$request->isMethod('post'))
    {
      $this->forward('error', 'invalid');
    }

    $alias = Doctrine::getTable('Alias')->find($request->getParameter('id'));
    $this->forward404Unless($alias && !$alias->is_primary);
    
    $redirect = $alias->Entity->getInternalUrl('editAliases');    
    $alias->delete();

    $this->clearCache($alias->Entity);
    
    $this->redirect($redirect);
  }
  
  
  public function executeMakePrimaryAlias($request)
  {
    if (!$request->isMethod('post'))
    {
      $this->forward('error', 'invalid');
    }  

    $alias = Doctrine::getTable('Alias')->find($request->getParameter('id'));
    $this->forward404Unless($alias);

    $alias->makePrimary();


    //comprehensive cache clearing due to changed primary alias
    $this->clearCache($alias->Entity);

    $entity_relationships = $alias->Entity->getEntitiesWithRelationships();
    $patterns = array();

    foreach ($entity_relationships as $order => $categoryIds)
    {
      foreach ($categoryIds as $categoryId => $entityIds)
      {
        foreach ($entityIds as $entityId => $entityAry)
        {
          $patterns = array_merge($patterns, LsCache::getEntityCachePatternsById($entityId));

          foreach ($entityAry['rels'] as $rel)
          {
            $patterns = array_merge($patterns, LsCache::getRelationshipCachePatternsById($rel['id']));
          }
        }
      }
    }

    LsCache::clearCachePatterns($patterns);

    $this->redirect($alias->Entity->getInternalUrl('editAliases'));
  }
  
  
  public function executeAddList($request)
  {
    $this->checkEntity($request, false, false);
   
   
    if ($request->hasParameter('add_list_terms'))
    {
      $terms = explode(' ', $request->getParameter('add_list_terms'));
      $page = $request->getParameter('page', 1);
      $num = $request->getParameter('num', 10);
  
      $q = LsDoctrineQuery::create()
        ->from('LsList l')
        ->where('l.is_network = 0 AND NOT EXISTS(SELECT le.id FROM ls_list_entity le WHERE le.list_id = l.id AND le.entity_id = ? AND le.is_deleted = 0)', $this->entity->id);

      foreach ($terms as $term)
      {
        $q->addWhere('l.name LIKE \'%' . $term . '%\' OR l.description LIKE \'%' . $term . '%\'');
      }
      
      if (!$this->getUser()->hasCredential('admin'))
      {
        $q->addWhere('l.is_admin = ?', 0);
      }

      $this->results_pager = new Doctrine_Pager($q, $page, $num);
    }


    if ($request->isMethod('post'))
    {
      $list = Doctrine::getTable('LsList')->find($request->getParameter('list_id'));
      $this->forward404Unless($list);

      //check that entity isn't already on this list
      $q = LsDoctrineQuery::create()
        ->from('LsListEntity le')
        ->where('le.list_id = ? AND le.entity_id = ? AND le.is_deleted IS NOT NULL', array($list->id, $this->entity->id));
      
      if ($listEntity = $q->fetchOne())
      {          
        if ($listEntity->is_deleted)
        {
          $listEntity->is_deleted = 0;
          $listEntity->save();
        }
      } 
      else 
      {
        $listEntity = new LsListEntity;
        $listEntity->list_id = $list->id;
        $listEntity->entity_id = $this->entity->id;
        $listEntity->save();      
      }

      $this->clearCache($this->entity);
      LsCache::clearListCacheById($list->id);
      
      $this->redirect($this->entity->getInternalUrl());
    }


    //also show popular lists
    $q = LsDoctrineQuery::create()
      ->select('l.*, COUNT(le.id) num')
      ->from('LsList l')
      ->where('l.is_network = 0')
      ->leftJoin('l.LsListEntity le')
      ->groupBy('l.id')
      ->orderBy('num DESC')
      ->limit(10);
      
    if (!$this->getUser()->hasCredential('admin'))
    {
      $q->addWhere('l.is_admin = ?', 0);
    }

    $this->popular_lists = $q->execute();
  }
  
  
  public function executeRemoveList($request)
  {
    if (!$request->isMethod('post'))
    {
      $this->forward('error', 'invalid');
    }

    $le = Doctrine::getTable('LsListEntity')->find($request->getParameter('id'));
    $this->forward404Unless($le);
    
    $entity = $le->Entity;
    $le->delete();

    $this->clearCache($entity);
    
    $this->redirect($entity->getInternalUrl());
  }
  
  
  // public function executeEditHierarchy($request)
  // {
  //   $this->checkEntity($request, false, false);
    
  //   $this->childs = $this->entity->getChildrenQuery()->execute();
  // }
  
  
  public function executeAddChild($request)
  {    
    $this->checkEntity($request, false, false);


    //form submission, display matching persons
    if ($request->hasParameter('child_terms'))
    {
      $terms = $request->getParameter('child_terms');
      $terms = preg_replace('/\s{2,}/', ' ', $terms);
      $terms = explode(' ', $terms);

      //search for query that excludes the current Entity
      $q = EntityTable::getSimpleSearchQuery($terms, array('Org'))
        ->andWhere('e.id <> ?', $this->entity->id)
        ->andWhere('e.parent_id IS NULL');

      $num = $request->getParameter('num', 10);
      $page = $request->getParameter('page', 1);

      $this->entity_pager = new LsDoctrinePager($q, $page, $num);
    }

    
    if ($request->isMethod('post'))
    {    
      if (!$child = Doctrine::getTable('Entity')->findOneById($request->getParameter('child_id')))
      {
        $this->forward404();
      }
      
      $child->parent_id = $this->entity->id;
      $child->save();

      $this->clearCache($this->entity);
      $this->clearCache($child);
      
      $this->redirect($this->entity->getInternalUrl('editHierarchy'));
    }
  }
  
  
  public function executeRemoveChild($request)
  {
    if (!$request->isMethod('post'))
    {
      $this->forward('error', 'invalid');
    }
    
    $this->checkEntity($request, false, false);
    
    if (!$child = Doctrine::getTable('Entity')->findOneById($request->getParameter('child_id')))
    {
      $this->forward404();
    }

    if (!$child->parent_id == $this->entity->id)
    {
      $this->forward404();
    }
    
    $child->parent_id = null;
    $child->save();

    $this->clearCache($this->entity);
    $this->clearCache($child);
    
    $this->redirect($this->entity->getInternalUrl('editHierarchy'));  
  }
  
  
  public function executeChangeParent($request)
  {
    $this->checkEntity($request, false, false);

    //form submission, display matching persons
    if ($request->hasParameter('parent_terms'))
    {
      $terms = $request->getParameter('parent_terms');
      $terms = preg_replace('/\s{2,}/', ' ', $terms);
      $terms = explode(' ', $terms);

      //search for query that excludes the current Entity
      $q = EntityTable::getSimpleSearchQuery($terms, array('Org'))
        ->addWhere('e.id <> ?', $this->entity->id);

      $num = $request->getParameter('num', 10);
      $page = $request->getParameter('page', 1);

      $this->entity_pager = new LsDoctrinePager($q, $page, $num);
    }
    
    if ($request->isMethod('post'))
    {    
      if (!$parent = Doctrine::getTable('Entity')->findOneById($request->getParameter('parent_id')))
      {
        $this->forward404();
      }
      
      $this->entity->parent_id = $parent->id;
      $this->entity->save();

      $this->clearCache($this->entity);
      $this->clearCache($parent);
      
      $this->redirect($this->entity->getInternalUrl('editHierarchy'));
    }
  }


  public function executeRemoveParent($request)
  {
    if (!$request->isMethod('post'))
    {
      $this->forward('error', 'invalid');
    }
    
    $this->checkEntity($request, false, false);
        
    $this->entity->parent_id = null;
    $this->entity->save();

    $this->clearCache($this->entity);
    $this->clearCache($parent);
    
    $this->redirect($this->entity->getInternalUrl('editHierarchy'));  
  }

  
  public function executeNotes($request)
  {
    $this->checkEntity($request, false, false);

    $this->note_form = new NoteForm;
    
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);

    $s = new LsSphinxClient($page, $num);
    $s->setFilter('entity_ids', array($this->entity['id']));
    $s->setFilter('visible_to_user_ids', array_unique(array(0, sfGuardUserTable::getCurrentUserId())));
    
    if ($userId = $request->getParameter('user_id'))
    {
      $s->setFilter('user_id', array($userId));
    }
    
    $this->note_pager = NoteTable::getSphinxPager($s, null, Doctrine::HYDRATE_ARRAY);
  }
  
  
  public function executeMatchDonations($request)
  {  
    $this->checkEntity($request);

    //only allowed for persons
    $this->forward404Unless($this->entity['primary_ext'] == 'Person');

    $userId = $this->getUser()->getGuardUser()->id;


    //preprocess if requested
    if ($request->getParameter('preprocess'))
    {
      OsPreprocessMatchesTask::preprocessEntity($this->entity);
      
      $databaseManager = sfContext::getInstance()->getDatabaseManager();
      $db = $databaseManager->getDatabase('main');
      $db = Doctrine_Manager::connection($db->getParameter('dsn'));        
    }

    //only allowed for preprocessed entities
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT COUNT(*) FROM os_entity_preprocess WHERE entity_id = ?';
    $stmt = $db->execute($sql, array($this->entity['id']));    
    //$this->forward404Unless($stmt->fetch(PDO::FETCH_COLUMN));
    $count = $stmt->fetch(PDO::FETCH_COLUMN);
    
    if (!$count)
    {
      OsEntityDonorTable::unlockEntityById($this->entity['id']);
    }

		$related = $this->getUser()->getAttribute('os_related_ids');
		if (count($related))
		{
			
			if ($this->entity['id'] == $related[0])
			{
				$this->next_entity = Doctrine::getTable('Entity')->find($related[1]);	
				$this->remaining = count($related)-1;
				if ($request->isMethod('post'))
				{
					array_shift($related);
					$this->getUser()->setAttribute('os_related_ids', $related);
				}
			}
			else 
			{
				$this->next_entity = Doctrine::getTable('Entity')->find($related[0]);	
				$this->remaining = count($related);
			}
	  }
		else
		{
			//get another entity that needs matching
			$where = 'et.reviewed_at IS NULL AND et.locked_at IS NULL AND e.id <> ? AND e.is_deleted = 0';
			$params = array($this->entity['id']);
			
			if ($skippedIds = $this->getUser()->getAttribute('os_skipped_ids'))
			{
				$where .= ' AND e.id NOT IN (' . implode(',', array_fill(0, count($skippedIds), '?')) . ')';
				$params = array_merge($params, $skippedIds);
			}
			
			$sql = 'SELECT e.* FROM os_entity_transaction et LEFT JOIN entity e ON (et.entity_id = e.id) WHERE ' . $where . ' LIMIT 100'; 
			$stmt = $db->execute($sql, $params);
			$nextEntities = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$this->next_entity = $nextEntities[rand(0, count($nextEntities) - 1)];
		}


    //check for lock
    $sql = 'SELECT COUNT(*) FROM os_entity_transaction WHERE entity_id = ? AND locked_by_user_id <> ? AND locked_by_user_id IS NOT NULL';
    $stmt = $db->execute($sql, array($this->entity['id'], $userId));    
    $lockCount = $stmt->fetch(PDO::FETCH_COLUMN);

    if ($lockCount > 0)
    {
      return 'Locked';
    }


    //get preprocessed transactions
    $sql = "SELECT CONCAT(cycle, ':', transaction_id) trans FROM os_entity_transaction WHERE entity_id = ?";
    $stmt = $db->execute($sql, array($this->entity['id']));
    $trans = $stmt->fetchAll(PDO::FETCH_COLUMN);

    //get verified transactions
    $sql = "SELECT CONCAT(cycle, ':', transaction_id) FROM os_entity_transaction WHERE entity_id = ? AND is_verified = 1";
    $stmt = $db->execute($sql, array($this->entity['id']));
    $this->verified_trans = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($trans))
    {
      //check if matches have been updated
      $sql = "SELECT MAX(updated_at) FROM os_entity_preprocess WHERE entity_id = ?";
      $stmt = $db->execute($sql, array($this->entity['id']));
      $this->updated_at = $stmt->fetch(PDO::FETCH_COLUMN);
    }
    
    //verify and unverify transactions
    if ($request->isMethod('post'))
    {
      $commit = $request->getParameter('commit');


      //user cancels matching
      if ($commit == 'Cancel')
      {
        //unlock
        OsEntityTransactionTable::unlockEntityById($this->entity['id']);
        
        $this->redirect(EntityTable::getInternalUrl($this->entity));
      }

      
      if ($nextEntityId = $request->getParameter('next_id'))
      {
        $nextEntity = EntityApi::get($nextEntityId);
      }
      

      //user skips this entity but wants to match another
      if ($nextEntity && $commit == 'Skip and Match Another')
      {
        $skippedIds = $this->getUser()->getAttribute('os_skipped_ids', array());
        
        if (!array_search($this->entity['id'], $skippedIds))
        {
          $skippedIds[] = $this->entity['id'];
        }

        $this->getUser()->setAttribute('os_skipped_ids', $skippedIds);
        
        //unlock
        OsEntityTransactionTable::unlockEntityById($this->entity['id']);
        
        $this->redirect(EntityTable::getInternalUrl($nextEntity, 'matchDonations'));        
      }


      if (!$submittedTrans = $request->getParameter('trans'))
      {
        $submittedTrans = array();
      }
      
      $newTrans = array_diff($submittedTrans, $this->verified_trans);
      $oldTrans = array_diff($this->verified_trans, $submittedTrans);

      if ($newTrans)
      {
        //mark new donor ids as verified
        $sql = 'UPDATE os_entity_transaction SET is_verified = 1, is_synced = (is_verified = is_processed), reviewed_at = ?, reviewed_by_user_id = ? WHERE entity_id = ? AND (' . OsDonationTable::generateKeyClause('cycle', 'transaction_id', $newTrans) . ')';
        $stmt = $db->execute($sql, array(date('Y-m-d H:i:s'), $this->getUser()->getGuardUser()->id, $this->entity['id']));
      }
      
      if ($oldTrans)
      {
        //mark old donor ids as unverified
        $sql = 'UPDATE os_entity_transaction SET is_verified = 0, is_synced = (is_verified = is_processed), reviewed_at = ?, reviewed_by_user_id = ? WHERE entity_id = ? AND (' . OsDonationTable::generateKeyClause('cycle', 'transaction_id', $oldTrans) . ')';
        $stmt = $db->execute($sql, array(date('Y-m-d H:i:s'), $this->getUser()->getGuardUser()->id, $this->entity['id']));
      }
      
      //mark all donor ids as reviewed
      $sql = 'UPDATE os_entity_transaction SET reviewed_at = ?, reviewed_by_user_id = ? WHERE entity_id = ?';
      $stmt = $db->execute($sql, array(date('Y-m-d H:i:s'), $this->getUser()->getGuardUser()->id, $this->entity['id']));

      //clear preprocess updated_at fields because the donations have now been verified by a user
      if (isset($this->updated_at) && $this->updated_at)
      {
        $sql = "UPDATE os_entity_preprocess SET updated_at = NULL WHERE entity_id = ?";
        $stmt = $db->execute($sql, array($this->entity['id']));
      }

      //unlock
      OsEntityTransactionTable::unlockEntityById($this->entity['id']);

      
      //user submits matches and wants to match another entity
      if ($nextEntity && $commit == 'Verify and Match Another')
      {
        $this->redirect(EntityTable::getInternalUrl($nextEntity, 'matchDonations'));
      }
      
            
      $this->redirect(EntityTable::getInternalUrl($this->entity));
    }


    //lock page
    OsEntityTransactionTable::lockEntityById($this->entity['id'], $userId);

    //get user who last reviewed, if any
    $this->reviewed_by_user = null;
    $this->reviewed_at = null;
    $sql = 'SELECT p.public_name, et.reviewed_at FROM os_entity_transaction et ' .
           'LEFT JOIN sf_guard_user_profile p ON (et.reviewed_by_user_id = p.user_id) ' .
           'WHERE et.entity_id = ? LIMIT 1';
    $stmt = $db->execute($sql, array($this->entity['id']));

    if ($row = $stmt->fetch(PDO::FETCH_NUM))
    {
      list($this->reviewed_by_user, $this->reviewed_at) = $row;
    }


    //get related entities
    $options = array('cat_ids' => '1,3');
    $this->related_entities = EntityApi::getRelated($this->entity['id'], $options);

    //get addresses
    $this->addresses = LsDoctrineQuery::create()
      ->from('Address a')
      ->leftJoin('a.State s')
      ->where('a.entity_id = ?', $this->entity['id'])
      ->andWhere('a.is_deleted = 0')
      ->orderBy('s.abbreviation, a.city, a.postal')
      ->execute();

    $databaseManager = sfContext::getInstance()->getDatabaseManager();

    if (count($trans))
    {
      //get all transaction records for the given donor ids
      $rawDb = $databaseManager->getDatabase('raw');
      $rawDb = Doctrine_Manager::connection($rawDb->getParameter('dsn'));  
      $sql = 'SELECT * FROM os_donation FORCE INDEX(PRIMARY) WHERE ' . OsDonationTable::generateKeyClause('cycle', 'row_id', $trans);
      $stmt = $rawDb->execute($sql);
      $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else
    {
      $donations = array();
    }
    
    $this->donors = array();

    foreach ($donations as $row)
    {
      //clean donor IDs so they don't break HTML ids and classes in the view
      $donorId = str_replace(' ', '_', $row['donor_id']);
      $donorId = str_replace('@', '-', $donorId);

      //generate employer_raw from occupation and employer fields if null
      if (!$row['employer_raw'] && $row['title_raw'])
      {
        $row['employer_raw'] = trim($row['org_raw'] . "/" . $row['title_raw'], "/");
      }

      if (isset($this->donors[$donorId]))
      {
        $this->donors[$donorId]['names'][$row['donor_name']] = true;
        $this->donors[$donorId]['cycles'][$row['cycle']] = true;
        $this->donors[$donorId]['orgs'][$row['employer_raw']] = true;
        $this->donors[$donorId]['addresses'][OsDonationTable::buildAddress($row)] = true;
        $this->donors[$donorId]['image_ids'][$row['fec_id']] = $row['cycle'];

        if ($row['donor_name_middle'])
        {
          $this->donors[$donorId]['middles'][$row['donor_name_middle']] = true;        
        }

        $this->donors[$donorId]['donations'][] = $row;
      }
      else
      {
        $this->donors[$donorId] = array(
          'names' => array($row['donor_name'] => true),
          'middles' => $row['donor_name_middle'] ? array($row['donor_name_middle'] => true) : array(),
          'cycles' => array($row['cycle'] => true),
          'orgs' => array($row['employer_raw'] => true),
          'addresses' => array(OsDonationTable::buildAddress($row) => true),
          'image_ids' => array($row['fec_id'] => $row['cycle']),
          'donations' => array($row)
        );
      }      
    }


    $db = $databaseManager->getDatabase('main');
    Doctrine_Manager::connection($db->getParameter('dsn'));
  }
  
  
  public function executeAddBoard($request)
  {
    $this->checkEntity($request, false, false);    
        
    //FIND NAMES AT REF URL PROVIDED
    $this->reference_form = new ReferenceForm;
    $this->reference_form->setSelectObject($this->entity);
      
     if ($request->isMethod('post'))
    {
      $commit = $request->getParameter('commit');

      if ($commit == 'Cancel')
      {
        $this->redirect(EntityTable::getInternalUrl($this->entity));
      }

      $this->lim = 5;

      // REFERENCE INFO HAS BEEN SUBMITTED, SO GO TO URL AND SCRAPE
      if (!$request->hasParameter('ref_id') && $request->hasParameter('reference'))
      {
        $this->getUser()->setAttribute('board_names', null);
        $refParams = $request->getParameter('reference');
        $this->reference_form->bind($refParams);
        
        if ($this->reference_form->isValid())
        {
          if ($this->ref_id = $refParams['existing_source'])
          {
            $ref = Doctrine::getTable('Reference')->find($this->ref_id);
            $url = $ref->source;
          }
          else
          {
            $ref = new Reference;
            $ref->object_model = 'Entity';
            $ref->object_id = $this->entity->id;
            $ref->source = $refParams['source'];
            $ref->name = $refParams['name'];
            $ref->source_detail = $refParams['source_detail'];
            $ref->publication_date = $refParams['publication_date'];
            $ref->save();

            $this->ref_id = $ref->id;
            $url = $ref->source;
          }
          
          $browser = new sfWebBrowser();

          //FIND NAMES AT URL USING COMBO OF OPENCALAIS & LS CUSTOM HTML PARSING
          if (!$browser->get($url)->responseIsError())
          {
            $text = $browser->getResponseText();
            $ls_names = LsLanguage::getHtmlPersonNames($text);

            $oc = new LsOpencalais;
            $oc->setParameter( array('contentType' => 'text/html' ) );
            $oc->setContent($text);
            $oc->execute();

            $response = $oc->getParsedResponse(array("Person")); 
            $oc_names = (array) $response['Person'];  
            $names = array_merge($oc_names, $ls_names);
            $names = array_unique($names);
            sort($names);

            $this->getUser()->setAttribute('board_names', $names);
          }    
        }
      }
      // REFERENCE HAS ALREADY BEEN ADDED, CREATE NEW RELATIONSHIPS
      else if ($request->hasParameter('ref_id'))
      {  
        $this->ref_id = $this->getRequestParameter('ref_id');  
        $entity_ids = array();

        for ($i = 0; $i < $this->lim; $i++)
        {
          if ($entity_id = $request->getParameter('entity_' . $i))
          {
            if ($entity_id == 'new')
            {
              $name = $request->getParameter('new_name_' . $i);
              $new_entity = PersonTable::parseFlatName($name); 
              $new_entity->blurb = $request->getParameter('new_blurb_' . $i);

              if ($name && !$new_entity->name_last)
              {
                $request->setError('name', 'The name you entered is invalid');
              }
              else
              {
                $new_entity->save();
                $entity_ids[] = $new_entity->id;
              }
            }
            else if ($entity_id > 0)
            {
              $entity_ids[] = $entity_id;
            }
          }
        }

        $this->existing_rels = array();
        $this->new_rels = array();

        //CHECK FOR EXISTING RELATIONSHIPS, CREATE NEW IF NONE FOUND
        foreach ($entity_ids as $entity_id)
        {
          $existing_rel = LsDoctrineQuery::create()
            ->from('Relationship r')
            ->leftJoin('r.Position p')
            ->where('r.entity1_id = ? and r.entity2_id = ? and p.is_board = ?', array($entity_id, $this->entity->id, '1'))
            ->fetchOne();

          if ($existing_rel)
          {
            $this->existing_rels[] = $existing_rel;
          }
          else
          {
            $rel = new Relationship;
            $rel->entity1_id = $entity_id;
            $rel->entity2_id = $this->entity->id;
            $rel->setCategory('Position');
            $rel->description1 = 'Board Member';
            $rel->description2 = 'Board Member';
            $rel->is_board = 1;
            $rel->is_employee = 0;
            $rel->saveWithRequiredReference(array(
              'existing_source' => $this->ref_id, 
              'excerpt' => null,
              'source_detail' => null, 
              'publication_date' => null
            ));
            $this->new_rels[] = $rel;
          }
        }      
      }
    }    
    // NO POST REQUEST, CLEAR SESSION VARIABLE
    else
    {
      $this->getUser()->setAttribute('board_names', null);
    }
    
    // IF BOARD NAMES SESSION VARIABLE NOT NULL, PAGE THROUGH TO CORRECT START    
    if ($board_names = $this->getUser()->getAttribute('board_names'))
    {
      $this->start = $this->getRequestParameter('start');
      $this->matches = array();

      if (count($board_names) > $this->start)
      {        
        for ($i = $this->start; $i < $this->start + $this->lim; $i++)
        {
          if (!isset($board_names[$i]))
          {
            break;
          }

          $name = $board_names[$i];
          $pager = EntityTable::getSphinxPager($terms, $page=1, $num=10, $listIds=null, $aliases=true, $primary_ext="Person");  
          $this->matches[$name] = $pager->execute();
          $this->total = $pager->getNumResults();
        }
      }

      $this->total = count($board_names);
      $this->end = (count($this->matches) < $this->lim) ? $this->start + count($this->matches) : $this->start + $this->lim;
    }

    if ($this->hasRequestParameter('finished'))
    {
      $this->finished = 1;
    }
  }
  
  
  
  public function executeRefresh($request)
  {
    $this->checkEntity($request);
    LsCache::clearEntityCacheById($this->entity['id']);
    
    $this->redirect($request->getParameter('ref', EntityTable::getInternalUrl($this->entity)));
  }
  
  
  public function executeFindConnections($request)
  {
    $this->checkEntity($request);
    $this->logRecentView();

    $this->page = $request->getParameter('page', 1);
    $this->num = $request->getParameter('num', 10);

    if ($request->isXmlHttpRequest())
    {
      return $this->renderComponent('entity', 'findConnections', array(
        'entity' => $this->entity,
        'page' => $this->page,
        'num' => $this->num
      ));
    }

    $this->tab_name = 'findConnections';

    $this->setTemplate('view');
  }


  public function executeLobbyingArmy($request)
  {
    $this->checkEntity($request);
    $listId = $request->getParameter('list_id', 102);

    //get revolving door lobbyists
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT e.id, e.name, e.blurb, e.primary_ext, i.filename, IF(i.filename IS NOT NULL, 1, 0) AS has_image ' .
           'FROM ls_list_entity le ' .
           'LEFT JOIN entity e ON (e.id = le.entity_id) ' .
           'LEFT JOIN person p ON (p.entity_id = e.id) ' .
           'LEFT JOIN image i ON (i.entity_id = e.id AND i.is_featured = 1 AND i.is_deleted = 0) ' .
           'LEFT JOIN link l ON (l.entity1_id = le.entity_id) ' .
           'LEFT JOIN relationship r ON (r.id = l.relationship_id) ' .
           'WHERE le.list_id = ? AND le.is_deleted = 0 ' .
           'AND l.category_id IN (1, 6) AND l.entity2_id = ? AND (r.is_current = 1 OR r.is_current IS NULL) ' .
           'AND e.is_deleted = 0 ' .
           'GROUP BY e.id ' .
           'ORDER BY has_image DESC, p.name_last ASC';
    $stmt = $db->execute($sql, array($listId, $this->entity['id']));
    $this->lobbyists = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $lobbyistIds = array();
    foreach ($this->lobbyists as $l)
    {
      $lobbyistIds[] = $l['id'];
    } 
    
    //get members of congress these lobbyists have worked for
    $sql = 'SELECT e.id, e.name, e.blurb, e.primary_ext, i.filename ' .
           'FROM relationship r ' .
           'LEFT JOIN entity e ON (e.id = r.entity2_id) ' .
           'LEFT JOIN person p ON (p.entity_id = e.id) ' .
           'LEFT JOIN elected_representative er ON (er.entity_id = e.id ) ' .
           'LEFT JOIN image i ON (i.entity_id = e.id AND i.is_featured = 1 AND i.is_deleted = 0) ' .
           'WHERE r.entity1_id IN (' . implode(',', $lobbyistIds) . ') ' .
           'AND er.id IS NOT NULL ' .
           'AND r.category_id = 1 AND e.primary_ext = \'Person\' AND e.is_deleted = 0 AND r.is_deleted = 0 ' .
           'GROUP BY e.id ' .
           'ORDER BY p.name_last ASC';
    $stmt = $db->execute($sql);
    $this->members = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  
  public function executeTheyRuleGenders($request)
  {
    $num = $request->getParameter('num', 25);
    $db = Doctrine_Manager::connection();

    if ($request->isMethod('post'))
    {
      $genders = array();
    
      foreach (range(1, 25) as $row)
      {
        $genders[] = $request->getParameter('genders' . $row, array());
      }

      $done = array();
      
      foreach ($genders as $gender)
      {      
        if (count($gender))
        {
          list($entityId, $genderId) = explode(':', $gender[0]);

          if ($entityId && $genderId != '?')
          {
            $entity = Doctrine::getTable('Entity')->find($entityId);
            $entity->gender_id = $genderId;
            $entity->save();          
            
            $done[] = $entityId;
          }
          
          if ($genderId == '?')
          {
            $done[] = $entityId;
          }
        }
      }
      
      //update queue
      if (count($done))
      {
        $sql = 'UPDATE theyrule_gender_queue SET is_done = 1 WHERE entity_id IN (' . implode(',', $done) . ')';
        $stmt = $db->execute($sql);
      }
      
      $this->redirect('entity/theyRuleGenders?num=' . $num);
    }


    //get unlocked undone entities
    $sql = 'SELECT entity_id FROM theyrule_gender_queue WHERE is_done = 0 AND (locked_at IS NULL OR locked_at < ?) ' .
           'LIMIT ' . $num;
    $stmt = $db->execute($sql, array(date('Y-m-d H:i:s', strtotime('10 minutes ago'))));
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($ids))
    {
      /* set new locks 
      $sql = 'UPDATE theyrule_gender_queue ' . 
             'SET locked_at = ? ' .
             'WHERE entity_id IN (' . implode(',', $ids) . ')';
      $stmt = $db->execute($sql, array(LsDate::getCurrentDateTime()));      
      */
    
      $this->entities = LsDoctrineQuery::create()
        ->from('Entity e')
        ->leftJoin('e.Image i')
        ->leftJoin('e.Person p')
        ->whereIn('e.id', $ids)
        ->groupBy('e.id')
        ->execute();
    }
    else
    {
      $this->entities = array();
    }
  }


  public function executeEditCustomFields($request)
  {
    $this->checkEntity($request, false, false);
    $this->keys = $this->entity->getCustomFields();  
  }


  public function executeEditCustomField($request)
  {
    $this->checkEntity($request, false, false);
    $this->keys = $this->entity->getCustomFields();  
    $this->reserved_keys = array_merge($this->entity->getAllFields(), array_keys($this->keys));
    sort($this->reserved_keys);

    if ($this->key = $request->getParameter('key'))    
    {
      $q = LsDoctrineQuery::create()
        ->from('CustomKey k')
        ->where('k.object_model = ?', 'Entity')
        ->andWhere('k.object_id = ?', $this->entity->id)
        ->andWhere('k.name = ?', $this->key);

      if (!$customKey = $q->fetchOne())
      {
        $customKey = new CustomKey;
      }      
    }
    else
    {
      $customKey = new CustomKey;
    }

    $this->key_form = new CustomKeyForm($customKey);
    $this->reference_form = new ReferenceForm;

    $refParams = $request->getParameter('reference');
    $keyParams = $request->getParameter('custom_key');

    if ($request->isMethod('post'))
    {
      $this->reference_form->bind($refParams);
      $this->key_form->bind($keyParams);
      
      if ($this->reference_form->isValid() && $this->key_form->isValid())
      {
        if (!$this->key && in_array($keyParams['name'], $this->reserved_keys))
        {
          $validatorSchema = $this->key_form->getValidatorSchema();
          $this->key_form->getErrorSchema()->addError(new sfValidatorError($validatorSchema['name'], 'invalid'), 'name');

          return sfView::SUCCESS;
        }
        
        //create key
        $this->entity->setCustomField($keyParams['name'], $keyParams['value']);

        //create reference
        $ref = new Reference;
        $ref->object_model = 'Entity';
        $ref->object_id = $this->entity->id;
        $ref->name = $refParams['name'];
        $ref->source = $refParams['source'];
        $ref->source_detail = $refParams['source_detail'];
        $ref->publication_date = $refParams['publication_date'];
        $ref->save();

        $this->redirect($this->entity->getInternalUrl('editCustomFields'));
      }
    }
  }


  public function executeRemoveCustomField($request)
  {
    $this->checkEntity($request, false, false);

    if (!$this->getUser()->hasCredential('deleter'))
    {
      $this->forward('error', 'invalid');
    }

    if (!$key = $request->getParameter('key'))
    {
      $this->forward('error', 'invalid');
    }

    $this->entity->removeCustomField($key);
    $this->redirect($this->entity->getInternalUrl('editCustomFields'));
  }

  
  public function executeEditExternalKeys($request)
  {
    $this->checkEntity($request, false, false);
    $this->domains = LsDoctrineQuery::create()
          ->from('Domain d')
          ->orderBy('d.name DESC')
          ->execute();
    
    $externalIds = $this->entity->getExternalIds();
    if (!$request->isMethod('post') && $domain_id = $request->getParameter('domain_id'))
    {
      $this->domain = Doctrine::getTable('Domain')->find($domain_id);
      $this->matches = LsExternalApiKeys::findKeys($this->entity,$this->domain->name);
      $this->existing_keys = $externalIds;
      $ids = array();
      if ($this->matches)
      {
        foreach($this->matches as $match)
        {
          $ids[] = $match['id'];
        }
      }
      if (isset($this->existing_keys[$this->domain->name]))
      {
        foreach($this->existing_keys[$this->domain->name] as $ek)
        {
          if (!in_array($ek,$ids))
          {
            $this->matches[] = array('id' => $ek, 'name' => '');
          }
        }
      }
    }
    else if (!$request->isMethod('post') && $all_domains = $request->getParameter('all_domains'))
    {
    	$this->all_domains = true;
    	$this->domains = Doctrine::getTable('Domain')->findAll();
    	$this->matches = array();
     	foreach($this->domains as $domain)
     	{
     		$this->matches[$domain->name] = LsExternalApiKeys::findKeys($this->entity,$domain->name);	
				if (isset($this->existing_keys[$domain->name]))
				{
					foreach($this->existing_keys[$domain->name] as $ek)
					{
						if (!in_array($ek,$ids))
						{
							$this->matches[$domain->name] = array('id' => $ek, 'name' => '');
						}
					}
				}
     	}
     	$this->existing_keys = $externalIds;
    }
    else if ($request->isMethod('post') && $request->getParameter('submit') == 'submit')
    {
    	if ($request->getParameter('all_domains'))
    	{
    		$params = (array) $request->getParameterHolder();
    		$params = array_shift($params);
    	  $domain_keys = $params['domains'];
    	  $domains = Doctrine::getTable('Domain')->findAll();
    	  foreach($domains as $domain)
    	  {
    	  	if (isset($domain_keys[$domain->name]))
    	  	{
						$newKeyIds = $domain_keys[$domain->name];
					}
				  else $newKeyIds = array();
					if (!isset($externalIds[$domain->name]))
					{
						$externalIds[$domain->name] = array();
					}
					$keysToRemove = array_diff($externalIds[$domain->name], $newKeyIds);
					$keysToAdd = array_diff($newKeyIds, $externalIds[$domain->name]);
					foreach($keysToAdd as $k)
					{
						$key = new ExternalKey;
						$key->external_id = $k;
						$key->domain_id = $domain->id;
						$key->entity_id = $this->entity->id;
						$key->save();
					}
					foreach($keysToRemove as $k)
					{
						$key = LsDoctrineQuery::create()
									->from('ExternalKey ek')
									->where('ek.external_id = ? and ek.domain_id = ? and ek.entity_id = ?',
										array($k,$domain->id,$this->entity->id))
									->fetchOne();
						$key->delete();  
					}
			  }
    	}
    	else
    	{
				$domain = Doctrine::getTable('Domain')->find($request->getParameter('domain_id'));
				$newKeyIds = $request->getParameter('key') ? $request->getParameter('key') : array();
				$manual_key = $request->getParameter('manual_key');
				if($manual_key && $manual_key != '')
				{
					$newKeyIds[] = $manual_key;
				}
				if (!isset($externalIds[$domain->name]))
				{
					$externalIds[$domain->name] = array();
				}
				$keysToRemove = array_diff($externalIds[$domain->name], $newKeyIds);
				$keysToAdd = array_diff($newKeyIds, $externalIds[$domain->name]);
				foreach($keysToAdd as $k)
				{
					$key = new ExternalKey;
					$key->external_id = $k;
					$key->domain_id = $domain->id;
					$key->entity_id = $this->entity->id;
					$key->save();
				}
				foreach($keysToRemove as $k)
				{
					$key = LsDoctrineQuery::create()
								->from('ExternalKey ek')
								->where('ek.external_id = ? and ek.domain_id = ? and ek.entity_id = ?',
									array($k,$domain->id,$this->entity->id))
								->fetchOne();
					$key->delete();  
				}
		  }
    }
    $this->keys = $this->entity->ExternalKey;
  }

  public function executeAddBulk($request)
  {
    $this->checkEntity($request, false, false);    
        
    $this->reference_form = new ReferenceForm;
    $this->reference_form->setSelectObject($this->entity);
    $this->add_bulk_form = new AddBulkForm;
    

    //get possible default categories
    $this->categories = LsDoctrineQuery::create()
      ->select('c.name, c.name')
      ->from('RelationshipCategory c')
      ->orderBy('c.id')
      ->fetchAll(PDO::FETCH_KEY_PAIR);

    array_unshift($this->categories, '');
    
    
    if ($request->isMethod('post') && in_array($request->getParameter('commit'),array('Begin','Continue')))
    {
      if ($request->hasParameter('ref_id'))
      {
        $this->ref_id = $request->getParameter('ref_id');
      }
      else
      {
        $refParams = $request->getParameter('reference');
        $this->reference_form->bind($refParams);
        $restOfParams = (array) $request->getParameterHolder();
        $restOfParams = array_shift($restOfParams);
        $this->add_bulk_form->bind($restOfParams, $request->getFiles());
        
        if (!$this->reference_form->isValid() || !$this->add_bulk_form->isValid())
        {
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
          $ref->object_model = 'Entity';
          $ref->object_id = $this->entity->id;
          $ref->source = $refParams['source'];
          $ref->name = $refParams['name'];
          $ref->source_detail = $refParams['source_detail'];
          $ref->publication_date = $refParams['publication_date'];
          $ref->save();
          
        }
        $this->ref_id = $ref->id;
        $this->reference = $ref;
      }
      
      $verify_method = $request->getParameter('verify_method');
      
      if ($this->add_method = $request->getParameter('add_method'))
      {
        
        if ($this->add_method == 'scrape')
        {
          //scrape ref url
          //set names to confirm
          $browser = new sfWebBrowser();
          $entity_types = $request->getParameter('entity_types');
          //FIND NAMES AT URL USING COMBO OF OPENCALAIS & LS CUSTOM HTML PARSING
          if (!$browser->get($ref->source)->responseIsError())
          {
            $text = $browser->getResponseText();
            $this->names = LsTextAnalysis::getHtmlEntityNames($text,$entity_types);
            $text = LsHtml::findParagraphs($text);
            $this->text = preg_replace('/<[^b][^>]*>/is'," ", $text);
            $this->confirm_names = true;
            return;
          }
          else
          {
            $request->setError('csv', 'problems finding names at that url');
          }
          
        }
        else if ($this->add_method == 'upload')
        {
          $file = $this->add_bulk_form->getValue('file');
         
          $filename = 'uploaded_'.sha1($file->getOriginalName());
          $extension = $file->getExtension($file->getOriginalExtension());
          $filePath = sfConfig::get('sf_temp_dir').'/'.$filename.$extension;
          $file->save($filePath);
          
          if ($filePath)
          {
            if ($spreadsheetArr = LsSpreadsheet::parse($filePath))
            {         
              $names = $spreadsheetArr['rows'];
             
              if (!in_array('name',$spreadsheetArr['headers']))
              {
                $request->setError('file', 'The file you uploaded could not be parsed properly because there is no "name" column.');
                return;
              }
              if (in_array('summary',$spreadsheetArr['headers']))
              {
              	foreach($names as &$name)
              	{
			            $name['summary'] = str_replace(array('?',"'"),"'",$name['summary']);
              		$name['summary'] = str_replace(array('?','?','"'),'"',$name['summary']);

              		if (isset($name['title']))
              		{
              			$name['description1'] = $name['title'];
              		}              		
              	}
              	unset($name);
              }
            }
            else
            {
              $request->setError('file', 'The file you uploaded could not be parsed properly.');
              return;
            }   
          }
          else 
          {
            $request->setError('file', 'You need to upload a file.');
              return;
          }
        }
        else if ($this->add_method == 'summary')
        {
          //parse summary for names
          $this->text = $this->entity->summary;
          $entity_types = $request->getParameter('entity_types');
          $this->names = LsTextAnalysis::getTextEntityNames($this->text,$entity_types);
           $this->confirm_names = true;
          return;
        }
        else if ($this->add_method == 'text')
        {
          $manual_names = $request->getParameter('manual_names');
          if ($manual_names && $manual_names != "")
          {
            $manual_names = preg_split('#[\r\n]+#', $manual_names);
            $manual_names = array_map('trim', $manual_names);
            $names = array();
            foreach($manual_names as $name)
            {
              $names[] = array('name' => $name);
            }
          }
          else
          {
            $request->setError('csv', 'You did not add names properly.');
            return;
          }
        }
        else if ($this->add_method == 'db_search')
        {
					$this->db_search = true;
        }
      }
      
      //intermediate scrape page -- takes confirmed names, builds names arr
      if ($confirmed_names = $request->getParameter('confirmed_names'))
      {
        $restOfParams = (array) $request->getParameterHolder();
        $restOfParams = array_shift($restOfParams);
        $this->add_bulk_form->bind($restOfParams, $request->getFiles());
        if(!$this->add_bulk_form->isValid())
        {
          $this->reference = Doctrine::getTable('reference')->find($this->ref_id);
          $this->names = unserialize(stripslashes($request->getParameter('names')));
          $this->confirm_names = true;
          return;
        }
        $names = array();
        
        foreach($confirmed_names as $cn)
        {
          $names[] = array('name' => $cn);
        } 
        $manual_names = $request->getParameter('manual_names');
        if ($manual_names && $manual_names != "")
        {
          $manual_names = preg_split('#[\r\n]+#', $manual_names);
          $manual_names = array_map('trim', $manual_names);
          foreach($manual_names as $name)
          {
            $names[] = array('name' => $name);
          }
        }
      }
      
      // LOAD IN RELATIONSHIP DEFAULTS
      
      if (isset($verify_method))
      {  
        
        $defaults = $request->getParameter('relationship');
        
        if ($verify_method == 'enmasse')
        {
          $this->default_type = $request->getParameter('default_type');
          $this->order = $request->getParameter('order');
          $category_name = $request->getParameter('relationship_category_all');
          $this->extensions = ExtensionDefinitionTable::getByTier(2, $this->default_type);
          $extensions_arr = array();
          foreach($this->extensions as $ext)
          {
            $extensions_arr[] = $ext->name;
          }
        }
        else
        {
          $category_name = $request->getParameter('relationship_category_one');
        }
        if($category_name)
        {
          $this->category_name = $category_name;
          if (!$category = Doctrine::getTable('RelationshipCategory')->findOneByName($category_name))
          {
            $request->setError('csv', 'You did not select a relationship category.');
            return;
          }
          $formClass = $category_name . 'Form';
          
          $categoryForm = new $formClass(new Relationship);
      
          $categoryForm->setDefaults($defaults);
      
          $this->form_schema = $categoryForm->getFormFieldSchema();
      
          if (in_array($category_name, array('Position', 'Education', 'Membership', 'Donation', 'Lobbying', 'Ownership')))
          {
            $this->field_names = array('description1', 'start_date', 'end_date', 'is_current');
          }
          else
          {
            $this->field_names = array('description1', 'description2', 'start_date', 'end_date', 'is_current');
          }
          
          $extraFields = array(
            'Position' => array('is_board', 'is_executive'),
            'Education' => array('degree_id'),
            'Donation' => array('amount'),
            'Transaction' => array('amount'),
            'Lobbying' => array('amount'),
            'Ownership' => array('percent_stake', 'shares')
          );
          
          if (isset($extraFields[$category_name]))
          {
            $this->field_names = array_merge($this->field_names, $extraFields[$category_name]);
          }
        }
        
        $this->matches = array();
        
        // BOOT TO TOOLBAR OR LOOK FOR MATCHES FOR ENMASSE ADD
        
        if ((isset($names) && count($names) > 0) || isset($this->db_search))
        { 
          if ($verify_method == 'onebyone')
          {
            if(isset($category_name))
            {
              $defaults['category'] = $category_name;
            }
            $toolbar_names = array();
            foreach($names as $name)
            {
              $toolbar_names[] = $name['name'];      
            }
            $this->getUser()->setAttribute('toolbar_names', $toolbar_names);
            $this->getUser()->setAttribute('toolbar_entity', $this->entity->id);
            $this->getUser()->setAttribute('toolbar_defaults', $defaults);
            $this->getUser()->setAttribute('toolbar_ref', $this->ref_id);
            $this->redirect('relationship/toolbar'); 
          }
          else
          {  
          	
            $this->category_name = $category_name;
            if (isset($this->db_search))
            {
            	$num = $request->getParameter('num', 10);
      				$page = $request->getParameter('page', 1);
      
            	$q = LsDoctrineQuery::create()
        				->from('Entity e')
        				->where('(e.summary rlike ? or e.blurb rlike ?)',array('[[:<:]]' . $this->entity->name . '[[:>:]]','[[:<:]]' . $this->entity->name . '[[:>:]]'));
        				
							foreach($this->entity->Alias as $alias)
							{
								$q->orWhere('(e.summary rlike ? or e.blurb rlike ?)',array('[[:<:]]' . $alias->name . '[[:>:]]','[[:<:]]' . $alias->name . '[[:>:]]'));
							}
							$q->setHydrationMode(Doctrine::HYDRATE_ARRAY);
							$cat_id = constant('RelationshipTable::' . strtoupper($category_name) . '_CATEGORY');
							$q->whereParenWrap();
							$q->andWhere('NOT EXISTS (SELECT DISTINCT l.relationship_id FROM Link l ' .
											'WHERE l.entity1_id = e.id AND l.entity2_id = ? AND l.category_id = ?)',
											 array($this->entity['id'], $cat_id));
							$summary_matches = $q->execute();
        	
            	foreach($summary_matches as $summary_match)
            	{
								$aliases = array();
								foreach($this->entity->Alias as $alias)
								{
									$aliases[] = LsString::escapeStringForRegex($alias->name);
								}
								$aliases = implode("|",$aliases);
								$summary_match['summary'] = preg_replace('/('. $aliases . ')/is','<strong>$1</strong>',$summary_match['summary']);
								$this->matches[] = array('search_results' => array($summary_match));
            	}
            }
            else
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
											$name_terms = PersonTable::nameSearch($name);
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
									$match = $names[$i];
									
									$match['search_results'] = $pager->execute();
									if(isset($names[$i]['types']))
									{
										$types = explode(',',$names[$i]['types']);
										$types = array_map('trim',$types);
										$match['types'] = array();
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
      }  
    }
    
    else if ($page = $this->getRequestParameter('page'))
    {
    	$this->page = $page;
    	$this->num = $this->getRequestParameter('num', 50);
    }
    
    
    // CREATE NEW RELATIONSHIPS
    
    else if ($request->isMethod('post') && $request->getParameter('commit') == 'Submit')
    { 
      $this->ref_id = $this->getRequestParameter('ref_id');  
      $entity_ids = array();
      $relationship_category = $this->getRequestParameter('category_name');
      $order = $this->getRequestParameter('order');
      
      $default_type = $request->getParameter('default_type');
  		$default_ref = Doctrine::getTable('Reference')->find($request->getParameter('ref_id'));
           
      for ($i = 0; $i < $this->getRequestParameter('count'); $i++)
      {
        if ($entity_id = $request->getParameter('entity_' . $i))
        {
          $selected_entity_id = null;
	  $relParams = $request->getParameter("relationship_" . $i);
          if ($relParams['ref_name'])
	  {
		$ref['source'] = $relParams['ref_source'];
		$ref['name'] = $relParams['ref_name'];
	  }

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
            $new_entity->save();
            $new_entity->blurb = $request->getParameter('new_blurb_' . $i);
            $new_entity->summary = $request->getParameter('new_summary_' . $i);
  				
  	    if (!$ref) $ref = $default_ref;
            $new_entity->addReference($ref['source'], null, null, $ref['name']);
            
            if ($types = $request->getParameter('new_extensions_' . $i))
						{
							foreach($types as $type)
							{
								$new_entity->addExtension($type);
							}
						}
						
            $new_entity->save();
            
            $selected_entity_id = $new_entity->id;
          }
          else if ($entity_id > 0)
          {
            $selected_entity_id = $entity_id;
            LsCache::clearEntityCacheById($selected_entity_id);
          }
          if ($selected_entity_id)
          {
            $startDate = $relParams['start_date'];
            $endDate = $relParams['end_date'];
            unset($relParams['start_date'], $relParams['end_date'], $relParams['ref_name'],$relParams['ref_url']);            
            $rel = new Relationship;
            $rel->setCategory($relationship_category);
            if ($order == '1')
            {
              $rel->entity1_id = $this->entity['id'];
              $rel->entity2_id = $selected_entity_id;
            }
            else
            {
              $rel->entity2_id = $this->entity['id'];
              $rel->entity1_id = $selected_entity_id;
            }
            
            //only set dates if valid
            if ($startDate && preg_match('#^\d{4}-\d{2}-\d{2}$#', Dateable::convertForDb($startDate)))
            {
              $rel->start_date = Dateable::convertForDb($startDate);
            }
           
            if ($endDate && preg_match('#^\d{4}-\d{2}-\d{2}$#', Dateable::convertForDb($endDate)))
            {
              $rel->end_date = Dateable::convertForDb($endDate);
            }
      
            $rel->fromArray($relParams, null, $hydrateCategory=true);
            if ($request->hasParameter('add_method') && $request->getParameter('add_method') == 'db_search')
            {
            	$refs = EntityTable::getSummaryReferences($selected_entity_id);
            	if (count($refs))
            	{
            		$ref = $refs[0];
              }
              else
              {
              	$refs = EntityTable::getAllReferencesById($selected_entity_id);
              	if (count($refs))
              	{
              		$ref = $refs[0];
              	}
              }
            }
            if (!$ref) $ref = $default_ref;
            $rel->saveWithRequiredReference(array('source' => $ref['source'], 'name' => $ref['name']));
            $ref = null;
          }
        }
      }
			$this->clearCache($this->entity);
      $this->redirect($this->entity->getInternalUrl());
    }  
    else if ($request->isMethod('post') && $request->getParameter('commit') == 'Cancel')
    { 
    	$this->redirect($this->entity->getInternalUrl());
    }
  }

  
  public function executeEditBlurbInline($request)
  {
    $id = $request->getParameter('id') ? $request->getParameter('id') : $request->getParameter('entity[id]');
    $this->entity = Doctrine::getTable('Entity')->find($id);
    $this->forward404Unless($this->entity);
    
    $blurb = $request->getParameter('blurb');
   	$this->entity->blurb = $blurb;
   	$this->entity->save();
	  $this->clearCache($this->entity);   	
  }  
  
  
  public function executeMatchRelated($request)
  {  
    $this->checkEntity($request);

    //only allowed for persons
    $this->forward404Unless($this->entity['primary_ext'] == 'Org');
		
		$db = Doctrine_Manager::connection();
			
		$sql = 'SELECT e.* FROM os_entity_transaction et LEFT JOIN entity e ON (et.entity_id = e.id) LEFT JOIN relationship r on r.entity1_id = e.id WHERE e.is_deleted=0 AND r.entity2_id = ? AND r.is_deleted=0 AND (r.category_id = 1 OR r.category_id = 3) AND et.reviewed_at IS NULL AND et.locked_at IS NULL GROUP BY e.id LIMIT 100'; 
		
		$stmt = $db->execute($sql, array($this->entity['id']));
		$this->relatedIds = $stmt->fetchAll(PDO::FETCH_COLUMN);   
		if (count($this->relatedIds))
		{
    	$nextEntity = Doctrine::getTable('Entity')->find($this->relatedIds[0]);
    	$this->getUser()->setAttribute('os_related_ids', $this->relatedIds);
			$this->redirect(EntityTable::getInternalUrl($nextEntity, 'matchDonations'));
		}
		else $this->redirect(EntityTable::getInternalUrl($this->entity));
    
  }
  
  public function executePolitical($request)
  {
    $this->checkEntity($request);
    $this->logRecentView();

    $this->start_cycle = $request->getParameter('start_cycle','1990');
 		$this->end_cycle = $request->getParameter('end_cycle','2012');

    if ($request->isXmlHttpRequest())
    {
      return $this->renderComponent('entity', 'political', array(
        'entity' => $this->entity,
        'start_cycle' => $this->start_cycle,
        'end_cycle' => $this->end_cycle
      ));
    }

    $this->tab_name = 'political';

    $this->getResponse()->addJavaScript('d3');
    $this->setTemplate('view');
  }
  
  public function executeEditIndustries($request)
  {
    $this->checkEntity($request, false, false);    
    $this->categories = OsEntity::getCategories($this->entity['id']);
    usort($this->categories, array('OsCategoryTable', 'categoryCmp'));
  }
  
  public function executeRemoveIndustry($request)
  {
    $this->checkEntity($request);

    if (!$request->isMethod('post'))
    {
      $this->forward('error', 'invalid');    
    }
    
    $ec = LsDoctrineQuery::create()
      ->from('OsEntityCategory ec')
      ->where('ec.entity_id = ?', $this->entity['id'])
      ->andWhere('ec.category_id = ?', $request->getParameter('category'))
      ->fetchOne();

    $this->forward404Unless($ec);
    $ec->delete();    
    $this->redirect(EntityTable::getInternalUrl($this->entity, 'editIndustries'));
  }
  
  public function executeAddIndustry($request)
  {
    $this->checkEntity($request, false, false);
    
    if ($request->isMethod('post'))
    {
      $catId = $request->getParameter('category');

      $count = LsDoctrineQuery::create()
        ->from('OsCategory c')
        ->where('c.category_id = ?', $catId)
        ->count();

      if (!$count)
      {
        $this->forward404();        
      }

      if (!in_array($catId, OsEntity::getCategoryIds($this->entity['id'])))
      {
        $ec = new OsEntityCategory;
        $ec->entity_id = $this->entity['id'];
        $ec->category_id = $catId;
        $ec->source = "user_id: " . $this->getUser()->getGuardUser()->id;
        $ec->save();
      }

      $this->redirect(EntityTable::getInternalUrl($this->entity, 'editIndustries'));
    }
    
    $this->categories = Doctrine::getTable('OsCategory')->findAll(Doctrine::HYDRATE_ARRAY);
    usort($this->categories, array('OsCategoryTable', 'categoryCmp'));
    
    $this->existing_category_ids = OsEntity::getCategoryIds($this->entity['id']);
  }

  public function executeUpdateIndustries($request)
  {
    $this->checkEntity($request, false, false);
    
    if (!$request->isMethod('post'))
    {
      $this->forward('error', 'invalid');        
    }
    
    if ($this->entity['primary_ext'] == 'Person')
    {
      $new = OsPerson::updateCategories($this->entity['id']);
    }
    else
    {
      $new = OsOrg::updateCategories($this->entity['id'], $this->entity['name'], $exactNameOverride=true);
    }
    
    $this->redirect(EntityTable::getInternalUrl($this->entity, 'editIndustries'));
  }
  
  public function executeMap($request)
  {
    $this->checkEntity($request, false, false);
    $num = $request->getParameter("num", 12);

    if ($request->getParameter("use_interlocks", "0") == "0")
    {
      //excluding donations from starting entity map right now
      $this->data = json_encode(EntityTable::getRelatedEntitiesAndRelsForMap($this->entity->id, $num, array(), array(5)));
    }
    else
    {
    
      $order1 = ($this->entity['primary_ext'] == 'Person') ? 1 : 2;
      $order2 = ($this->entity['primary_ext'] == 'Person') ? 2 : 1;

      $options = array(
        //'cat1_ids' => RelationshipTable::POSITION_CATEGORY . ',' . RelationshipTable::MEMBERSHIP_CATEGORY,
        //'cat2_ids' => RelationshipTable::POSITION_CATEGORY . ',' . RelationshipTable::MEMBERSHIP_CATEGORY,
        'cat1_ids' => "1,2,3,4,6,7",
        'cat2_ids' => "1,2,3,4,6,7",
        'order1' => $order1,
        'order2' => $order2,
        'page' => 1,
        'num' => 5
      );
    
      $interlocks = EntityApi::getSecondDegreeNetwork($this->entity['id'], $options);

      $degree1_ids = array();
      $degree1_scores = array();
    
      foreach ($interlocks as $i) 
      {
        $new_degree1_ids = explode(",", $i["degree1_ids"]);
    
        foreach ($new_degree1_ids as $id)
        {
          $degree1_scores[$id] = isset($degree1_scores[$id]) ? $degree1_scores[$id] + 1 : 1;
        }
      
        $degree1_ids = array_merge($degree1_ids, $new_degree1_ids);
      }

      arsort($degree1_scores);
      $degree1_ids = array_keys($degree1_scores);
      $degree1_ids = array_slice($degree1_ids, 0, $num);
    
      $entity_ids = array_unique(array_merge(array($this->entity["id"]), $degree1_ids));

      $data = EntityTable::getEntitiesAndRelsForMap($entity_ids);
      $entities = array();

      foreach ($data["entities"] as $e)
      {
        array_push($entities, $e);
      }

      $this->data = json_encode(array("entities" => $entities, "rels" => $data["rels"]));   
    }
  }
  
  public function executeInterlocksMap($request)
  {
    $this->checkEntity($request, false, false);
    $num = $request->getParameter("num", 6);  
    $degree1_num = $request->getParameter("degree1_num", 10);

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
    
    $interlocks = EntityApi::getSecondDegreeNetwork($this->entity['id'], $options);

    $degree1_ids = array();
    $degree2_ids = array();
    $degree1_scores = array();
    
    foreach ($interlocks as $i) 
    {
      $new_degree1_ids = explode(",", $i["degree1_ids"]);
      
      foreach ($new_degree1_ids as $id)
      {
        $degree1_scores[$id] = isset($degree1_scores[$id]) ? $degree1_scores[$id] + 1 : 1;
      }
      
      $degree1_ids = array_merge($degree1_ids, $new_degree1_ids);
      $degree2_ids[] = $i["id"];
    }

    arsort($degree1_scores);
    $degree1_ids = array_keys($degree1_scores);
    $degree1_ids = array_slice($degree1_ids, 0, $degree1_num);
    
    $entity_ids = array_unique(array_merge(array($this->entity["id"]), $degree1_ids, $degree2_ids));
    $cats = array(RelationshipTable::POSITION_CATEGORY, RelationshipTable::MEMBERSHIP_CATEGORY);

    $data = EntityTable::getEntitiesAndRelsForMap($entity_ids, $cats);

    /*
    foreach ($data["entities"] as $i => $entity)
    {
      $data["entities"][$i]["url"] = preg_replace("/map$/", "interlocksMap", $entity["url"]);
    }
    */

    $entities = array();

    foreach ($data["entities"] as $e)
    {
      array_push($entities, $e);
    }

    $this->data = json_encode(array("entities" => $entities, "rels" => $data["rels"]));
    $this->degree1_ids = $degree1_ids;  
    $this->degree2_ids = $degree2_ids;
  }

  public function executeDatatable($request) 
  {
    $this->checkEntity($request, false, false);
    $this->redirect(EntityTable::railsUrl($this->entity, "relationships", true));
  }

  public function executeEditFields($request) 
  {
    $this->checkEntity($request, false, false);
    $this->redirect(EntityTable::railsUrl($this->entity, "fields", true));
  }

  public function executeFindArticles($request)
  {
    $this->checkEntity($request, false, false);
    $this->redirect(EntityTable::railsUrl($this->entity, "find_articles", true));
  }

  public function executeArticles($request)
  {
    $this->checkEntity($request, false, false);
    $this->redirect(EntityTable::railsUrl($this->entity, "articles", true));
  }  
}
