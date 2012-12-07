<?php

class LsWikipedia
{
  
  protected $apiUrl = 'http://en.wikipedia.org/w/api.php';
  protected $baseUrl = "http://en.wikipedia.org/wiki/";
  protected $query;
  protected $defaultHeaders;
  protected $browser;
  protected $cookieBrowser;
  protected $formatTypes = array("json", "php", "xml");
  protected $responseText;
  protected $responseObj;

  
  private $_prop = array();
  private $_rvprop = array();
  private $_iiprop = array();
  private $_title = array();
  private $_pageid = array();
  private $_list = array();
  
  function __construct(){
    //browser
    $this->defaultHeaders = array(
      'User-Agent' => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20061205 Iceweasel/2.0.0.1 (Debian-2.0.0.1+dfsg-2)'
    );
    $this->browser = new sfWebBrowser($this->defaultHeaders);
    $this->cookieBrowser = new sfWebBrowser($this->defaultHeaders, 'sfCurlAdapter', array('cookies' => true));  
    
    $this->query['action'] = "query";    
    $this->query['format'] = "xml";    
    $this->query['redirects'] = "1";            
  }
  
  public function getQueryUrl()
  {
    $query_arr = null;
    foreach($this->query as $var => $value){
      $query_arr[] = $var . "=" . $value; 
    }    
    $query_str = implode("&", $query_arr);
    return $this->apiUrl. "?" . $query_str;
  }
  

  public function setTitle($query)
  {
    unset($this->query['pageids']);
    unset($this->query['search']);
    $this->query['titles'] =  urlencode(trim($query));
  }
  
  public function getTitle()
  {
    return $this->query['titles'];
  }

  public function setSearch($val)
  {
    unset($this->query['pageids']);
    unset($this->query['title']);
    $this->query['search'] = urlencode(trim($val));
  }
  
  
  public function setPageId($query)
  {
    unset($this->query['titles']);
    $this->query['pageids'] =  urlencode(trim($query));
  }
  
  public function getPageId($query)
  {
    return $this->query['pageids'];
  }
  
  public function setFormat($format = 'php')
  {
    if(in_array($format, $this->formatTypes))
    {
      $this->query['format'] = $format;
    }
    else
    {
      return false;
    }
  }
  
  public function getFormat()
  {
    return $this->query['format'];
  }

  /*
   Which properties to get for the titles/revisions/pageids
   Values (separate with '|'): info, revisions, links, langlinks, images, imageinfo, templates, categories, extlinks, categoryinfo, duplicatefiles
  */
  public function setProperty($prop)
  {    
    $this->_prop[] = $prop;
    $this->query['prop'] = implode('|', $this->_prop);    
  }

  public function getProperty($prop)
  {
    return $this->query['prop'];    
  }
  
   
  /*
   Which properties to get for each revision.
   Values (separate with '|'): ids, flags, timestamp, user, size, comment, content
  */
  public function getRevisionProperty()
  {
    return $this->query['rvprop'];    
  }
  
  public function setRevisionProperty($prop)
  {
    $this->_rvprop[] = $prop;
    $this->query['rvprop'] = implode('|', $this->_rvprop);
  }


  /*
   Which properties to get for each revision.
   Values (separate with '|'): ids, flags, timestamp, user, size, comment, content
  */
  public function getImageInfoProperty()
  {
    return $this->query['rvprop'];    
  }
  
  public function setImageInfoProperty($prop)
  {
    $this->_iiprop[] = $prop;
    $this->query['iiprop'] = implode('|', $this->_iiprop);
  }


  public function getLists()
  {
    return $this->query['list'];    
  }
  
  public function setList($list)
  {
    $this->_list[] = $list;
    $this->query['list'] = implode('|', $this->_list);
  }


  
  public function getParameter($name)
  {
    return $this->query[$name];    
  }
  
  public function setParameter($name, $value)
  {
    $this->query[$name] = $value;
  }
  
  /*
    What action you would like to perform  
    sitematrix, opensearch, login, logout, query, expandtemplates, parse, 
    feedwatchlist, help, paraminfo, purge, rollback, delete, undelete, 
    protect, block, unblock, move, edit, emailuser, watch, patrol
    Default: help
  */
  public function setAction($action = 'query')
  {
    $this->query['action'] = $action;     
  }

  public function getAction()
  {
    return $this->query['action'];    
  }
  
  
  /*
    Use the output of a list as the input for other prop/list/meta items
    One value: links, images, templates, categories, duplicatefiles, allimages, 
    allpages, alllinks, allcategories, backlinks, categorymembers, embeddedin, 
    imageusage, search, watchlist, watchlistraw, exturlusage, random 
  */
  public function setGenerator($action = 'query')
  {
    $this->query['generator'] = $action;     
  }

  public function getGenerator()
  {
    return $this->query['generator'];    
  }
  

  
  
  public function getInfoBox()
  {
    if (!preg_match('/\{\{Infobox(.*?)\n\n/isu',$this->responseText,$match))
    {
      return null;
    }
    $infobox_text = $match[1];
    preg_match_all('/\s*\|\s*([a-zA-Z_]+?)\s+\=([^\n]+)/si', $infobox_text, $matches);
    $infobox = null;
    $names = $matches[1];
    $values = $matches[2];
    $unique = 1;
    foreach($names as $key => $name)
    {
      if(isset($infobox[trim($name)]))
      {
        $infobox[trim($name)."_".$unique] = array('clean' => $this->stripAllTags(trim($values[$key])), 'str' => trim($values[$key]));
        $unique++;
      }
      else
      {
        $infobox[trim($name)] = array('clean' => $this->stripAllTags(trim($values[$key])), 'str' => trim($values[$key]));
      }
    }
    return $infobox;
  }

  
  
  
  public function getObject()
  {
    return $this->responseObj;
  }
  
  public function getPlainText()
  {
    return $this->stripAllTags($this->responseText);
  }

  public function getIntroduction()
  {  
    $paragraphs = $this->getParagraphs();
    if(! isset($paragraphs[0]) )
    {
      return false;  
    }

    $first_section = trim($paragraphs[0]);
    $introduction = preg_replace('/^.*?disambiguation.*?\n/isu','', $first_section);
    return trim($introduction);
  }

  public function getParagraphs()
  {
    preg_match_all( "/\={2}\s([^\=]+)\={2}/xis", "==Introduction==\n".$this->stripAllTags($this->responseText), $paragraphs);
    return $paragraphs[1];
  }
  
  public function getUrl()
  {
    return 'http://en.wikipedia.org/wiki/' . str_replace('+','_', $this->getTitle());        
  }
  
  public function stripAllTags( $text ) {  

    // WARNING: the order these functions are called matter!
    
    //find opening paragraph
    if(preg_match("/(\'\'\'.+)/si", $text, $match));
    {        
      //$text = isset($match[1]) ? $match[1] : $text;
    }

    //strip ref tags and contents
    $text = preg_replace( "/\<ref\>(.+?)\<\/ref\>/is", '', $text );
    
    //strip html tags
    $text = preg_replace( "/\<([^>]+)>/i", '', $text );
    
    //strip html comments
    $text = preg_replace( "/\<\!\-\-(.*?)\-\->/xis", ' ', $text );   
    
    //strip single line wikipedia meta tags
    $text = preg_replace( "/{{(.+?)}}\;?/", ' ', $text );          
    
    //strip multiline wikipedia tags      
    $text = preg_replace( "/{{(.+?)}}/s", '', $text );
    
    //strip basic wikipedia references     
    $text = preg_replace("/\[\[([0-9a-zA-Z\s\,\.\-\'\"]+?)\]\]/U", '$1', $text);

    //strip wikipedia images   
    $text = preg_replace("/\[\[Image\:([^\]]+)\]\]/isU", '', $text);
    
    //strip wikipedia links with different names 1st    
    $text = preg_replace("/\[\[[^\]]+\|([^\]]+)\]\]/U", '$1', $text);

    //strip categories    2nd 
    $text = preg_replace("/\[\[[^\]]+\:([^\]]+)\]\]/U", '', $text);          
    


    //strip external wikipedia links    
    $text = preg_replace("/\[http([^\]]+)\]/U", '', $text);          

    
    //strip remaining double brackets
    $text = str_replace(array('[[' , ']]'), array( '' ,  ''), $text );
    
    //strip bold & ital single quotes
    $text = str_replace(array("'''","''"), '', $text);
    
    $text = html_entity_decode(trim($text));

    return $text;
  }
  
  public function isDisambiguation()
  {
    if (stristr($this->responseText,'{{disambig}}'))
    {
      return true;
    }
    else
    {
      return false;
    }
  }

  function getCategories()
  {
    preg_match_all('/\[\[Category\:(.+)\]\]/i', $this->responseText, $matches);
    $categories = null;
    foreach($matches[1] as $category)
    {
      $categories[] = preg_replace("/\|(.+)/", "", $category);
    }
    return $categories;
  }

  // get high level functions
  public function listImagesStartingWith($title, $limit = 20)
  {
    $this->setAction('query');
    $this->setList('allimages');
    $this->setParameter('aifrom', $title);
    $this->setParameter('ailimit', $limit);
    $this->setParameter('aiprop', 'comment|url');
    return $this->execute();
  }
  
  public function requestImages($title)
  {
    $this->setAction('query');
    $this->setGenerator('images');
    $this->setProperty('imageinfo');
    $this->setImageInfoProperty('url');    
    $this->setImageInfoProperty('comment');    
    $this->setTitle($title);
    return $this->execute();      
  }
  
  public function requestCategories($title)
  {
    $this->request($title);      
    return $this->getCategories();
  }

  public function request($title)
  {      
    $this->setAction('query');
    $this->setProperty('revisions');
    $this->setRevisionProperty('content');
    $this->setTitle($title);
    return $this->execute();
  }
  
  public function search($val)
  {
    $this->setAction('opensearch');
    $this->setProperty('links');
    $this->setSearch($val);
    return $this->execute();
  }
  
  //execute function
  public function execute()
  {
    
    if (!$this->browser->get( $this->getQueryUrl() )->responseIsError())
		{
			$text = $this->browser->getResponseText();
      
      switch($this->query['format']){
        case 'xml':
          $this->responseObj = new SimpleXMLElement($text);        
          $this->responseText = isset($this->responseObj->query->pages->page->revisions->rev[0]) ? $this->responseObj->query->pages->page->revisions->rev[0] : null;
        break;        
        /*
        
        case 'php':
          //weird bug in wikipedia api that returns json when doing opensearch even if format is php
          if($this->getAction() == 'opensearch')
          {
            $this->responseObj = json_decode($text);        
            $this->responseText = isset($this->responseObj->result) ? $this->responseObj->result : null;
            break;
          }
          else
          {          
            $this->responseObj = unserialize($text);
            $page = isset( $this->responseObj['query']['pages'] ) ? current($this->responseObj['query']['pages']) : null;      
            $this->responseText = isset($page['revisions'][0]['*']) ? $page['revisions'][0]['*'] : null;
          }
        break;


        case 'json':
          $this->responseObj = json_decode($text);        
          $this->responseText = isset($this->responseObj->result) ? $this->responseObj->result : null;
        break;        
        */
      }
      
      return $this->responseObj;
    }
  }
  
  public function findKeys($entity)
  {
    $search_terms = $entity->nameSearch();
    $response = $this->search($search_terms);
    //var_dump($response);
    $results = (array) $response;
    $keys = array();
    if(isset($results['Section']))
    {
      $results = (array) $results['Section'];
      if(isset($results['Item']))
      {
        $results = (array) $results['Item'];
        //var_dump($results);
        if(isset($results['Url']))
        {
          $results = array($results);
        }
        foreach($results as $result)
        {
          $result = (array) $result;
          $url = $result['Url'];
          $matched = preg_match('/wiki\/(.*)$/is',$url,$match);
          if ($matched)
          {
            $keys[] = array('name' => $result['Text'], 'id' => $match[1]);
          }
        }
      }
    }
    return $keys;
  }



}
