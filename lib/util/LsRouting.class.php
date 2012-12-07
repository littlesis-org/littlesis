<?php

class LsRouting
{
  static function generateUrlForRedirect($url)
  {
    $routeAry = sfContext::getInstance()->getRouting()->findRoute($url);

    if ($routeAry['name'] != 'default')
    {
      $url = sfContext::getInstance()->getRouting()->generate('default', $routeAry['parameters'], '?', '&', '=');
      $url = trim($url, '/');
    }
    
    
    return $url;
  }
}