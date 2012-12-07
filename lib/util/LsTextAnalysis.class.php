<?php

class LsTextAnalysis
{

  public function __construct()
  {
  
  }
  
  public static function test()
  {
    $url = 'http://www.nytimes.com/2010/11/02/us/politics/02campaign.html?hp';
    $browser = new sfWebBrowser;
    $text = $browser->get($url)->getResponseText();
    $ret = self::getEntityNames($text,array('Person','Org'));
    //var_dump($ret);
  }
  
  public static function getHtmlEntityNames($text,$entity_types)
  {
    $ls_names = LsLanguage::getHtmlPersonNames($text);
    //var_dump($ls_names);
    $oc = new LsOpencalais;
    $oc->setParameter(array('contentType' => 'text/html' ));
    $oc->setContent($text);
    $oc->execute();
    $response = $oc->getParsedResponse(array("Person", "Company", "Organization")); 
    if ($entity_types == 'all')
    {
      $oc_names = array_merge((array) $response['Person'], (array) $response['Company'], (array) $response['Organization']);
      $names = array_merge($oc_names, $ls_names);
    }
    else if ($entity_types == 'people')
    {
      $oc_names = array_merge((array) $response['Person']);  
      $names = array_merge($oc_names, $ls_names);
    }
    else if ($entity_types == 'orgs')
    {
      $names = array_merge((array) $response['Company'], (array) $response['Organization']);
    }
    return $names;  
  }
  
  public static function getTextEntityNames($text,$entity_types)
  {
    //$ls_names = LsLanguage::getHtmlPersonNames($text);
    //var_dump($ls_names);
    $oc = new LsOpencalais;
    $oc->setParameter(array('contentType' => 'text/txt' ));
    $oc->setContent($text);
    $oc->execute();
    $response = $oc->getParsedResponse(array("Person", "Company", "Organization"));
    $names = array();
    if ($entity_types == 'all')
    {
      $names = array_merge((array) $response['Person'], (array) $response['Company'], (array) $response['Organization']);
    }
    else if ($entity_types == 'people')
    {
      $names = array_merge((array) $response['Person']);  
    }
    else if ($entity_types == 'orgs')
    {
      $names = array_merge((array) $response['Company'], (array) $response['Organization']);
    }
    return $names;  
  }
  
}