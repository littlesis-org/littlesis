<?php

class LsTwitter
{

  public function __construct()
  {
  
  }
  
  public function findKeys($entity)
  {
    $search_terms = $entity->nameSearch() . " site:twitter.com";
    $goog = new LsGoogle();
    $goog->setQuery($search_terms);
    $results = $goog->execute();
    $results = $goog->parseSearchResults($results);
    $matches = array();
    if ($results)
    {
      foreach($results as $r)
      {
        $match = array();
        $url = $r['unescapedUrl'];
        if(preg_match('/(.+?)\(.*?\)\s+on\s+Twitter$/is',$r['titleNoFormatting'], $m_name))
        {
          $match['name'] = trim($m_name[1]);
          if(preg_match('/twitter.com\\/(\#\\/)?([^\\/]+)$/is',$url,$m))
          {
            $match['id'] = $m[2];
            $matches[] = $match;
          }
        }
      } 
    }
    return $matches;
  }
  
}