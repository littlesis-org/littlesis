<?php

class LsNyTimes
{
  protected $api;
  protected $api_key;
  protected $apis = array('articles','tags');
  protected $api_urls = array(
    'articles' => 'http://api.nytimes.com/svc/search/v1/article',
    'tags' => 'http://api.nytimes.com/svc/timestags/suggest');
  protected $api_url;
  protected $default_headers = array(
      'User-Agent' => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20061205 Iceweasel/2.0.0.1 (Debian-2.0.0.1+dfsg-2)');
  protected $query_url;
  protected $response;
  protected $parsed_response;
  protected $facets = array('nytd_per_facet','nytd_org_facet');
  
  
  public function __construct($api = 'articles')
  {
    $this->setApi($api);
    $this->browser = new sfWebBrowser($this->default_headers);
  }
  
  public function setApi($api)
  {
    if (in_array($api,$this->apis))
    {
      $this->api_key = sfConfig::get('app_nytimes_' . $api . '_api_key');
      $this->api_url = $this->api_urls[$api];
      $this->api = $api;
    }
  }
  
  public function getQueryUrl(array $params)
  {
    if (!$this->api_key)
    {
      return false;
    }
    $arr = array('api-key=' . $this->api_key);
    foreach($params as $pk => $pv)
    { 
      $arr[] = $pk . "=" . $pv;
    }
    $query_url = $this->api_url . "?" . implode('&',$arr);   
    $this->query_url = $query_url;
    return $query_url;
  }
  
  public function tagSearch($search_terms, $filter = 'Per', $max = 20)
  {
    $this->setApi('tags');
    $filter = (array) $filter;
    $arr = array();
    $arr['query'] = urlencode($search_terms);
    if ($filter)
    {
      $arr['filter'] = '(' . implode(',',$filter) . ')';
    }
    if ($max)
    {
      $arr['max'] = $max;
    }
    return $this->getResponse($arr);
  }
  
  public function entityTagSearch($entity)
  {
    $search_terms = $entity->nameSearch();
    $filter = substr($entity->primary_ext,0,3);
    $this->tagSearch($search_terms, $filter);
  }
  
  public function articleSearch($search_terms = "", $facets = array(), $offset = 0)
  {
    $this->setApi('articles');
    $facet_str = "";
    foreach($facets as $fk => $fv)
    {
      if (in_array($fk,$this->facets))
      {
        $facet_str .= " " . $fk . ":[" . $fv . "]";
      } 
    }
    $params = array("query" => urlencode($search_terms) . $facet_str, 'offset' => 0);
    return $this->getResponse($params);  
  } 
  
  public function getResponse($params = null)
  {
    if (!$this->query_url && $params)
    {
      $this->getQueryUrl($params);
    }
    
    if ($this->query_url && !$this->browser->get($this->query_url)->responseIsError())
    {
      $this->response = $this->browser->getResponseText();
      return $this->response;
    }
  }
  
  public function getParsedResponse($params = null)
  {
    if (!$this->response)
    {
      $this->getResponse($params);
    }
    $response = json_decode($this->response);
    $response = (array) $response;
    $this->parsed_response = $response; 
    return $response;
  }
  
  public function findKeys($entity)
  {
    $this->entityTagSearch($entity);
    $response = $this->getParsedResponse();
    $results = $response['results'];
    $keys = array();
    foreach($results as $result)
    {
      $result = self::cleanTag($result);
      $keys[] = array('name' => '', 'id' => $result);
    }
    return $keys;
  }
  
  //not working for orgs, NYT doesn't have good tag to page mapping in place
  public static function getTopicUrl($tag,$type)
  {
    $tag = strtolower($tag);
    
    if ($type == 'Person')
    {
      $base_url = 'http://topics.nytimes.com/top/reference/timestopics/';
      $parts = explode(", ", trim($tag));
      if (count($parts) > 1)
      {
        $tag = $parts[1] . "_" . $parts[0];
        $index = substr($parts[0],0,1);
        $category = 'people';  
      }
      else return false;
    }
    $tag = str_replace(" ", "_",$tag);
    if (isset($index) && isset($category))
    {
      $url = $base_url . $category . "/" . $index . "/" . $tag . "/index.html";
      return $url;
    }
    else return false;
  }
  
  public static function getKeyUrl($key)
  {
    return self::getTopicUrl($key, $key->Entity->primary->ext);
  }
  
  public static function cleanTag($tag)
  {
    $tag = trim(preg_replace("/\((Per|Org)\)/is","",$tag));
    return $tag;
  }


}
