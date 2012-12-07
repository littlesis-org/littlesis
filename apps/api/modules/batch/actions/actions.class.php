<?php

class batchActions extends LsApiActions
{
  public function preExecute()
  {
    //setup response format
    $this->setResponseFormat();
  }
  
  
  public function executeEntities($request)
  {
    if (!$ids = $request->getParameter('ids'))
    {
      $this->returnStatusCode(400);
    }
    
    $ids = explode(',', $ids);
    $options = $this->getParams(array('details'));
    
    $this->entities = BatchApi::getEntities($ids, $options);
    
    return 'Xml';
  }
  
  
  public function executeEntitiesWithOrgs($request)
  {
    if (!$ids = $request->getParameter('ids'))
    {
      $this->returnStatusCode(400);
    }
    
    $ids = explode(',', $ids);
    $options = $this->getParams(array('is_current'));
    
    $this->entities = BatchApi::getEntitiesWithOrgs($ids, $options);
    
    return 'Xml';    
  }
  
  
  public function executeRelationships($request)
  {
    if (!$ids = $request->getParameter('ids'))
    {
      $this->returnStatusCode(400);
    }
    
    $ids = explode(',', $ids);
    $options = $this->getParams(array('details', 'cat_id'));
    
    $this->relationships = BatchApi::getRelationships($ids, $options);
    
    return 'Xml';  
  }
}