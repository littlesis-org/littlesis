<?php

class userActions extends sfActions
{
  public function checkUser($request)
  {
    $q = LsDoctrineQuery::create()
      ->from('sfGuardUser u')
      ->leftJoin('u.Profile p')
      ->where('p.public_name = ?', $request->getParameter('name'));

    $this->user = $q->fetchOne();

    $this->forward404Unless($this->user);
  }
  
  
  public function executeModifications($request)
  {
    $this->checkUser($request);
    
    $this->page = $request->getParameter('page', 1);
    $this->num = $request->getParameter('num', 20);
  }


  public function executeNotes($request)
  {
    $this->checkUser($request);

    if ($this->user->isCurrentUser())
    {
      $this->redirect('home/notes');
    }

    $this->note_form = new NoteForm;

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);
    $withReplies = $request->getParameter('replies', 1);

    $s = new LsSphinxClient($page, $num);

    if ($withReplies)
    {
      $s->setFilter('visible_to_user_ids', array($this->user->id));
    }
    else
    {
      $s->setFilter('user_id', array($this->user->id));
    }

    $s->setFilter('visible_to_user_ids', array_unique(array(0, sfGuardUserTable::getCurrentUserId())));
    
    $this->note_pager = NoteTable::getSphinxPager($s, null, Doctrine::HYDRATE_ARRAY);
  }
  
  
  public function executeNote($request)
  {
    $this->checkUser($request);
    
    $this->note = Doctrine::getTable('Note')->find($request->getParameter('id'));
    $this->forward404Unless($this->note);

    $currentUser = $this->getUser()->isAuthenticated() ? $this->getUser()->getGuardUser() : new sfGuardUser;

    if (!$this->note->isViewableBy($currentUser))
    {
      $this->forward('error', 'credentials');
    }
  }


  public function executeGroups($request)
  {
    $this->checkUser($request);

    $db = Doctrine_Manager::connection();

    //first get list of groups this user belongs to
    $sql = 'SELECT group_id FROM sf_guard_user_group WHERE user_id = ?';
    $stmt = $db->execute($sql, array($this->user->id));
    $groupIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($groupIds))
    {
      $sql = 'SELECT g.*, COUNT(ug.user_id) users FROM sf_guard_group g ' . 
             'LEFT JOIN sf_guard_user_group ug ON (ug.group_id = g.id) ' . 
             'WHERE g.id IN (' . implode(', ', $groupIds) . ') AND g.is_working = 1 AND g.is_private = 0 GROUP BY g.id ORDER BY users DESC';
      $stmt = $db->execute($sql);
      
      $this->groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else
    {
      $this->groups = array();
    }
  }
  
  
  public function executeFeatured($request)
  {
    $page = $request->getParameter('page');
    $num = $request->getParameter('num', 10);

    $q = LsDoctrineQuery::create()
      ->from('sfGuardUser u')
      ->leftJoin('u.Profile p')
      ->leftJoin('u.groups g')
      ->where('u.is_super_admin = 0')
      ->orderBy('p.score DESC')
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);
      
    $this->user_pager = new LsDoctrinePager($q, $page, $num);
  }  
}