<?php

class mapActions extends sfActions
{
  public function executeView($request)
  {
    $map = Doctrine::getTable("NetworkMap")->find($request->getParameter("id"));
    $this->forward404Unless($map);

    $this->map = $map->toArray();
    $this->map["data"] = json_encode($this->map["data"]);    
  }
}