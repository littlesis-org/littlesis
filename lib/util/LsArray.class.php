<?php

class LsArray
{

  //case insensitive search for string in array
  //returns string found in array in instances when case needs to be standardized
  static function inArrayNoCase($str, $array) 
  {
    $str = strtolower($str);
    foreach ($array as $a)
    {
      $orig = $a;
      if (strtolower($a) == $str)
        return $orig;
    }
    return false;
  }

  //sort multidimensional array, $keys is array of keys to bring you to the element you want to sort by
  static function multiSort ($arr, $keys)
  {
    $keys = (array) $keys;
    $new = array();
    $val_arr = array();
    foreach ($arr as $a)
    {
      foreach ($keys as $key)
      {
        $a = $a[$key];
      }
      $val_arr[] = $a;
    }
    asort($val_arr);
    
    while(list($key, $val) = each($val_arr))
    {
      $new[] = $arr[$key];
    }
    return $new;
  
  }
  
  static function aasort (&$array, $key, $desc=0) 
  {
    if (count($array) == 0) return $array;
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    if ($desc)
    {
      arsort($sorter);
    }
    else asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
    return $array;
  }
  
  static function flip($arr)
  {
    $out = array();
  
    foreach ($arr as $key => $subarr)
    {
      foreach ($subarr as $subkey => $subvalue)
      {
           $out[$subkey][$key] = $subvalue;
      }
    }
  
    return $out;
  }
  
  static function strlenSort ($arr)
  {
    $new = array();
    $lens = array_map('strlen', $arr);
    arsort($lens);
    $keys = array_keys($lens);
    for ($i = 0; $i < count($keys); $i++)
    {
      $key = $keys[$i];
      $new[] = $arr[$key];    
    }
    return $new;
  }

    
  static function CsvFileToArrayObject($filename){
   $f = @fopen($filename,'r');
   if (!$f) return false;
   $headers = fgetcsv($f,8090);
   $all = array();
   while (!feof($f)){
    $values = fgetcsv($f,8098);
    $row = array();
    $i=0;
    if (is_array($values)){
       foreach($values as $v){
          if ($i < count($headers)) $row[$headers[$i]] = $v;
        $i++;
       }
       $all[] = (object)$row;
      }
   }
   return $all;
  }

  static function arrayTrim($array) {
    while (!empty($array) and strlen(reset($array)) === 0) {
        array_shift($array);
    }
    while (!empty($array) and strlen(end($array)) === 0) {
        array_pop($array);
    }
    return $array;
}  
}