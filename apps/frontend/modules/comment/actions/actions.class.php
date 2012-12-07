<?php

class commentActions extends sfActions
{
  public function checkObject($request)
  {
    $this->object = Objectable::getObjectByModelAndId(
      $request->getParameter('model'), 
      $request->getParameter('id'), 
      $includeDeleted=true
    );
    $this->forward404Unless($this->object);
  }
  
  
  public function executeList($request)
  {
    $this->checkObject($request);
    
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);

    $q = Objectable::getByModelAndObjectQuery('Comment', $this->object)
      ->leftJoin('c.User u')
      ->addWhere('c.parent_id IS NULL')
      ->orderBy('c.created_at ASC');
    
    $this->comment_pager = new LsDoctrinePager($q, $page, $num);
  }
  
  
  public function executeAdd($request)
  {
    $this->checkObject($request);

    if ($parentId = $request->getParameter('parent_id'))
    {
      $this->parent_comment = Objectable::getObjectByModelAndId('Comment', $parentId);
    }

    $this->comment_form = new CommentForm;  
    
    if ($request->isMethod('post'))
    {
      $params = $request->getParameter('comment');
      $this->comment_form->bind($params);
      
      if ($this->comment_form->isValid())
      {
        $comment = new Comment;
        $comment->setObject($this->object);
        $comment->user_id = $this->getUser()->getGuardUser()->id;
        $comment->title = $params['title'];
        $comment->body = $params['body'];
        $comment->parent_id = $parentId;
        $comment->save();
        
        $this->redirect($request->getParameter('module') . '/comments?id=' . $this->object->id);
      }
    }
  }
  
  
  public function executeLatest($request)
  {
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);

    $q = LsDoctrineQuery::create()
      ->from('Comment c')
      ->leftJoin('c.User u')
      ->orderBy('c.created_at DESC');
      
    $this->comment_pager = new LsDoctrinePager($q, $page, $num);
  }
}