<?php

class LsSlug
{
  private static $regexMap = array(
    ' ' => '_',
    '/' => '~',
    '+' => '_'
  );


  static function convertNameToSlug($str)
  {
    foreach (self::$regexMap as $bad => $good)
    {
      $str = preg_replace('#' . preg_quote($bad) . '#', $good, $str);
    }
    
    return $str;
  }


  static function convertSlugToName($str)
  {
    foreach (self::$regexMap as $bad => $good)
    {
      $str = preg_replace('#' . preg_quote($good) . '#', $bad, $str);
    }
    
    return $str;
  }  
}