<?php
//http://search.cpan.org/dist/WWW-Metaweb/

//http://www.freebase.com/view/freebase/metaweb_framework

class LsFreebase {
  protected $URL = "http://www.freebase.com/api/service/mqlread";
  protected $defaultHeaders;
  protected $browser;
  protected $cookieBrowser;
  
/*  
  function browser(){
    #run the query
    $apiendpoint = "http://sandbox.freebase.com/api/service/mqlread?queries";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$apiendpoint=$jsonquerystr");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $jsonresultstr = curl_exec($ch);
    curl_close($ch); 
  
  }
*/
  
  function __construct(){
    //browser
    $this->defaultHeaders = array(
      'User-Agent' => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20061205 Iceweasel/2.0.0.1 (Debian-2.0.0.1+dfsg-2)'
    );
    $this->browser = new sfWebBrowser($this->defaultHeaders);
    $this->cookieBrowser = new sfWebBrowser($this->defaultHeaders, 'sfCurlAdapter', array('cookies' => true));  
  }

  function read($query_arr) {
    // Put the query into an envelope object
    $envelope = array("query" => array($query_arr) );

    // Serialize the envelope object to JSON text
    $querytext =  json_encode($envelope);

    // Then URL encode the serialized text
    $encoded = stripslashes(urlencode($querytext));

    // Now build the URL that represents the query
    // Note that we use an HTTP GET request for read queries 
    $url = $this->URL . "?query=" . $encoded;

    //echo $url."\n";
    if (!$this->browser->get($url)->responseIsError())
    {
      $text = $this->browser->getResponseText();
      $response = json_decode($text);
      

      //Return null if the query was not successful
      if ($response->code != "/api/status/error")
      {
        return $response->result;
      }
      else{
        return null;
      }
    }
  }
}
