<?php

class LsNumber
{

  //takes dollar amount in any format, factor to multiply it by, and returns number 
  //eg $ 5,003.26 million becomes 5003260000
  static function formatDollarAmountAsNumber($str, $factor = 1)
  {
    $decimal = '';
    $matched = preg_match('/\.\d+/',$str, $match);
    if ($matched == 1)
    {
      $decimal = $match[0];
    }
    $str = preg_replace("/\.\d+/",'',$str);
    $str = preg_replace("/\D/",'',$str);
    $str = $str . $decimal;
    $amt = $str * $factor;
    return $amt;
  }

  static function clean($str)
  {
    $str = trim($str);
    $str = str_replace(',','',$str);
    return $str;
  }
  
  static function compare($num1, $num2)
  {
    $num1 = (int) $num1;
    $num2 = (int) $num2;

    if ($num1 > $num2)
    {
      return 1;    
    }
    elseif ($num1 == $num2)
    {
      return 0;
    }
    else
    {
      return -1;
    }
  }


  static function makeReadable($num, $prefix=null, $decimals=1)
  {
    if (is_null($num))
    {
      return null;
    }
  
    $ret = null;
  
    if ($num >= 1000000000000)
    {
      $ret .= number_format($num / 1000000000000, $decimals, '.', '') . ' trillion';	
    }
    elseif ($num >= 1000000000)
    {
      $ret .= number_format($num / 1000000000, $decimals, '.', '') . ' billion';
    }
    elseif ($num >= 1000000)
    {
      $ret .= number_format($num / 1000000, $decimals, '.', '') . ' million';
    }
    else
    {
      $ret .= $num;
    }
    
    if ($ret)
    {
      $ret = $prefix . $ret;
    }
    
    return $ret;
  }
  
  

  static function makeBytesReadable($bytes) 
  {
    $size = $bytes / 1024;
    if($size < 1024)
    {
      $size = number_format($size, 2);
      $size .= ' KB';
    } 
    else 
    {
      if($size / 1024 < 1024) 
      {
        $size = number_format($size / 1024, 2);
        $size .= ' MB';
      } 
      else if ($size / 1024 / 1024 < 1024)  
      {
        $size = number_format($size / 1024 / 1024, 2);
        $size .= ' GB';
      } 
    }
    return $size;
  }
    
}