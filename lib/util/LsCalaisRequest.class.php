<?php

class LsCalaisRequest
{
  protected $licenseId;
  protected $calaisBaseUrl = 'http://api.opencalais.com/enlighten/rest/';
  protected $outputFormat = 'text/simple';
  protected $contentType = 'text/html';
  protected $content;
  protected $response;
  
  
  public function __construct()
  {
    if (!$this->licenseId = sfConfig::get('app_opencalais_license_id'))
    {
      throw new Exception("Can't create LsCalaisRequest object; 'app_opencalais_license_id' not set");
    }

    $defaultHeaders = array(
      'User-Agent' => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20061205 Iceweasel/2.0.0.1 (Debian-2.0.0.1+dfsg-2)'
    );
    $this->browser = new sfWebBrowser($defaultHeaders);
  }
  
  
  public function setOutputFormat($format)
  {
    $this->outputFormat = $format;
  }
  
  
  public function setContent($content)
  {
    $this->content = $content;
  }
  
  
  public function setContentType($type)
  {
    $this->contentType = $type;
  }

  
  public function request()
  {
    $calaisParams = sprintf(
      '<c:params xmlns:c="http://s.opencalais.com/1/pred/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">' . 
      '<c:processingDirectives c:contentType="%s" c:outputFormat="%s" c:docRDFaccesible="false" >' . 
      '</c:processingDirectives>' . 
      '<c:userDirectives>' .
      '</c:userDirectives>' .
      '<c:externalMetadata>' . 
      '</c:externalMetadata>' .
      '</c:params>',
      $this->contentType,
      $this->outputFormat
    );
    

    $httpParams = array(
      'licenseID' => $this->licenseId,
      'content' => $this->content,
      'paramsXML' => $calaisParams
    );
    
    $url = $this->calaisBaseUrl . '?' . http_build_query($httpParams);
    
    if ($this->browser->post($this->calaisBaseUrl, $httpParams)->responseIsError())
    {
      throw new Exception("Can't get response from OpenCalais: " . $url);
    }
    
    return $this->response = $this->browser->getResponseText();
  }
  
  
  public function getResponse()
  {
    return $this->response;
  }
}