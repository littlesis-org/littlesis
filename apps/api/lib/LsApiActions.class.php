<?php

class LsApiActions extends sfActions
{
  protected $contentTypeMap = array(
    'json'  => 'application/json',
    'xml'   => 'application/xml',
    'html'  => 'text/html',
    'csv'   => 'text/csv'
  );


  public function checkExistence($record, $model)
  {
    if (!$record)
    {
      $lower = strtolower($model);

      //if record is deleted, return 410 Gone status
      $q = LsDoctrineQuery::create()
        ->from($model . ' ' . $lower)
        ->where($lower . '.id = ? AND ' . $lower . '.is_deleted = ?', array($this->getRequest()->getParameter('id'), true))
        ->setHydrationMode(Doctrine::HYDRATE_ARRAY);

      if ($deleted = $q->fetchOne())
      {
        //if merged, redirect
        if (isset($deleted['merged_id']) && $deleted['merged_id'])
        {
          //301 Moved Permanently
          $uri = call_user_func(array($model . 'Api', 'getUri'), $deleted['merged_id'], $this->format);
          $this->getResponse()->setHttpHeader('Location', $uri);
          $html = 'This resource has been merged into ' . $uri;
          $this->returnStatusCode(301, $html);
        }
        else
        {
          //410 Gone
          $this->returnStatusCode(410);
        }
      }
      else
      {
        //404 Not Found
        $this->returnStatusCode(404);
      }
    }
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


  public function setResponseFormat(Array $validFormats = array('xml', 'json'))
  {
    $this->format = $this->getRequest()->getParameter('format', 'xml'); 
    
    if (in_array($this->format, $validFormats))
    {
      $this->getResponse()->setContentType($this->contentTypeMap[$this->format]);
      $this->setLayout($this->format);      
    }
    else    
    {
      //400 Bad Request
      $this->returnStatusCode(400);
    }    
  }


  public function returnStatusCode($num, $content=null)
  {
    $this->getResponse()->setContentType('text/html');
    $this->getResponse()->setStatusCode($num);
    
    if ($content)
    {
      $this->getResponse()->setContent($content);
      $this->getResponse()->send();
      
      throw new sfStopException();
    }
    else
    {
      $this->getResponse()->setHeaderOnly(true); 
      $this->getResponse()->send();

      throw new sfStopException();
    }
  }
  
  
  public function executeEmpty($request)
  {
    return sfView::NONE;
  }
  
  
  public function setLastModified($record)
  {
    if (isset($record['updated_at']))
    {
      $serverZoneTxt = date_default_timezone_get();
      $lastModifiedServer = new DateTime($record['updated_at']);

      date_default_timezone_set('GMT');
      $lastModified = date('r', $lastModifiedServer->format('U'));
      date_default_timezone_set($serverZoneTxt);

      $this->getResponse()->setHttpHeader('Last-Modified', $lastModified);
      $this->getResponse()->setHttpHeader('Cache-Control', 'must-revalidate');
      unset($record['updated_at']);
    }
  }
}