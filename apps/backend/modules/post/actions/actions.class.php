<?php

class postActions extends sfActions
{
  public function executeList($request)
  {
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);
    $userId = $request->getParameter('user_id');

    $q = LsDoctrineQuery::create()
      ->from('UserFormPost p')
      ->leftJoin('p.User u')
      ->orderBy('p.created_at DESC');
    
    if ($userId)
    {
      $q->andWhere('p.user_id = ?', $userId);
    }

    if ($start = $request->getParameter('start'))
    {
      $start = date('Y-m-d H:i:s', strtotime($start));
      $q->addWhere('p.created_at > ?', $start);
    }

    if ($end = $request->getParameter('end'))
    {
      $end = date('Y-m-d H:i:s', strtotime($end));
      $q->addWhere('p.created_at < ?', $end);
    }
        
    $this->post_pager = new LsDoctrinePager($q, $page, $num);
  }
}