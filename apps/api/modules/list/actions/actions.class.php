<?php

/**
 * list actions.
 *
 * @package    ls
 * @subpackage list
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class listActions extends LsApiActions
{
  public function preExecute()
  {
    $this->list = LsListApi::get($this->getRequest()->getParameter('id'));

    $this->setLastModified($this->list);

    $this->checkExistence($this->list, 'LsList');
  }
  

  public function getParams(Array $acceptedKeys)
  {
    $ret = array();
    $params = $this->getRequest()->getParameterHolder()->getAll();
    
    foreach ($acceptedKeys as $key)
    {
      if (@$params[$key]) { $ret[$key] = $params[$key]; }
    }

    $this->getResponse()->setSlot('params', $ret);
    
    return $ret;
  }

  
  public function executeView($request)
  {
    $this->setResponseFormat();    

    return 'Xml';
  }
  
  
  public function executeEntities($request)
  {
    $this->setResponseFormat(array('xml', 'json', 'csv'));    

    $options = $this->getParams(array('num', 'page', 'offset'));

    $results = LsListApi::getEntities($this->list['id'], $options);
    $this->entities = LsApi::sliceArrayFromOptions($results, $options, $defaultNum=100);
    $this->getResponse()->setSlot('total', count($results));

    return ($this->format == 'csv') ? 'Csv' : 'Xml';
    //return ($this->format == 'xml') ? 'Xml' : 'Csv';
  }
  

  public function executeEntityIds($request)
  {
    $this->setResponseFormat(array('json'));    

    $options = $this->getParams(array('expand_couples'));
    $results = array_map(
      function($id) { return intval($id); },
      LsListApi::getEntityIds($this->list['id'], $options)
    );

    return $this->renderText(json_encode($results));
  }

  
  public function executeEntitiesWithOrgs($request)
  {
    $this->setResponseFormat(array('xml', 'json'));    

    $options = $this->getParams(array('is_current', 'num', 'page'));

    $results = LsListApi::getEntitiesWithOrgs($this->list['id'], $options);
    $this->entities = LsApi::sliceArrayFromOptions($results, $options, $defaultNum=100);
    $this->getResponse()->setSlot('total', count($results));

    return 'Xml';  
  }

  public function executeMap($request)
  {
    $this->setResponseFormat(array('xml', 'json'));    
    $options = $this->getParams(array('num'));
  
    $this->data = LsListTable::getEntitiesAndRelsForMap($this->list['id']);

    if ($request->getParameter('format') == "json")
    {
      return $this->renderText(json_encode($this->data));
    }
    else
    {
      return 'Xml';
    }
  }

  public function executeNetworkLinks($request)
  {
    $this->setResponseFormat(array('json'));    
    $options = $this->getParams(array());

    $this->data = array_map(
      function($ary) { return array_map(function($e) { return intval($e); }, $ary); },
      LsListApi::getNetworkLinks($this->list['id'], $options)
    );

    return $this->renderText(json_encode($this->data));
  }

  public function executeImages($request)
  {
    $this->setResponseFormat(array('json'));    
    $options = $this->getParams(array('with_address'));
    $this->data = LsListApi::getImages($this->list['id'], $options);
    return $this->renderText(json_encode($this->data));
  }

  public function executeSearchData($request)
  {
    $this->setResponseFormat(array('json'));    
    $this->data = LsListApi::getSearchData($this->list['id']);
    return $this->renderText(json_encode($this->data));
  }  

  public function executeArticles($request)
  {
    $this->setResponseFormat(array('json'));    
    $this->data = LsListApi::getArticles($this->list['id']);
    return $this->renderText(json_encode($this->data));
  }
}
