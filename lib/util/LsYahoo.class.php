<?php


class LsYahoo
{
  protected $defaultHeaders;
  protected $browser;
  protected $cookieBrowser;
  protected $appid = sfConfig::get('app_yahoo_api_key'); 
  protected $url = 'http://search.yahooapis.com/';
  protected $services = array('image' => 'ImageSearchService/V1/imageSearch',                                
                              'local' => 'LocalSearchService/V3/localSearch',
                              'news' => 'NewsSearchService/V1/newsSearch',
                              'video' => 'VideoSearchService/V1/videoSearch',
                              'extraction' => 'ContentAnalysisService/V1/termExtraction',                              
                              'url' => 'MyWebService/V1/urlSearch',
                              'tag' => 'MyWebService/V1/tagSearch',
                              'related' => 'MyWebService/V1/relatedTags',
                              'context' => 'WebSearchService/V1/contextSearch',
                              'related' => 'WebSearchService/V1/relatedSuggestion',
                              'spelling' => 'WebSearchService/V1/spellingSuggestion',
                              'web' => 'WebSearchService/V1/webSearch',
                              'inlink' => 'SiteExplorerService/V1/inlinkData',
                              'pagedata' => 'SiteExplorerService/V1/pageData',
                              'pint' => 'SiteExplorerService/V1/ping',
                              'update' => 'SiteExplorerService/V1/updateNotification',
                             );
  
  protected $service = 'web';
  private   $responseObject;
  
  function __construct()
  {
    //browser
    $this->defaultHeaders = array(
      'User-Agent' => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20061205 Iceweasel/2.0.0.1 (Debian-2.0.0.1+dfsg-2)'
    );
    $this->browser = new sfWebBrowser($this->defaultHeaders);
    $this->cookieBrowser = new sfWebBrowser($this->defaultHeaders, 'sfCurlAdapter', array('cookies' => true));  
    
    $this->queryBuilder['appid'] = $this->appid;    
    $this->queryBuilder['output'] = 'php';    
    $this->queryBuilder['results'] = '15';    
  }

  public function setService($service)
  {
    if( array_key_exists  ( $service  , $this->services  ) )
    {
      return $this->service = $service;
    }
    else
    {
      return false;
    }
  }
  public function getService()
  {
    return $this->service;    
  }
  
  public function getSupportedServices()
  {
    return array_keys( $this->services );
  }
  
  
  public function getQueryUrl()
  {
    $query_arr = null;
    foreach($this->queryBuilder as $var => $value){
      $query_arr[] = $var . "=" . $value; 
    }    
    $query_str = implode("&", $query_arr);
    
    $url = $this->url . $this->services[ $this->service ]. "?". $query_str;
    
    return $url;
  }
  
  //query
  public function setQuery($query)
  {
    $this->queryBuilder['query'] = urlencode($query);
  }
  
  public function getQuery()
  {
    return $this->queryBuilder['query'];  
  }

  //street
  public function setStreetAddress($street)
  {
    $this->queryBuilder['street'] = urlencode($street);
  }
  
  public function getStreetAddress()
  {
    return $this->queryBuilder['street'];  
  }
  
  
  //postal
  public function setPostalCode($zip)
  {
    $this->queryBuilder['zip'] = urlencode($zip);
  }
  
  public function getPostalCode()
  {
    return $this->queryBuilder['zip'];  
  }
  
  //city
  public function setCity($city)
  {
    $this->queryBuilder['city'] = urlencode($city);
  }
  
  public function getCity()
  {
    return $this->queryBuilder['city'];  
  }
  
  
  //state
  public function setState($state)
  {
    $this->queryBuilder['state'] = urlencode($state);
  }
  
  public function getState()
  {
    return $this->queryBuilder['state'];  
  }


  //site
  public function setSite($site)
  {
    $this->queryBuilder['site'] = urlencode($site);
  }
  
  public function getSite()
  {
    return $this->queryBuilder['site'];  
  }
  
  
  // execute function
  public function execute()
  {
    $url = $this->getQueryUrl();
    
    if (!$this->browser->post($url)->responseIsError())
    {
      $text = $this->browser->getResponseText();
      $this->responseObject = unserialize($text);
      return $this->responseObject;
    }
    else
    {
      return null;
    }  
  }

  public function fetchOne()
  {
    $response = null;
    if($this->responseObject)
    {
      $response = $this->responseObject['ResultSet']['Result'][0];  
    }
    else
    {
      $response = $this->execute();
    }
    
    return $response;
  }

  public function getResults()
  {
    $response = null;
    if($this->responseObject)
    {
      $response = $this->responseObject['ResultSet']['Result'];  
    }
    else
    {
      $response = $this->execute();
    }  
    return $response;
  }
  
  
  
  
}  
?>
