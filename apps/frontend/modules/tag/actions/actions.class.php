<?php

class tagActions extends sfActions
{
  public function checkTag($request)
  {
    $name = $request->getParameter('name');
    $parts = explode(':', $name);

    if (count($parts) == 3)
    {
      $this->tag = TagTable::getByTripleQuery($parts[0], $parts[1], $parts[2])->fetchOne();
    }
    else
    {
      $this->tag = Doctrine::getTable('Tag')->findOneByName($name);
    }
    
    $this->forward404Unless($this->tag);  
    
    
    if (!$this->tag->is_visible && !$sf_user->hasCredential('admin'))
    {
      $this->redirect('error/credentials');
    }
  }


  public function checkObject($request)
  {
    $model = $request->getParameter('object_model');
    $id = $request->getParameter('object_id');
    
    $this->object = Doctrine::getTable($model)->find($id);
    $this->forward404Unless($this->object);  
  }
  

  public function executeView($request)
  {
    $this->checkTag($request);
    
    $this->models = $this->tag->getObjectModels();
        
    foreach ($this->models as $model)
    {
      $pagerStr = strtolower($model) . '_pager';
      $pagerQuery = $this->tag->getObjectsByModelQuery($model);

      $this->$pagerStr = new LsDoctrinePager($pagerQuery, $page=1, $num=10);
    }
  }


  public function executeRemove($request)
  {
    if (!$request->isMethod('post'))
    {
      $this->redirect('error/invalid');
    }
    
    $this->checkTag($request);  
    
    $this->tag->delete();
    
    $this->redirect('@homepage');
  }


  public function executeAddTag($request)
  {
    $this->checkObject($request);

    $name = $request->getParameter('name');    
    $redirect = $request->getParameter('redirect');
    
    $this->object->addTagByName($name);
    
    $this->redirect($redirect ? $redirect : strtolower(get_class($this->object)) . '/view?id=' . $this->object->id);
  }
  
  
  public function executeUntag($request)
  {
    if (!$request->isMethod('post'))
    {
      $this->redirect('error/invalid');
    }


    $this->checkTag($request);  
    $this->checkObject($request);

    $name = $request->getParameter('name');
    $redirect = $request->getParameter('redirect');

    $this->object->removeTagByName($name);

    $this->redirect($redirect ? $redirect : strtolower(get_class($this->object)) . '/view?id=' . $this->object->id);    
  }
  
  
  public function executeList($request)
  {
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);

    $q = TagTable::getPopularTagsQuery();
  
    $this->tag_pager = new Doctrine_Pager($q, $page, $num);
  }
}