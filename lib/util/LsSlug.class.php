<?php

class LsSlug
{
  private static $regexMap = array(
    ' ' => '_',
    '/' => '~'
  );


  static function convertNameToSlug($str)
  {
    foreach (self::$regexMap as $bad => $good)
    {
      $str = preg_replace('#' . $bad . '#', $good, $str);
    }
    
    return $str;
  }


  static function convertSlugToName($str)
  {
    foreach (self::$regexMap as $bad => $good)
    {
      $str = preg_replace('#' . $good . '#', $bad, $str);
    }
    
    return $str;
  }  
}