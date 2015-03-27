<?php

class entityActions extends LsApiActions
{
  public function preExecute()
  {
    //setup response format
    $this->setResponseFormat();

    $this->entity = EntityApi::get($this->getRequest()->getParameter('id'));

    $this->setLastModified($this->entity);
    
    $this->checkExistence($this->entity, 'Entity');
  }


  public function getParams(Array $acceptedKeys)
  {
    $ret = array();
    $params = $this->getRequest()->getParameterHolder()->getAll();
    
    foreach ($acceptedKeys as $key)
    {
      if (isset($params[$key])) { $ret[$key] = $params[$key]; }
    }
    
    $this->getResponse()->setSlot('params', $ret);
    
    return $ret;
  }


  public function executeView($request)
  {
    return 'Xml';
  }
  
  
  public function executeDetails($request)
  {
    $this->entity = array_merge($this->entity, EntityApi::getDetails($this->entity['id']));
    $this->entity = array_merge($this->entity, EntityApi::getFields($this->entity['id']));
    $this->aliases = EntityApi::getAliases($this->entity['id'], false);    
    return 'Xml';
  }
  
  
  public function executeAliases($request)
  {
    $this->aliases = EntityApi::getAliases($this->entity['id'], false);    
    return 'Xml';
  }


  public function executeRelationships($request)
  {
    $options = $this->getParams(array('cat_ids', 'order', 'num', 'page'));

    //get full results, then limit
    $results = EntityApi::getRelationships($this->entity['id'], $options);  
    $this->rels = LsApi::sliceArrayFromOptions($results, $options, $defaultNum=null, $maxNum=null);
    $this->getResponse()->setSlot('total', count($results));

    return 'Xml';
  }
  
  
  public function executeRelated($request)
  {
    $options = $this->getParams(array('sort', 'cat_ids', 'order', 'is_current', 'num', 'page'));
    
    //get related entities
    $totalResults = EntityApi::getRelated($this->entity['id'], $options);

    //limit results
    $results = LsApi::sliceArrayFromOptions($totalResults, $options, $defaultNum=null, $maxNum=null);
    $this->getResponse()->setSlot('total', count($totalResults));

    //use different template for each sort method
    if (@$options['sort'] == 'relationship')
    {
      $this->relationships = $results;
      return 'RelationshipsXml';
    }
    elseif (@$options['sort'] == 'category')
    {
      $this->categories = $results;
      return 'CategoriesXml';
    }
    else
    {
      $this->entities = $results;
      return 'Xml';
    }
  }


  public function executeLeadership($request)
  {
    $options = $this->getParams(array('is_current'));

    //respond with 400 Bad Request if entity isn't an org
    if ($this->entity['primary_type'] != 'Org')
    {
      $this->returnStatusCode(400, "Can't retrieve leadership; requested entity is a person.");
    }

    $this->entities = EntityApi::getLeadership($this->entity['id'], $options);
    return 'Xml';
  }
  
  
  public function executeOrgs($request)
  {
    $options = $this->getParams(array('is_current', 'type'));

    //respond with 400 Bad Request if entity isn't a person
    if ($this->entity['primary_type'] != 'Person')
    {
      $this->returnStatusCode(400, "Can't retrieve orgs; requested entity is an org.");
    }

    $this->entities = EntityApi::getOrgs($this->entity['id'], $options);
    return 'Xml';
  }
  
  
  
  public function executeDegree2($request)
  {
    $options = $this->getParams(array('cat1_ids', 'order1', 'cat2_ids', 'order2', 'num', 'page', 'show_count'));  

    if ($request->getParameter('show_count'))
    {
      $count = EntityApi::getSecondDegreeNetwork($this->entity['id'], $options, $countOnly=true);
      $this->getResponse()->setSlot('total', $count);
    }

    $this->entities = EntityApi::getSecondDegreeNetwork($this->entity['id'], $options);

    return 'Xml';
  }
  
  
  public function executeLeadershipDegree2($request)
  {
    //respond with 400 Bad Request if entity isn't an org
    if ($this->entity['primary_type'] != 'Org')
    {
      $this->returnStatusCode(400, "Can't retrieve leadership and their orgs; requested entity is a person.");
    }

    $options = $this->getParams(array('num', 'page', 'is_current'));  

    $this->entities = EntityApi::getLeadershipWithOrgs($this->entity['id'], $options);

    return 'Xml';  
  }
  
  
  public function executeLists($request)
  {
    $this->lists = EntityApi::getLists($this->entity['id']);    
    return 'Xml';
  }
  
  
  public function executeReferences($request)
  {
    $this->references = EntityApi::getReferences($this->entity['id']);
    return 'Xml';
  }
  

  public function executeRelationshipReferences($request)
  {  
    $options = $this->getParams(array('cat_ids', 'order', 'num', 'page'));

    //get full results, then limit
    $results = EntityApi::getRelationshipReferences($this->entity['id'], $options);  
    $this->rels = LsApi::sliceArrayFromOptions($results, $options, $defaultNum=null, $maxNum=null);
    $this->getResponse()->setSlot('total', count($results));

    return 'Xml';
  }

  
  public function executeChildOrgs($request)
  {
    $this->forward404Unless($this->entity['primary_type'] == 'Org');
    $this->child_orgs = EntityApi::getChildOrgs($this->entity['id']);
    return 'Xml';
  }
  
  
  public function executeImages($request)
  {
    $options = $this->getParams(array('size'));  
    $this->images = EntityApi::getImages($this->entity['id'], $options);
    return 'Xml';
  }
  
  
  public function executeImage($request)
  {
    $options = $this->getParams(array('size'));  
    
    if ($uri = EntityApi::getImage($this->entity['id'], $options))
    {
      $this->redirect($uri);
    }
    else
    {
      $this->forward404();
    }
  }
  
  public function executeMap($request)
  {
    $options = $this->getParams(array('num'));
  
    $this->data = EntityTable::getRelatedEntitiesAndRelsForMap($this->entity['id']);

    if ($request->getParameter('format') == "json")
    {
      return $this->renderText(json_encode($this->data));
    }
    else
    {
      return 'Xml';
    }
  }

  public function executePolitical($request)
  {
    $options = $this->getParams(array('start_cycle', 'end_cycle'));
    $this->data = EntityApi::getDonationSummary($this->entity['id'], $options);

    return $this->renderText(json_encode($this->data));
  }

  public function executeArticles($request)
  {
    $this->data = EntityApi::getArticles($this->entity['id']);
    return $this->renderText(json_encode($this->data));
  }

  public function executeAddresses($request)
  {
    $this->data = EntityApi::getAddresses($this->entity['id']);
    return $this->renderText(json_encode($this->data));
  }
}
