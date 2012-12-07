<?php

class groupActions extends sfActions
{
  public function checkGroup($request)
  {
    if (!$this->group = Doctrine::getTable('sfGuardGroup')->findOneByName($request->getParameter('name')))
    {
      if ($this->group = Doctrine::getTable('sfGuardGroup')->find($request->getParameter('name')))
      {
        $this->redirect($this->group->getInternalUrl($request->getParameter('action')));
      }
    }

    $this->forward404Unless($this->group && $this->group->is_working);
  }
  
  
  public function checkUser($request)
  {
    if ($this->getUser()->isAuthenticated())
    {
      $this->getUser()->getGuardUser()->reloadGroupsAndPermissions();
      $this->getUser()->getGuardUser()->loadGroupsAndPermissions();
    }
  
    if ($this->group->is_private && !$this->getUser()->hasGroup($this->group->name))
    {
      $this->forward('error', 'credentials');
    }
  }
  
  
  public function checkOwner()
  {
    if (!$this->getUser()->hasCredential('admin') && !$this->getUser()->getGuardUser()->isGroupOwner($this->group->name))
    {
      $this->forward('error', 'credentials');    
    }
  }
  
  
  public function clearCache($group)
  {
    LsCache::clearGroupCacheByName($group['name']);
  }
  

  public function executeView($request)
  {
    $this->checkGroup($request);
    $this->checkUser($request);

    $this->is_group_owner = ($this->getUser()->isAuthenticated() && $this->getUser()->getGuardUser()->isGroupOwner($this->group['name']));
    
    //get members
    $q = LsDoctrineQuery::create()
      ->from('sfGuardUser u')
      ->leftJoin('u.Profile p')
      ->leftJoin('u.sfGuardUserGroup ug')
      ->where('ug.group_id = ?', $this->group->id)
      ->orderBy('rand()')
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);
      
    $this->user_pager = new LsDoctrinePager($q, $page=1, $num=10);

    //get top analysts
    //$this->top_analysts = $this->group->getTopAnalystsQuery(array('21','636','184','191','648'))->limit(5)->execute();
    
    //get lists
    $q = LsDoctrineQuery::create()
      ->from('LsList l')
      ->leftJoin('l.sfGuardGroupList gl')
      ->where('gl.group_id = ?', $this->group->id)
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);
    
    $this->lists = $q->execute();
    
    //get notes
    $s = new LsSphinxClient(1, 10);
    $s->setFilter('sfguardgroup_ids', array($this->group['id']));

    if (!$currentUserId = sfGuardUserTable::getCurrentUserId())
    {
      $currentUserId = 0;
    }

    $s->setFilter('visible_to_user_ids', array_unique(array(0, $currentUserId)));

    if ($userId = $request->getParameter('user_id'))
    {
      $s->setFilter('user_id', array($userId));
    }
    
    $this->notes = NoteTable::getSphinxRecords($s, null, Doctrine::HYDRATE_ARRAY);    
    
    //get updated entities
    $this->user_ids = $this->group->getUserIds();
    $this->entity_ids = $this->group->getEntityIds();
  }
  
  
  public function executeAddList($request)
  {
    $this->checkGroup($request);
    $this->checkOwner();

    if ($request->hasParameter('q'))
    {
      $terms = explode(' ', $request->getParameter('q'));
      $page = $request->getParameter('page', 1);
      $num = $request->getParameter('num', 10);

      //get existing research lists
      $db = Doctrine_Manager::connection();
      $sql = 'SELECT list_id FROM sf_guard_group_list gl WHERE gl.group_id = ?';
      $stmt = $db->execute($sql, array($this->group->id));
      $listIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
  
      $q = LsDoctrineQuery::create()
        ->from('LsList l');

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

      //check that the group doesn't already have this list
      $q = LsDoctrineQuery::create()
        ->from('sfGuardGroupList gl')
        ->where('gl.list_id = ? AND gl.group_id = ?', array($list->id, $this->group->id));
      
      if (!$q->count())
      {
        $groupList = new sfGuardGroupList;
        $groupList->list_id = $list->id;
        $groupList->group_id = $this->group->id;
        $groupList->save();        
      }

      $this->clearCache($this->group);

      $this->redirect($this->group->getInternalUrl());
    }    
  }
    
  
  public function executeRemoveList($request)
  {
    $this->checkGroup($request);
    $this->checkOwner();

    if (!$request->isMethod('post'))
    {
      $this->forward('error', 'invalid');
    }
    
    $list = Doctrine::getTable('LsList')->find($request->getParameter('list_id'));
    $this->forward404Unless($list);

    //check that the group has this list
    $q = LsDoctrineQuery::create()
      ->from('sfGuardGroupList gl')
      ->where('gl.list_id = ? AND gl.group_id = ?', array($list->id, $this->group->id));
    
    if (!$groupList = $q->fetchOne())
    {
      $this->forward('error', 'invalid');
    }

    $groupList->delete();

    $this->clearCache($this->group);

    $this->redirect($this->group->getInternalUrl());
  }  
  
  
  public function executeUpdates($request)
  {
    $this->checkGroup($request);
    $this->checkUser($request);
    $entityIds = $this->group->getEntityIds();
    //$entityIds = '(' . implode(",",$entityIds) . ')';
    
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);

    $q = Doctrine_Query::create()
      ->from('Entity e')
      ->orderBy('e.updated_at DESC');
      
    if (count($userIds = $this->group->getUserIds()))
    {
      $q->whereIn('e.last_user_id', $userIds);
      $q->whereIn('e.id', $entityIds);
    }
    else
    {
      $q->where('1=0');
    }
    

    $this->entity_pager = new LsDoctrinePager($q, $page, $num);    
  }
  
  
  public function executeEdit($request)
  {
    $this->checkGroup($request);
    $this->checkUser($request);
    $this->checkOwner();

    $this->group_form = new sfGuardGroupForm($this->group);
    
    if ($request->isMethod('post'))
    {
      $params = $request->getParameter('sf_guard_group');
      $this->group_form->bind($params);
      
      if ($this->group_form->isValid())
      {
        $this->group->display_name = $params['display_name'];
        $this->group->blurb = $params['blurb'];
        $this->group->description = $params['description'];
        $this->group->contest = $params['contest'];
        $this->group->is_private = @$params['is_private'] ? true : false;
        $this->group->save();

        $this->clearCache($this->group);
        
        $this->redirect($this->group->getInternalUrl());
      }
    }
  }
  
  
  public function executeJoin($request)
  {
    $this->checkGroup($request);
    $this->checkUser($request);
    
    if (!$request->isMethod('post'))
    {
      $this->forward('error', 'invalid');
    }
    
    if (!$this->getUser()->hasGroup($this->group->name))
    {
      $ug = new sfGuardUserGroup;
      $ug->group_id = $this->group->id;
      $ug->user_id = $this->getUser()->getGuardUser()->id;
      $ug->save();
    }

    $this->clearCache($this->group);
    
    $this->redirect($this->group->getInternalUrl());
  }
  
  
  public function executeLeave($request)
  {
    $this->checkGroup($request);
    $this->checkUser($request);
    
    if (!$request->isMethod('post'))
    {
      $this->forward('error', 'invalid');
    }
    
    if ($this->getUser()->hasGroup($this->group->name))
    {
      $ug = LsDoctrineQuery::create()
        ->from('sfGuardUserGroup ug')
        ->where('ug.user_id = ? AND ug.group_id = ?', array($this->getUser()->getGuardUser()->id, $this->group->id))
        ->delete()
        ->execute();
    }
    
    $this->redirect('@homepage');  
  }
  
  
  public function executeMembers($request)
  {
    $this->checkGroup($request);
    $this->checkUser($request);
    $this->checkOwner();

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);

    $q = LsDoctrineQuery::create()
      ->from('sfGuardUser u')
      ->leftJoin('u.Profile p')
      ->leftJoin('u.sfGuardUserGroup ug')
      ->where('ug.group_id = ?', $this->group->id)
      ->orderBy('p.public_name')
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);
      
    $this->user_pager = new LsDoctrinePager($q, $page, $num);
  }
  

  public function executeAnalysts($request)
  {
    $this->checkGroup($request);
    $this->checkUser($request);

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 50);

    $q = LsDoctrineQuery::create()
      ->from('sfGuardUser u')
      ->leftJoin('u.Profile p')
      ->leftJoin('u.sfGuardUserGroup ug')
      ->where('ug.group_id = ?', $this->group->id)
      ->orderBy('p.public_name')
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);
      
    $this->user_pager = new LsDoctrinePager($q, $page, $num);
  }

  
  public function executeRefreshScores($request)
  {
    $this->checkGroup($request);
    $this->group->refreshScores();
    $this->redirect($this->group->getInternalUrl());
  }
  
  
  public function executeAddUser($request)
  {
    $this->checkGroup($request);
    $this->checkUser($request);  
    $this->checkOwner();
    
    if ($request->isMethod('post'))
    {    
      if (!$userId = $request->getParameter('user_id'))
      {
        $this->forward('error', 'invalid');    
      }

      //add user if not already group member
      $q = LsDoctrineQuery::create()
        ->from('sfGuardUserGroup ug')
        ->where('ug.group_id = ? AND ug.user_id = ?', array($this->group->id, $userId));
      
      if (!$q->count())
      {
        $ug = new sfGuardUserGroup;
        $ug->group_id = $this->group->id;
        $ug->user_id = $userId;
        $ug->save();
      }

      $this->clearCache($this->group);
      
      $this->redirect($this->group->getInternalUrl('members'));
    }

    if ($terms = $request->getParameter('q'))
    {
      $q = sfGuardUserTable::getPublicNameSearchQuery($terms);
      $this->result_pager = new LsDoctrinePager($q, $page=1, $num=20);
    }
  }
  
  
  public function executeRemoveUser($request)
  {
    $this->checkGroup($request);
    $this->checkUser($request);  
    $this->checkOwner();
    
    if (!$request->isMethod('post') || (!$userId = $request->getParameter('user_id')))
    {
      $this->forward('error', 'invalid');
    }
    
    //make sure user is a group member
    $q = LsDoctrineQuery::create()
      ->from('sfGuardUserGroup ug')
      ->where('ug.group_id = ? AND ug.user_id = ?', array($this->group->id, $userId));
    
    if (!$ug = $q->fetchOne())
    {
      $this->forward('error', 'invalid');    
    }
    
    $ug->delete();

    $this->clearCache($this->group);
    
    $this->redirect($this->group->getInternalUrl('members'));  
  }
  
  
  public function executeChangeOwner($request)
  {
    $this->checkGroup($request);
    $this->checkUser($request);
    
    if (!$request->isMethod('post') || (!$userId = $request->getParameter('user_id')))
    {
      $this->forward('error', 'invalid');
    }
    
    //make sure user is a group member
    $q = LsDoctrineQuery::create()
      ->from('sfGuardUserGroup ug')
      ->where('ug.group_id = ? AND ug.user_id = ?', array($this->group->id, $userId));
    
    if (!$ug = $q->fetchOne())
    {
      $this->forward('error', 'invalid');    
    }
    
    $ug->is_owner = $request->getParameter('is_owner') ? true : false;
    $ug->save();

    $this->redirect($this->group->getInternalUrl('members'));      
  }

  
  public function executeContest($request)
  {
    $this->checkGroup($request);
    $this->checkUser($request);
    $this->top_analysts = $this->group->getTopAnalystsQuery(array('21','636','184','191','648'))->limit(10)->execute();
  }
  
  
  public function executeList($request)
  {
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT g.*, COUNT(ug.user_id) users FROM sf_guard_group g ' . 
           'LEFT JOIN sf_guard_user_group ug ON (ug.group_id = g.id) ' . 
           'WHERE g.is_working = 1 AND g.is_private = 0 GROUP BY g.id ORDER BY users DESC';
    $stmt = $db->execute($sql);
    
    $this->groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  

  public function executeNotes($request)
  {
    $this->checkGroup($request);
    $this->checkUser($request);

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);
    
    $s = new LsSphinxClient($page, $num);
    $s->setFilter('sfguardgroup_ids', array($this->group['id']));
    $s->setFilter('visible_to_user_ids', array_unique(array(0, sfGuardUserTable::getCurrentUserId())));
    
    if ($userId = $request->getParameter('user_id'))
    {
      $s->setFilter('user_id', array($userId));
    }
    
    $this->note_pager = NoteTable::getSphinxPager($s);
  }
}