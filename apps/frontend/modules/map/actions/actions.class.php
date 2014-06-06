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
    $db = Doctrine_Manager::connection();
    $sql = "SELECT * FROM network_map WHERE id = ? AND is_deleted = 0";
    $stmt = $db->execute($sql, array($request->getParameter("id")));
    $this->map = $stmt->fetch(PDO::FETCH_ASSOC);
    $data = json_decode($this->map["data"], true);
    $this->map["data"] = json_encode(array(
      "entities" => array_map(array('NetworkMapTable', 'prepareEntityData'), $data["entities"]),
      "rels" => array_map(array('NetworkMapTable', 'prepareRelData'), $data["rels"]),
    ));
    $this->forward404Unless($this->map);
  }
  
  public function executeEdit($request)
  {
    $this->checkMap($request);

    if ($request->isMethod('post'))
    {
      $this->map->title = trim($request->getParameter("title"));
      $this->map->description = trim($request->getParameter("description"));
      $this->map->save();

      LsCache::clearNetworkMapCacheById($this->map->id);
      
      $this->redirect("@editMap?id=" . $this->map->id);
    }    
  }

  public function executeCreate($request)
  {
    if ($request->isMethod('post'))
    {
      $data = $request->getParameter("data");
      $decoded = json_decode($data);

      $map = new NetworkMap();
      $map->width = $request->getParameter("width", sfConfig::get('app_netmap_default_width'));
      $map->height = $request->getParameter("height", sfConfig::get('app_netmap_default_height'));
      $map->user_id = $request->getParameter("user_id");
      $map->data = $data;
      $map->entity_ids = implode(",", array_values(array_map(function($e) { return $e->id; }, $decoded->entities)));
      $map->rel_ids = implode(",", array_values(array_map(function($e) { return $e->id; }, $decoded->rels)));
      $map->save();

      $response = $map->toArray();
      $response["data"] = json_decode($response["data"]);
      
      return $this->renderText(json_encode($response));
    }  
    
    $this->returnStatusCode(400);
  }

  public function executeUpdate($request)
  {
    if ($request->isMethod('post'))
    {
      $map = Doctrine::getTable("NetworkMap")->find($request->getParameter("id"));
      $this->forward404Unless($map);

      $data = $request->getParameter("data");
      $decoded = json_decode($data);

      $map->width = $request->getParameter("width");
      $map->height = $request->getParameter("height");
      $map->data = $data;
      $map->entity_ids = implode(",", array_values(array_map(function($e) { return $e->id; }, $decoded->entities)));
      $map->rel_ids = implode(",", array_values(array_map(function($e) { return $e->id; }, $decoded->rels)));
      $map->save();

      LsCache::clearNetworkMapCacheById($map->id);

      $response = $map->toArray();
      $response["data"] = json_decode($response["data"]);

      return $this->renderText(json_encode($response));
    }  
    
    $this->returnStatusCode(400);
  }
  
  public function executeList($request)
  {
    $this->maps = Doctrine::getTable('NetworkMap')
      ->createQuery('m')
       ->orderBy('updated_at DESC')
       ->execute();
  }
  
  public function executeDelete($request)
  {
    if ($request->isMethod('post'))
    {
      $this->checkMap($request);    
      $this->map->delete();    

      LsCache::clearNetworkMapCacheById($this->map->id);

      $this->redirect("map/list");
    }
    else
    {
      $this->forward("error", "invalid");
    }
  }
}