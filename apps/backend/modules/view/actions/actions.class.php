<?php

/**
 * view actions.
 *
 * @package    ls
 * @subpackage view
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class viewActions extends sfActions
{
  public function executeList($request)
  {
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);

    $q = LsDoctrineQuery::create()
      ->from('UserView v')
      ->leftJoin('v.User u')
      ->orderBy('v.created_at DESC');
      
    $this->constraints = array();
      
    if ($userId = $request->getParameter('user_id'))
    {
      $user = Doctrine::getTable('sfGuardUser')->find($userId);
      $this->constraints[] = 'Limiting to ' . $user->Profile->getFullName();

      $q->addWhere('v.user_id = ?', $userId);
    }
    
    if (($model = $request->getParameter('object_model')) && ($id = $request->getParameter('object_id')))
    {
      if ($object = Objectable::getObjectByModelAndId($model, $id, $includeDeleted=true))
      {
        $this->constraints[] = 'Limiting to ' . $object->getName();
      }
      else
      {
        $this->constraints[] = 'Limiting to ' . $model . ' ID ' . $id;
      }
      
      $q->addWhere('v.object_model = ? AND v.object_id = ?', array($model, $id));
    }

    if ($start = $request->getParameter('start'))
    {
      $start = date('Y-m-d H:i:s', strtotime($start));
      $q->addWhere('v.created_at > ?', $start);
    }

    if ($end = $request->getParameter('end'))
    {
      $end = date('Y-m-d H:i:s', strtotime($end));
      $q->addWhere('v.created_at < ?', $end);
    }
    
    $this->view_pager = new LsDoctrinePager($q, $page, $num);
  }
}
