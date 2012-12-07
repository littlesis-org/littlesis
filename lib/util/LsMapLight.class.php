<?php

class LsMapLight
{
  protected $api_key;
  protected $api_url = 'http://maplight.org/services_open_api/';
  protected $default_headers = array(
      'User-Agent' => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20061205 Iceweasel/2.0.0.1 (Debian-2.0.0.1+dfsg-2)');
  protected $format = 'json';
  protected $query_url;
  protected $response;
  protected $parsed_response;
  
  
  public function __construct()
  {
    $this->api_key = sfConfig::get('app_maplight_api_key');
    $this->browser = new sfWebBrowser($this->default_headers);
  }
  
  public function getQueryUrl($params, $method = 'map.organization_search_v1')
  {
    $arr = array('apikey=' . $this->api_key);
    foreach($params as $pk => $pv)
    { 
      if ($pk == 'search' && $method == 'map.organization_search_v1')
      {
        $arr[] = 'search=' . urlencode(OrgTable::nameSearch($pv));  
      }
      else
      {
        $arr[] = $pk . "=" . urlencode($pv);
      }
    } 
    $query_url = $this->api_url . $method . "." . $this->format . "?" . implode('&',$arr);   
    $this->query_url = $query_url;
    return $query_url;
  }
  
  public function getResponse($params, $method = 'map.organization_search_v1')
  {
    if (!$this->query_url)
    {
      $this->getQueryUrl($params, $method);
    }
    if (!$this->browser->get($this->query_url)->responseIsError())
    {
      $this->response = $this->browser->getResponseText();
      return $this->response;
    }
  }
  
  public function getParsedResponse($params, $method = 'map.organization_search_v1')
  {
    if (!$this->response)
    {
      $this->getResponse($params, $method);
    }
    //var_dump($this->response);
    $response = json_decode($this->response);
    //var_dump($response);
    $response = (array) $response;
    //$response = array_pop($response);
    $this->parsed_response = $response; 
    return $response;
  }
  
  public function findKeys($entity, $params = array('exact' => 0))
  {
    if ($entity->primary_ext != 'Org')
    {
      return false;
    }
    else
    {
      if ($entity)
      {
        $params['search'] = $entity->name;
      }
      $response = $this->getParsedResponse($params);
      $results = array();
      if (isset($response['organizations']))
      {
        foreach($response['organizations'] as $org)
        {
          $results[] = array('name' => $org->name, 'id' => $org->organization_id);
        }
      }
      return $results;
    }
  }


}
