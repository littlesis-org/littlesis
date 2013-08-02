<?php

class mapActions extends sfActions
{
  protected function checkMap($request)
  {
    $this->map = Doctrine::getTable("NetworkMap")->find($request->getParameter("id"));
    $this->forward404Unless($this->map);  
  }

  public function executeView($request)
  {
    $this->checkMap($request);

    $this->map = $this->map->toArray();
    $this->map["data"] = json_encode($this->map["data"]);    
  }
  
  public function executeEdit($request)
  {
    $this->checkMap($request);

    if ($request->isMethod('post'))
    {
      $this->map->title = trim($request->getParameter("title"));
      $this->map->description = trim($request->getParameter("description"));
      $this->map->save();
      
      $this->redirect("map/view?id=" . $this->map->id);
    }    
  }
  
  public function executeList($request)
  {
    $this->maps = Doctrine::getTable('NetworkMap')
      ->createQuery('m')
       ->orderBy('updated_at DESC')
       ->execute();
  }
}