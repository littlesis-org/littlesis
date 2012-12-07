<?php

class modificationActions extends sfActions
{
  public function checkObject($request)
  {
    $class = $request->getParameter('object_model');
    $lower = strtolower($class);
    $id = $request->getParameter('object_id');
    
    $q = LsDoctrineQuery::create()
      ->from($class . ' ' . $lower)
      ->where($lower . '.id = ?', $id);

    if (Doctrine::getTable($class)->hasTemplate('Doctrine_Template_SoftDelete'))
    {
      $q->addWhere($lower . '.is_deleted IS NOT NULL');
    }
    
    $this->object = $q->fetchOne();
    $this->forward404Unless($this->object);
  }


  public function executeList($request)
  { 
    $this->checkObject($request);
    
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);
    $userId = $request->getParameter('user_id');

    if ($request->getParameter('group_by_user'))
    {
      $q = $this->object->getModificationsQuery()
        ->addWhere('NOT EXISTS( SELECT id FROM modification WHERE modification.user_id = m.user_id AND modification.created_at > m.created_at )')
        ->setHydrationMode(Doctrine::HYDRATE_ARRAY);
      
      $this->modification_pager = new LsDoctrinePager($q, $page, $num);    
    }
    else
    {
      $this->modification_pager = new LsDoctrinePager(
        $this->object->getModificationsQuery($userId)->setHydrationMode(Doctrine::HYDRATE_ARRAY),
        $page,
        $num
      );
    }
  }
  
  
  public function executeLatest($request)
  {
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);

    $q = Doctrine_Query::create()
      ->from('Entity e')
      ->leftJoin('e.LastUser u')
      ->leftJoin('u.Profile p')
      ->orderBy('e.updated_at DESC')
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);

    $this->entity_pager = new LsDoctrinePager($q, $page, $num);
  }
}