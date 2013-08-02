<?php

class mapActions extends LsApiActions
{
  public function executeEntities($request)
  {
    $this->setResponseFormat();    

    $entity_ids = explode(",", $request->getParameter('entity_ids'));
    $this->data = EntityTable::getEntitiesAndRelsForMap($entity_ids);

    if ($request->getParameter('format') == "json")
    {
      return $this->renderText(json_encode($this->data));
    }
    else
    {
      return 'Xml';
    }
  }

  public function executeView($request)
  {
    $this->setResponseFormat();    

    $map = Doctrine::getTable("NetworkMap")->find($request->getParameter("id"));
    $this->forward404Unless($map);

    $response = $map->toArray();
    $response["data"] = json_decode($response["data"]);

    return $this->renderText(json_encode($response));
  }
  
  public function executeCreate($request)
  {
    if ($request->isMethod('post'))
    {
      $this->setResponseFormat();    

      $data = $request->getParameter("data");
      $decoded = json_decode($data);

      $map = new NetworkMap();
      $map->user_id = $request->getParameter("user_id");
      $map->data = $data;
      $map->entity_ids = implode(",", array_values(array_map(function($e) { return $e->id; }, $decoded->entities)));
      $map->rel_ids = implode(",", array_values(array_map(function($e) { return $e->id; }, $decoded->rels)));
      $map->save();

      $response = $map->toArray();
      $response["data"] = json_decode($response["data"]);
      
      return $this->renderText(json_encode($response));
    }  
    
    //404 Not Found
    $this->returnStatusCode(400);
  }  

  public function executeUpdate($request)
  {
    if ($request->isMethod('post'))
    {
      $this->setResponseFormat();    

      $map = Doctrine::getTable("NetworkMap")->find($request->getParameter("id"));
      $this->forward404Unless($map);

      $data = $request->getParameter("data");
      $decoded = json_decode($data);

      $map->data = $data;
      $map->entity_ids = implode(",", array_values(array_map(function($e) { return $e->id; }, $decoded->entities)));
      $map->rel_ids = implode(",", array_values(array_map(function($e) { return $e->id; }, $decoded->rels)));
      $map->save();

      $response = $map->toArray();
      $response["data"] = json_decode($response["data"]);

      return $this->renderText(json_encode($response));
    }  
    
    //404 Not Found
    $this->returnStatusCode(400);
  } 
}