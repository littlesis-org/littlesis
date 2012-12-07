<?php

class LsSourceWatch
{
  protected $base_url = 'http://sourcewatch.org/index.php?title=';
  protected $search_page = 'Special:Search';
  protected $default_headers = array(
      'User-Agent' => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20061205 Iceweasel/2.0.0.1 (Debian-2.0.0.1+dfsg-2)');
  protected $query_url;
  protected $response;
  protected $parsed_response;
  
  
  public function __construct()
  {
    $this->browser = new sfWebBrowser($this->default_headers);
  }
  
  public function getQueryUrl($params)
  {
    $arr = array();
    foreach($params as $pk => $pv)
    { 
      if ($pk == 'title')
      {
        $title = $pv;
      }
      $arr[] = $pk . "=" . urlencode($pv);
    } 
    $params = implode("&",$arr);
    if (isset($title))
    {
      $query_url = $this->base_url . $title . "&" . $params;
      $this->query_url = $query_url;
      return $query_url;
    }
    else return false;
  }
  
  public function getResponse($params)
  {
    if(!$this->query_url)
    {
      $this->getQueryUrl($params);
    }
    if(!$this->browser->get($this->query_url)->responseIsError())
    {
      $response = $this->browser->getResponseText();
      return $response;
    }
    else return false;
  }
  
  public function getSearchHits($search_terms)
  {
    $params = array('title' => 'Special:Search','fulltext' => 'Search','search' => $search_terms);
    $response = $this->getResponse($params);
    $response = $this->parseSearchResponse($response);
    return $response;
  }
  
  public function parseSearchResponse($response)
  {
    $matched = preg_match_all('/<li>.*?href...index.php.title.(?<id>.*?)".title..(?<name>.*?)".*?<.li/is',$response,$matches, PREG_SET_ORDER);
    if ($matched)
    {
      return $matches;
    }
  }
  
  public function findKeys($entity)
  {
    $search_terms = $entity->nameSearch();
    $params = array('title' => 'Special:Search','fulltext' => 'Search','search' => $search_terms);
    $response = $this->getResponse($params);
    $matches = $this->parseSearchResponse($response);
    $keys = array();
    $ids = array();
    if ($matches)
    {
      foreach ($matches as $match)
      { 
        array_shift($match);
        if (!in_array($match['id'],$ids))
        {
          $keys[] = $match;
          $ids[] = $match['id'];
        }
      }
      array_unique($keys);
    }
    return $keys;
  }


}
