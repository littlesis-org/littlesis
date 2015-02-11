<?php

class LsGoogle
{
  //reference: http://code.google.com/apis/ajaxsearch/documentation/reference.html#_intro_fonje
  
  protected $_url;
  protected $_baseUrl = 'http://ajax.googleapis.com/ajax/services/search/';
  protected $_service;
  protected $_results;
  protected $_numResults;
  protected $_jsonResult;

  protected $_responseStatus;
  protected $_defaultHeaders;  
  protected $browser;
  protected $cookieBrowser;

  protected $_services = array('web', 'local', 'video', 'blogs', 'news', 'books', 'images', 'patent');

  protected $_params  = array( 'web' => array('v', 'cx', 'cref', 'safe', 'lr'), 
                               'local' => array('v','sll', 'sspn', 'mrt'), 
                               'video' => array('v','scoring'), 
                               'blogs' => array('v','scoring'), 
                               'news' => array('v','scoring', 'geo', 'qsid', 'topic', 'ned'), 
                               'books' => array('v','as_brr', 'as_list'), 
                               'images' => array('v','safe', 'imgsz', 'imgc','imgtype', 'as_filetype', 'as_sitesearch'), 
                               'patents' => array('v','as_psrg', 'as_psra', 'scoring')
                             );
  
  protected $queryBuilder = array();

  public function __construct()
  {
    //browser
    $this->_defaultHeaders = array(
      'User-Agent' => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20061205 Iceweasel/2.0.0.1 (Debian-2.0.0.1+dfsg-2)'
    );
    $this->browser = new sfWebBrowser($this->_defaultHeaders);
    $this->cookieBrowser = new sfWebBrowser($this->_defaultHeaders, 'sfCurlAdapter', array('cookies' => true));  
    
    $this->_service = 'web';    
    $this->setParameter('v', '1.0');
  }

  public function setService($service)
  {
    if(in_array($service, $this->_services))
    {
      $this->_service = $service;
    }
    else 
    {
      return false;
    }
  }

  public function setParameter($name, $value)
  {    

    if( in_array( $name, $this->_params[ $this->_service ] ) )
    {
      $this->queryBuilder[ $name ] = $value;
    }
    
  }  

  public function setQuery($query)
  {
    $this->queryBuilder[ 'q' ] = urlencode($query);
  }

  private function setNumResults($num)
  {
    $this->_numResults = $num;
  }
  
  public function getNumResults()
  {
    return $this->_numResults;
  }  
  
  /*
    EXAMPLE RESULT SET
    ["GsearchResultClass"]=>
    string(10) "GwebSearch"
    ["unescapedUrl"]=>
    string(51) "http://www.techsupportforum.com/members/295877.html"
    ["url"]=>
    string(51) "http://www.techsupportforum.com/members/295877.html"
    ["visibleUrl"]=>
    string(24) "www.techsupportforum.com"
    ["cacheUrl"]=>
    string(74) "http://www.google.com/search?q=cache:IeLI62nW3wkJ:www.techsupportforum.com"
    ["title"]=>
    string(51) "Tech Support Forum - View Profile: <b>Littlesis</b>"
    ["titleNoFormatting"]=>
    string(44) "Tech Support Forum - View Profile: Littlesis"
    ["content"]=>
    string(103) "<b>Littlesis</b> is a Registered User in the Tech Support Forum. View <b>Littlesis&#39;s</b>   profile."

  */
  public function getResults()
  {
    return $this->_results;
  }
  
  public function execute()
  {
    $query = $this->getQueryUrl();
    
    if (!$query) 
    {
      return null;
    }
        
    if (!$this->browser->get($query)->responseIsError())
    {
      $text = $this->browser->getResponseText();
      $results = json_decode($text);
      $this->parseResults($results);
      return $results;
    }
  }

  public function getQueryUrl()
  {
    $query_arr = null;
    foreach($this->queryBuilder as $var => $value){
      $query_arr[] = $var . "=" . $value; 
    }    
    $query_str = implode("&", $query_arr);
    return $this->_baseUrl  .  $this->_service . "?" . $query_str;
  }
  
  
  protected function parseResults($result)
  {
    $this->_responseStatus = (string) $result->responseStatus;
    if ($this->_responseStatus == '200')
    {
      $this->_results = $result->responseData->results;
      if (count($this->_results))
      {
        $this->_numResults = (string) $result->responseData->cursor->estimatedResultCount;      
      }
      else 
      {
        $this->_numResults = 0;
      }
    }
  }
  
  public function parseSearchResults($result)
  {
    $result = (array) $result;
    if (isset($result['responseData']))
    {
      $result = (array) $result['responseData'];
      if (isset($result['results']))
      {
        $result = $result['results'];
        foreach($result as &$r)
        {
          $r = (array) $r;
        }
        return $result;
      }
    }
  }
}


