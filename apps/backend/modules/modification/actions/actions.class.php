<?php

class modificationActions extends sfActions
{
  public function executeList($request)
  {
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);
    $num = 20;

    $q = LsDoctrineQuery::create()
      ->from('Modification m')
      //->leftJoin('m.Field f')
      //->leftJoin('m.User u')
      ->orderBy('m.created_at DESC');
      
    
    if ($is_delete = $request->getParameter('is_delete'))
    {
      $q->andWhere('m.is_delete = ?', 1);
    }
    if ($is_create = $request->getParameter('is_create'))
    {
      $q->andWhere('m.is_create = ?', 1);
    }

    if ($userId = $request->getParameter('user_id'))
    {
      $q->addWhere('m.user_id = ?', $userId);
    }
    else //if ($usersOnly = $request->getParameter('users_only'))
    {
      $q->andWhere('m.user_id > 2');
    }

    if ( $model = $request->getParameter('model'))
    {
      if (is_array($model))
      {     
        $model = array_keys($model); 
        $ors = array_fill(0, count($model),'m.object_model = ?');
        $ors = implode(' OR ', $ors);

        $q->andWhereIn('m.object_model',$model);
      }
      else if ($id = $request->getParameter('id')) 
      {
        $q->addWhere('m.object_id = ?', $id);
      }

    }
    
    if ($start = $request->getParameter('start'))
    {
      $start = date('Y-m-d H:i:s', strtotime($start));
      $q->addWhere('m.created_at > ?', $start);
    }

    if ($end = $request->getParameter('end'))
    {
      $end = date('Y-m-d H:i:s', strtotime($end));
      $q->addWhere('m.created_at < ?', $end);
    }
    
    if ($distinct_user = $request->getParameter('distinct'))
    {    
      $q->groupBy('m.user_id');
    }
    
    if ($user = $request->getParameter('user'))
    {
      $q->leftJoin('m.User u');
      $q->leftJoin('u.Profile p');
      $q->andWhere('u.username like ? or p.public_name like ? or p.name_first like ? or p.name_last like ?', array_fill(0,4,'%' . $user . '%'));
    }
    
    $this->modification_pager = new LsDoctrinePager($q, $page, $num);
  }
}