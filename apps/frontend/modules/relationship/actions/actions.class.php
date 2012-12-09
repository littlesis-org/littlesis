<?php

/**
 * relationship actions.
 *
 * @package    ls
 * @subpackage relationship
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class relationshipActions extends sfActions
{
  public function checkRelationship($request, $includeDeleted=false)
  {
    if ($includeDeleted)
    {
      $this->relationship = LsDoctrineQuery::create()
        ->from('Relationship r')
        ->leftJoin('r.Entity1 e1')
        ->leftJoin('r.Entity2 e2')
        ->where('r.id = ?', $request->getParameter('id'))
        ->addWhere('r.is_deleted IS NOT NULL')
        ->fetchOne();    
    }
    else
    {
      $this->relationship = Doctrine::getTable('Relationship')->find($request->getParameter('id'));
    }
    
    $this->forward404Unless($this->relationship);  
  }


  public function checkEntitiesAndCategory($request)
  {
    $this->entity1 = Doctrine::getTable('Entity')->find($request->getParameter('e1'));
    $this->forward404Unless($this->entity1);

    $this->entity2 = Doctrine::getTable('Entity')->find($request->getParameter('e2'));
    $this->forward404Unless($this->entity2);
    
    $this->category = Doctrine::getTable('RelationshipCategory')->findOneByName($request->getParameter('category'));
    $this->forward404Unless($this->category);  
  }


  public function clearCache(Relationship $r)
  {
    LsCache::clearRelationshipCacheById($r->id);
    LsCache::clearUserCacheById($this->getUser()->getGuardUser()->id);
  }
  

  public function executeView($request)
  {    
    $this->relationship = RelationshipApi::get($request->getParameter('id'));
    $this->forward404Unless($this->relationship);

    $this->relationship = array_merge(
      $this->relationship, 
      RelationshipApi::getDetails(
        $this->relationship['id'], 
        $this->relationship['category_id']
      )
    );
    $this->current = null;
    if ($this->relationship['is_current'] == '1')
    {
      $this->current = 1;
    }
    else if ($this->relationship['is_current'] == '0' || (!$this->relationship['is_current'] && $this->relationship['end_date']))
    {
      $this->current = 0;
    }
    
    
    $this->relationship['Entity1'] = EntityApi::get($this->relationship['entity1_id']);
    $this->relationship['Entity2'] = EntityApi::get($this->relationship['entity2_id']);
  }


  public function executeRemove($request)
  {
    if (!$request->isMethod('post'))
    {
      $this->redirect('error/invalid');
    }

    $this->checkRelationship($request);

    $this->clearCache($this->relationship);
    
    $this->relationship->delete();

    $this->redirect('@homepage');
  }


  public function executeEdit($request)
  {
    $this->checkRelationship($request);

    $this->entity1 = $this->relationship->Entity1;
    $this->entity2 = $this->relationship->Entity2;
    $this->category = $this->relationship->Category;

    $categoryId = $this->category->id;
    $categoryClass = $this->category->name;
    $formClass = $categoryClass . 'Form';


    //switch Entity order
    if ($request->getParameter('switch'))
    {
      $this->relationship->switchEntityOrder();
    }


    //create forms    
    $this->category_form = new $formClass($this->relationship);
    $this->reference_form = new ReferenceForm;
    $autoSelect = ($request->getParameter('ref') == 'auto') ? true : false;
    $this->reference_form->setSelectObject($this->relationship, $autoSelect);


    if ($request->isMethod('post'))
    {
      $params = $request->getParameter('relationship');
      $refParams = $request->getParameter('reference');

      $this->category_form->bind($params);    
      $this->reference_form->bind($refParams);    


      if ($this->category_form->isValid() && $this->reference_form->isValid())
      {
        $db = Doctrine_Manager::connection();
      
        try
        {
          $db->beginTransaction();

          $r = $this->relationship;
          $r->fromArray($params, null, $hydrateCategory=true);

          if (isset($params['description_new']) && !$params['description1'] && $description = $params['description_new'])
          {
            $r->description1 = $description;
          }

          $r->saveWithRequiredReference($refParams);

          $db->commit();
        }
        catch (Exception $e)
        {
          $db->rollback();
          throw $e;          
        }
          
        $this->clearCache($r);  
          
        $this->redirect($r->getInternalUrl());
      }    
    }
    
    $this->setTemplate('edit' . $categoryClass);
  }


  public function executeDescriptions($request)
  {
    $params = $request->getParameter('relationship');
    $categoryId = $request->getParameter('category_id');
    $order = $request->getParameter('order');

    if ($order == 2)
    {
      $description = $params['description2'];
    }
    else
    {
      $description = $params['description1'];    
    }    

    $this->descriptions = RelationshipTable::getDescriptionsByText($description, $categoryId);
  }


  public function executeFindEntity($request)
  {
    $this->entityField = $request->getParameter('entity_field');
    $this->forward404Unless($this->entityField);


    $terms = $request->getParameter(strtolower($this->entityField) . '_terms');
    $terms = preg_replace('/\s{2,}/', ' ', $terms);
    $terms = explode(' ', $terms);

    //search for query that excludes the current Entity1
    $q = EntityTable::getSimpleSearchQuery($terms);

    $num = $request->getParameter('num', 10);
    $page = $request->getParameter('page', 1);

    $this->entity_pager = new LsDoctrinePager($q, $page, $num);  
    
    return $this->renderPartial('relationship/entityresults');
  }
  
  
  public function executeModifications($request)
  {
    $this->checkRelationship($request, $includeDeleted=true);
    
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);
    $userId = $request->getParameter('user_id');

    if ($request->getParameter('group_by_user'))
    {
      $q = $this->relationship->getModificationsQuery()
        ->addWhere('NOT EXISTS( SELECT id FROM modification WHERE modification.user_id = m.user_id AND modification.created_at > m.created_at )');
      
      $this->modification_pager = new LsDoctrinePager($q, $page, $num);    
    }
    else
    {
      $this->modification_pager = new LsDoctrinePager(
        $this->relationship->getModificationsQuery($userId)->setHydrationMode(Doctrine::HYDRATE_ARRAY),
        $page,
        $num
      );
    }
  }


  public function executeTagModifications($request)
  {
    $this->checkRelationship($request, $includeDeleted=true);

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);

    $q = $this->relationship->getTagModificationsQuery();
    $this->tag_modification_pager = new LsDoctrinePager($q, $page, $num);
  }


  public function executeComments($request)
  {
    $request->setParameter('model', 'Relationship');

    $this->forward('comment', 'list');
  }
  
  
  public function executeAddComment($request)
  {
    $request->setParameter('model', 'Relationship');

    $this->forward('comment', 'add');  
  }


  public function executeNotes($request)
  {
    $this->checkRelationship($request);

    $this->note_form = new NoteForm;
    
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);
    
    $s = new LsSphinxClient($page, $num);
    $s->setFilter('relationship_ids', array($this->relationship['id']));
    $s->setFilter('visible_to_user_ids', array_unique(array(0, sfGuardUserTable::getCurrentUserId())));
    
    if ($userId = $request->getParameter('user_id'))
    {
      $s->setFilter('user_id', array($userId));
    }
    
    $this->note_pager = NoteTable::getSphinxPager($s, null, Doctrine::HYDRATE_ARRAY);  
  }
  

  public function executeReferences($request)
  {
    $this->checkRelationship($request);

    $request->setParameter('model', 'Relationship');
    $request->setParameter('id', $this->relationship->id);

    $this->forward('reference', 'list');
  }
  

  public function checkToolbarCredentials($isPage=false)
  {
    $this->getRequest()->setParameter('no_layout', true);

    if (!$this->getUser()->isAuthenticated())
    {
      if ($isPage)
      {
        $this->getRequest()->setParameter('referer', $this->getRequest()->getUri());
        $this->forward('sfGuardAuth', 'signin');
      }
      else
      {
        $this->forward404();
      }
    }
    
    if (!$this->getUser()->hasCredential('importer'))
    {
      if ($isPage)
      {
        $this->forward('error', 'credential');
      }
      else
      {
        $this->forward404();
      }
    }
  }

  
  public function executeToolbar($request)
  {
    $this->checkToolbarCredentials(true);

    if ($request->isMethod('post'))
    {
      //if user wants to skip this relationship
      if ($request->getParameter('commit') == 'Skip')
      {
        $names = $this->getUser()->getAttribute('toolbar_names');
        array_shift($names);

        if (count($names))
        {
          $this->getUser()->setAttribute('toolbar_names', $names);
          $this->redirect('relationship/toolbar');
        }
        else
        {
          $entityId = $this->getUser()->getAttribute('toolbar_entity');
          $entity = Doctrine::getTable('Entity')->find($entityId);          

          $this->getUser()->setAttribute('toolbar_names', null);
          $this->getUser()->setAttribute('toolbar_ref', null);            
          $this->getUser()->setAttribute('toolbar_entity', null);
          $this->getUser()->setAttribute('toolbar_defaults', null);
          
          $this->redirect($entity->getInternalUrl());
        }
      }
      
      //if user wants to clear bulk queue
      if ($request->getParameter('commit') == 'Clear')
      {
        $entityId = $this->getUser()->getAttribute('toolbar_entity');
        $entity = Doctrine::getTable('Entity')->find($entityId);          

        $this->getUser()->setAttribute('toolbar_names', null);
        $this->getUser()->setAttribute('toolbar_ref', null);            
        $this->getUser()->setAttribute('toolbar_entity', null);
        $this->getUser()->setAttribute('toolbar_defaults', null);
        
        $this->redirect($entity->getInternalUrl());
      }

      $entity1Id = $request->getParameter('entity1_id');
      $entity2Id = $request->getParameter('entity2_id');
      $categoryName = $request->getParameter('category_name');
      $refSource = $request->getParameter('reference_source');
      $refName = $request->getParameter('reference_name');      

      $categoryParams = $request->getParameter('relationship');
      $startDate = $categoryParams['start_date'];
      $endDate = $categoryParams['end_date'];
      unset($categoryParams['start_date'], $categoryParams['end_date']);
      
      if (!$entity1Id || !$entity2Id || !$categoryName || !$refSource || !$refName)
      {
        $this->forward('error', 'invalid');
      }
      
      if (!$entity1 = EntityApi::get($entity1Id))
      {
        $this->forward('error', 'invalid');
      }
      
      if (!$entity2 = EntityApi::get($entity2Id))
      {
        $this->forward('error', 'invalid');      
      }
      
      $db = Doctrine_Manager::connection();
      $sql = 'SELECT name FROM relationship_category ' . 
             'WHERE (entity1_requirements IS NULL OR entity1_requirements = ?) ' .
             'AND (entity2_requirements IS NULL OR entity2_requirements = ?)';
      $stmt = $db->execute($sql, array($entity1['primary_ext'], $entity2['primary_ext']));
      $validCategoryNames = $stmt->fetchAll(PDO::FETCH_COLUMN);

      if (!in_array($categoryName, $validCategoryNames))
      {
        $request->setError('category', 'Invalid relationship; try changing the category or switching the entity order');

        //check session for bulk names
        if ($bulkEntityId = $this->getUser()->getAttribute('toolbar_entity'))
        {
          if ($this->entity1 = Doctrine::getTable('Entity')->find($bulkEntityId))
          {        
            if ($names = $this->getUser()->getAttribute('toolbar_names'))
            {
              $this->entity2_name = array_shift($names);
              
              if ($refId = $this->getUser()->getAttribute('toolbar_ref'))
              {
                $this->ref = Doctrine::getTable('Reference')->find($refId);
                $request->getParameterHolder()->set('title', $this->ref->name);
                $request->getParameterHolder()->set('url', $this->ref->source);
              }
              
              if ($defaults = $this->getUser()->getAttribute('toolbar_defaults'))
              {
                if (isset($defaults['category']))
                {
                  $this->category = $defaults['category'];
                }
              }
            }
          }      
        }
    
        
        if ($createdId = $request->getParameter('created_id'))
        {
          $this->created_rel = Doctrine::getTable('Relationship')->find($createdId);
        }

        return sfView::SUCCESS;
      }
      
      if (!preg_match('/^http(s?)\:\/\/.{3,193}/i', $refSource))
      {
        $this->forward('error', 'invalid');                  
      }
      
      //all's well, create relationship!
      $rel = new Relationship;
      $rel->setCategory($categoryName);
      $rel->entity1_id = $entity1['id'];
      $rel->entity2_id = $entity2['id'];

      //only set dates if valid
      if ($startDate && preg_match('#^\d{4}-\d{2}-\d{2}$#', Dateable::convertForDb($startDate)))
      {
        $rel->start_date = Dateable::convertForDb($startDate);
      }
     
      if ($endDate && preg_match('#^\d{4}-\d{2}-\d{2}$#', Dateable::convertForDb($endDate)))
      {
        $rel->end_date = Dateable::convertForDb($endDate);
      }

      $rel->fromArray($categoryParams, null, $hydrateCategory=true);
      $rel->save();
      
      //create reference
      $ref = new Reference;
      $ref->name = $refName;
      $ref->source = $refSource;
      $ref->object_id = $rel->id;
      $ref->object_model = 'Relationship';
      $ref->save();

      $redirect = 'relationship/toolbar?url=' . $refSource . '&title=' . $refName . '&created_id=' . $rel->id;


      //if there's a bulk queue, remove one from the start
      if ($isBulk = $request->getParameter('is_bulk'))
      {
        $names = $this->getUser()->getAttribute('toolbar_names');
        array_shift($names);

        if (count($names))
        {
          $this->getUser()->setAttribute('toolbar_names', $names);

          //keep track of entity order while in queue
          $this->getUser()->setAttribute('toolbar_switched', $request->getParameter('is_switched', 0));
          
          $redirect = 'relationship/toolbar?created_id=' . $rel->id;
        }
        else
        {
          //queue is finished; go to entity profile
          $entityId = $this->getUser()->getAttribute('toolbar_entity');
          $entity = Doctrine::getTable('Entity')->find($entityId);          
          $redirect = $entity->getInternalUrl();

          $this->getUser()->setAttribute('toolbar_names', null);
          $this->getUser()->setAttribute('toolbar_ref', null);            
          $this->getUser()->setAttribute('toolbar_entity', null);          
          $this->getUser()->setAttribute('toolbar_defaults', null);
          $this->getUser()->setAttribute('toolbar_switched', null);
        }        
      }


      LsCache::clearEntityCacheById($entity1['id']);
      LsCache::clearEntityCacheById($entity2['id']);
      
      $this->redirect($redirect);
    }    


    //check session for bulk names
    if ($bulkEntityId = $this->getUser()->getAttribute('toolbar_entity'))
    {
      if ($this->entity1 = Doctrine::getTable('Entity')->find($bulkEntityId))
      {        
        if ($names = $this->getUser()->getAttribute('toolbar_names'))
        {
          $this->entity2_name = array_shift($names);
          
          if ($refId = $this->getUser()->getAttribute('toolbar_ref'))
          {
            $this->ref = Doctrine::getTable('Reference')->find($refId);
            $request->getParameterHolder()->set('title', $this->ref->name);
            $request->getParameterHolder()->set('url', $this->ref->source);
          }
          
          if ($defaults = $this->getUser()->getAttribute('toolbar_defaults'))
          {
            if (isset($defaults['category']))
            {
              $this->category = $defaults['category'];
            }
          }
        }
      }
      
      $this->is_switched = $this->getUser()->getAttribute('toolbar_switched', 0);
    }

    
    if ($createdId = $request->getParameter('created_id'))
    {
      $this->created_rel = Doctrine::getTable('Relationship')->find($createdId);
    }
    

    $this->setLayout($bulkEntityId ? 'layout' : 'toolbar');
  }


  public function executeToolbarCreate($request)
  {
    $this->checkToolbarCredentials();

    if (!$request->isMethod('post'))
    {
      $this->forward404();
    }
    
    $name = $request->getParameter('name');
    $ext = $request->getParameter('ext');
    $blurb = $request->getParameter('blurb');
    $position = $request->getParameter('position');
    $listId = $request->getParameter("list_id");

    //save list_id to session for further use
    //$this->getUser()->setAttribute('list' . $position . '_id', $listId);

    if (!$name || !$ext)
    {
      $this->forward404();
    }
    
    if ($ext == 'Person')
    {
      $entity = PersonTable::parseFlatName($name);
      $entity->name = $name;
      
      if (!$entity->name_last)
      {
        $this->forward404();    
      }
    }
    else
    {
      $entity = new Entity;
      $entity->addExtension('Org');
      $entity->name = $name;
    }
    
    $entity->blurb = $blurb;
    $entity->save();

    if ($listId)
    {
      $db = Doctrine_Manager::connection();
      $sql = "SELECT COUNT(*) FROM ls_list WHERE id = ? AND is_network = 0 AND is_deleted = 0";

      if (!$this->getUser()->hasCredential('admin'))
      {
        $sql .= " AND is_admin = 0";
      }

      $stmt = $db->execute($sql, array($listId));
      $count = $stmt->fetch(PDO::FETCH_COLUMN);
      
      if ($count == "1")
      {
        $sql = "SELECT COUNT(*) FROM ls_list_entity WHERE list_id = ? AND entity_id = ? AND is_deleted = 0";
        $stmt = $db->execute($sql, array($listId, $entity->id));
        $count = $stmt->fetch(PDO::FETCH_COLUMN);

        if ($count == "0")
        {
          $le = new LsListEntity;
          $le->list_id = $listId;
          $le->entity_id = $entity->id;
          $le->save();
        }
      }    
      else
      {
        //save list_id to session for further use
        //$this->getUser()->setAttribute('list' . $position . '_id', null);
      }
    }
    
    $this->entity = $entity;

    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
  }


  public function executeToolbarSearch($request)
  {
    $this->checkToolbarCredentials();

		if (!$request->isMethod('post'))
		{
      $this->forward404();
		}
		else
		{
			$num = $request->getParameter('num', 7);
			$this->page = $request->getParameter('page', 1);
      $this->position = $request->getParameter('position');

      if (!$terms = $request->getParameter('q'))
      {
        $this->entities = array();
      }
      else
      {  
        $pager = EntityTable::getSphinxPager($terms, $this->page, $num);  
        $this->entities = $pager->execute();
        $this->total = $pager->getNumResults();
      }
    }
  }
  
  
  public function executeToolbarCategories($request)
  {
    $this->checkToolbarCredentials();

    $ext1 = $request->getParameter('ext1');
    $ext2 = $request->getParameter('ext2');
    
    $this->categories = RelationshipCategoryTable::getByExtensionsQuery($ext1, $ext2)
      ->select('c.name')
      ->fetchArray();
  }
  

  public function executeToolbarCategoryFields($request)
  {
    $this->checkToolbarCredentials();

		if (!$request->isMethod('post'))
		{
		  $this->forward404();
		}
		
		$name = $request->getParameter('name');
    $this->condensed = $request->getParameter('condensed', true);
		
		if (!$category = Doctrine::getTable('RelationshipCategory')->findOneByName($name))
		{
		  $this->forward404();
		}

		$formClass = $name . 'Form';
		$categoryForm = new $formClass(new Relationship);

    if ($defaults = $this->getUser()->getAttribute('toolbar_defaults'))
    {
      $categoryForm->setDefaults($defaults);
    }

    $this->form_schema = $categoryForm->getFormFieldSchema();

    if (in_array($name, array('Position', 'Education', 'Membership', 'Donation', 'Lobbying', 'Ownership')))
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
    
    if (isset($extraFields[$name]))
    {
      $this->field_names = array_merge($this->field_names, $extraFields[$name]);
    }    
  }


  public function executeToolbarCheckExisting($request)
  {
    $this->checkToolbarCredentials();
    
		if (!$request->isMethod('post'))
		{
		  $this->forward404();
		}
		
    $id1 = $request->getParameter('entity1_id');
    $id2 = $request->getParameter('entity2_id');
		$name = $request->getParameter('name');

    if (!$id1 || !$id2 || !$name)
    {
      $this->forward404();
    }
		
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT DISTINCT l.relationship_id FROM link l ' .
           'LEFT JOIN relationship_category c ON (c.id = l.category_id) ' .
           'WHERE l.entity1_id = ? AND l.entity2_id = ? AND c.name = ?';
    $stmt = $db->execute($sql, array($id1, $id2, $name));
    
    $this->relIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
  }


  public function executeRefresh($request)
  {
    $this->checkRelationship($request);
    LsCache::clearRelationshipCacheById($this->relationship['id']);
    
    $this->redirect($request->getParameter('ref', RelationshipTable::getInternalUrl($this->relationship)));
  }
}