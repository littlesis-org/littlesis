<?php

class LsOpencalais
{

  protected $url = 'http://api.opencalais.com/enlighten/rest/';
  protected $queryBuilder = array();
  protected $responseObject;

  protected $browser;
  protected $cookieBrowser;

  protected $_results;
  protected $_numResults;
  protected $_jsonResult;
  protected $_responseStatus;
  protected $_defaultHeaders;
  
  protected $_param;
    
  private $processingDirectives = array( "contentType" , "outputFormat" , "reltagBaseURL", "calculateRelevanceScore", "enableMetadataType",  "discardMetadata");
  private $userDirectives = array("allowDistribution", "allowSearch", "externalID", "submitter");
  private $externalMetadata = array();
  
  public function __construct()
  {
    //browser
    $this->_defaultHeaders = array(
      'User-Agent' => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20061205 Iceweasel/2.0.0.1 (Debian-2.0.0.1+dfsg-2)'
    );
    $this->browser = new sfWebBrowser($this->_defaultHeaders);
    $this->cookieBrowser = new sfWebBrowser($this->_defaultHeaders, 'sfCurlAdapter', array('cookies' => true));  

    $this->queryBuilder['licenseID'] = sfConfig::get('app_opencalais_license_id');
    $this->queryBuilder['content'] = null;
    $this->queryBuilder['paramsXML'] = null;
    
    $this->_param = array('processingDirectives', 'userDirectives', 'externalMetadata');
  
    $this->setParameter( array('contentType' => 'text/txt' ) );
    $this->setParameter( array('outputFormat'=> 'application/json' ) );
    
  }

  public function getQueryUrl()
  {
    $query_arr = null;
    foreach($this->queryBuilder as $var => $value){
      $query_arr[] = $var . "=" . $value; 
    }    
    $query_str = implode("&", $query_arr);
    
    return "?". $query_str;
  }
  
  
  public function setContent($content)
  {
    $this->queryBuilder['content'] = $content;
  }
  

  public function setParameter($param = array("contentType" => 'text/txt') )
  {
    
    if( in_array( key($param), $this->processingDirectives ) )
    {
      $this->_param['processingDirectives'][ key($param) ] = current($param);
    }
     
    if( in_array( key($param), $this->userDirectives ) )
    {
      $this->_param['userDirectives'][ key($param) ] = current($param);
    }

    if( in_array( key($param), $this->externalMetadata ) )
    {
      $this->_param['externalMetadata'][ key($param) ] = current($param);
    }

    $this->queryBuilder['paramsXML'] = 
    '<c:params xmlns:c="http://s.opencalais.com/1/pred/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
      <c:processingDirectives ';
      if(isset($this->_param['processingDirectives'])){
        foreach($this->_param['processingDirectives'] as $key => $value)
        {
          $this->queryBuilder['paramsXML'] .= 'c:'.$key."=\"".$value."\" ";
        }
      }      
      $this->queryBuilder['paramsXML'] .=  '>
      </c:processingDirectives>    
      <c:userDirectives ';
      if(isset($this->_param['userDirectives'])){
        foreach($this->_param['userDirectives'] as $key => $value)
        {
          $this->queryBuilder['paramsXML'] .= 'c:'.$key."=\"".$value."\" ";
        }
      }      
      $this->queryBuilder['paramsXML'] .=  '>    
      </c:userDirectives>      
      <c:externalMetadata ';
      if(isset($this->_param['externalMetadata'])){
        foreach($this->_param['externalMetadata'] as $key => $value)
        {
          $this->queryBuilder['paramsXML'] .= 'c:'.$key."=\"".$value."\" ";
        }
      }      
      $this->queryBuilder['paramsXML'] .=  '>          
      </c:externalMetadata>
    </c:params>';
    
  }
  
  public function getParameters()
  {       
    return $this->queryBuilder;  
  }
  
  
  // execute function
  public function execute()
  {
    
    if (!$this->browser->post($this->url, $this->queryBuilder)->responseIsError())
    {
      $text = $this->browser->getResponseText();    
      $this->responseObject = json_decode($text);
      return $this->responseObject;
    }
    else
    {
      return null;
    }  
  }
  
  public function getResponseObjectArray()
  {
    $response = (array) $this->responseObject;
    $response = array_pop($response);
    return $response;
  }
  
  public function getParsedResponse($types = array("Person"))
  {
    $response = (array) $this->responseObject;

    $arr = array_fill_keys($types,array());
    foreach($response as $r)
    {
      $r = (array) $r;
      foreach($types as $type)
      {
        if (isset($r["_type"]) && $r["_type"] == $type)
        {
          $arr[$type][] = str_replace(' , ', ', ', $r['name']);
        }
      }
    }
    return $arr;
  }

}
