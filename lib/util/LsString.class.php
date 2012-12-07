<?php

class LsString
{

  static function stripNonAlpha($str,$glue = '')
  {
    $alpha = '';
    if (preg_match_all('/\p{L}+/u',$str,$matches))
    {
      $alpha = implode($glue,$matches[0]);
    }
    return $alpha;
  }
  
    
  static function escapeStringForRegex($str) 
  {
  	$chars = array('^','.','[','$','(',')','|','*','+','?','{','\\','/');
    $arr = str_split($str);
  	foreach($arr as &$a) {
  	  if (in_array($a, $chars))
  	  {
  	    $a = '\\' . $a;
  	  }
  	}
  	$str = implode($arr);
  	$re = preg_replace('/\s+/','\s+',$str);
  	return $re;
  }
  
  
  static function checkCase($str)
  {
    $ret = null;
    $str = self::stripNonAlpha($str);
    
    if (preg_match('/^\p{L}/u',$str) == 0)
    {
      $ret = null;
    }
        
    else if (preg_match('/(\p{L}){2,}/u',$str) == 0 && preg_match('/\p{Lu}/u',$str) == 1)
    {
      $ret = 'initial';
    }

    else if (strtoupper($str) == $str)
    {
      $ret = 'upper';
    }

    else if (strtolower($str) == $str)
    {
      $ret = 'lower';
    }   
    
    //eg McDonald
    else if (preg_match('/^\p{Lu}(\p{Ll}){1,3}\p{Lu}\p{Ll}+$/u',$str) == 1)
    {
      $ret = 'camelCapitalized';
    } 
    
    //eg McDONALD
    else if (preg_match('/^\p{Lu}(\p{Ll}){1,3}\p{Lu}+$/u',$str) == 1)
    {
      $ret = 'camelUpper';
    } 
    
    else if (preg_match('/^\p{Lu}/u',$str) == 1)
    {
      $ret = 'capitalized';    
    }
    else
    {
      $ret = 'unknown';
    }
    
    return $ret;
  }

  //returns all needle match positions in haystack up to max (if set)
  static function striposMulti($haystack, $needle, $max = null, $offset = 0)
  {
    $pos = true;
    $arr = array();
    while ($offset < strlen($haystack) && $pos !== false)
    {
      if ($max !== null)
      {
        if (count($arr) == $max) break;
      }
      $pos = stripos($haystack,$needle,$offset);
      if ($pos !== false)
      {
        $arr[] = $pos;
        $offset = $pos + 1;
      }
    }
    return $arr;
  }  
  
  static function utf8TransUnaccent($instr) 
  {
    $tranmap = array(
      "\xC3\x80" => "A",   "\xC3\x81" => "A",   "\xC3\x82" => "A",   "\xC3\x83" => "A",
      "\xC3\x84" => "A",   "\xC3\x85" => "A",   "\xC3\x86" => "AE",  "\xC3\x87" => "C",
      "\xC3\x88" => "E",   "\xC3\x89" => "E",   "\xC3\x8A" => "E",   "\xC3\x8B" => "E",
      "\xC3\x8C" => "I",   "\xC3\x8D" => "I",   "\xC3\x8E" => "I",   "\xC3\x8F" => "I",
      "\xC3\x90" => "D",   "\xC3\x91" => "N",   "\xC3\x92" => "O",   "\xC3\x93" => "O",
      "\xC3\x94" => "O",   "\xC3\x95" => "O",   "\xC3\x96" => "O",   "\xC3\x98" => "O",
      "\xC3\x99" => "U",   "\xC3\x9A" => "U",   "\xC3\x9B" => "U",   "\xC3\x9C" => "U",
      "\xC3\x9D" => "Y",   "\xC3\x9E" => "P",   "\xC3\x9F" => "ss",
      "\xC3\xA0" => "a",   "\xC3\xA1" => "a",   "\xC3\xA2" => "a",   "\xC3\xA3" => "a",
      "\xC3\xA4" => "a",   "\xC3\xA5" => "a",   "\xC3\xA6" => "ae",  "\xC3\xA7" => "c",
      "\xC3\xA8" => "e",   "\xC3\xA9" => "e",   "\xC3\xAA" => "e",   "\xC3\xAB" => "e",
      "\xC3\xAC" => "i",   "\xC3\xAD" => "i",   "\xC3\xAE" => "i",   "\xC3\xAF" => "i",
      "\xC3\xB0" => "o",   "\xC3\xB1" => "n",   "\xC3\xB2" => "o",   "\xC3\xB3" => "o",
      "\xC3\xB4" => "o",   "\xC3\xB5" => "o",   "\xC3\xB6" => "o",   "\xC3\xB8" => "o",
      "\xC3\xB9" => "u",   "\xC3\xBA" => "u",   "\xC3\xBB" => "u",   "\xC3\xBC" => "u",
      "\xC3\xBD" => "y",   "\xC3\xBE" => "p",   "\xC3\xBF" => "y",
      "\xC4\x80" => "A",   "\xC4\x81" => "a",   "\xC4\x82" => "A",   "\xC4\x83" => "a",
      "\xC4\x84" => "A",   "\xC4\x85" => "a",   "\xC4\x86" => "C",   "\xC4\x87" => "c",
      "\xC4\x88" => "C",   "\xC4\x89" => "c",   "\xC4\x8A" => "C",   "\xC4\x8B" => "c",
      "\xC4\x8C" => "C",   "\xC4\x8D" => "c",   "\xC4\x8E" => "D",   "\xC4\x8F" => "d",
      "\xC4\x90" => "D",   "\xC4\x91" => "d",   "\xC4\x92" => "E",   "\xC4\x93" => "e",
      "\xC4\x94" => "E",   "\xC4\x95" => "e",   "\xC4\x96" => "E",   "\xC4\x97" => "e",
      "\xC4\x98" => "E",   "\xC4\x99" => "e",   "\xC4\x9A" => "E",   "\xC4\x9B" => "e",
      "\xC4\x9C" => "G",   "\xC4\x9D" => "g",   "\xC4\x9E" => "G",   "\xC4\x9F" => "g",
      "\xC4\xA0" => "G",   "\xC4\xA1" => "g",   "\xC4\xA2" => "G",   "\xC4\xA3" => "g",
      "\xC4\xA4" => "H",   "\xC4\xA5" => "h",   "\xC4\xA6" => "H",   "\xC4\xA7" => "h",
      "\xC4\xA8" => "I",   "\xC4\xA9" => "i",   "\xC4\xAA" => "I",   "\xC4\xAB" => "i",
      "\xC4\xAC" => "I",   "\xC4\xAD" => "i",   "\xC4\xAE" => "I",   "\xC4\xAF" => "i",
      "\xC4\xB0" => "I",   "\xC4\xB1" => "i",   "\xC4\xB2" => "IJ",  "\xC4\xB3" => "ij",
      "\xC4\xB4" => "J",   "\xC4\xB5" => "j",   "\xC4\xB6" => "K",   "\xC4\xB7" => "k",
      "\xC4\xB8" => "k",   "\xC4\xB9" => "L",   "\xC4\xBA" => "l",   "\xC4\xBB" => "L",
      "\xC4\xBC" => "l",   "\xC4\xBD" => "L",   "\xC4\xBE" => "l",   "\xC4\xBF" => "L",
      "\xC5\x80" => "l",   "\xC5\x81" => "L",   "\xC5\x82" => "l",   "\xC5\x83" => "N",
      "\xC5\x84" => "n",   "\xC5\x85" => "N",   "\xC5\x86" => "n",   "\xC5\x87" => "N",
      "\xC5\x88" => "n",   "\xC5\x89" => "n",   "\xC5\x8A" => "N",   "\xC5\x8B" => "n",
      "\xC5\x8C" => "O",   "\xC5\x8D" => "o",   "\xC5\x8E" => "O",   "\xC5\x8F" => "o",
      "\xC5\x90" => "O",   "\xC5\x91" => "o",   "\xC5\x92" => "CE",  "\xC5\x93" => "ce",
      "\xC5\x94" => "R",   "\xC5\x95" => "r",   "\xC5\x96" => "R",   "\xC5\x97" => "r",
      "\xC5\x98" => "R",   "\xC5\x99" => "r",   "\xC5\x9A" => "S",   "\xC5\x9B" => "s",
      "\xC5\x9C" => "S",   "\xC5\x9D" => "s",   "\xC5\x9E" => "S",   "\xC5\x9F" => "s",
      "\xC5\xA0" => "S",   "\xC5\xA1" => "s",   "\xC5\xA2" => "T",   "\xC5\xA3" => "t",
      "\xC5\xA4" => "T",   "\xC5\xA5" => "t",   "\xC5\xA6" => "T",   "\xC5\xA7" => "t",
      "\xC5\xA8" => "U",   "\xC5\xA9" => "u",   "\xC5\xAA" => "U",   "\xC5\xAB" => "u",
      "\xC5\xAC" => "U",   "\xC5\xAD" => "u",   "\xC5\xAE" => "U",   "\xC5\xAF" => "u",
      "\xC5\xB0" => "U",   "\xC5\xB1" => "u",   "\xC5\xB2" => "U",   "\xC5\xB3" => "u",
      "\xC5\xB4" => "W",   "\xC5\xB5" => "w",   "\xC5\xB6" => "Y",   "\xC5\xB7" => "y",
      "\xC5\xB8" => "Y",   "\xC5\xB9" => "Z",   "\xC5\xBA" => "z",   "\xC5\xBB" => "Z",
      "\xC5\xBC" => "z",   "\xC5\xBD" => "Z",   "\xC5\xBE" => "z",   "\xC6\x8F" => "E",
      "\xC6\xA0" => "O",   "\xC6\xA1" => "o",   "\xC6\xAF" => "U",   "\xC6\xB0" => "u",
      "\xC7\x8D" => "A",   "\xC7\x8E" => "a",   "\xC7\x8F" => "I",
      "\xC7\x90" => "i",   "\xC7\x91" => "O",   "\xC7\x92" => "o",   "\xC7\x93" => "U",
      "\xC7\x94" => "u",   "\xC7\x95" => "U",   "\xC7\x96" => "u",   "\xC7\x97" => "U",
      "\xC7\x98" => "u",   "\xC7\x99" => "U",   "\xC7\x9A" => "u",   "\xC7\x9B" => "U",
      "\xC7\x9C" => "u",
      "\xC7\xBA" => "A",   "\xC7\xBB" => "a",   "\xC7\xBC" => "AE",  "\xC7\xBD" => "ae",
      "\xC7\xBE" => "O",   "\xC7\xBF" => "o",
      "\xC9\x99" => "e",

      "\xC2\x82" => ",",        // High code comma
      "\xC2\x84" => ",,",       // High code double comma
      "\xC2\x85" => "...",      // Tripple dot
      "\xC2\x88" => "^",        // High carat
      "\xC2\x91" => "\x27",     // Forward single quote
      "\xC2\x92" => "\x27",     // Reverse single quote
      "\xC2\x93" => "\x22",     // Forward double quote
      "\xC2\x94" => "\x22",     // Reverse double quote
      "\xC2\x96" => "-",        // High hyphen
      "\xC2\x97" => "--",       // Double hyphen
      "\xC2\xA6" => "|",        // Split vertical bar
      "\xC2\xAB" => "<<",       // Double less than
      "\xC2\xBB" => ">>",       // Double greater than
      "\xC2\xBC" => "1/4",      // one quarter
      "\xC2\xBD" => "1/2",      // one half
      "\xC2\xBE" => "3/4",      // three quarters

      "\xCA\xBF" => "\x27",     // c-single quote
      "\xCC\xA8" => "",         // modifier - under curve
      "\xCC\xB1" => ""          // modifier - under line  
    );

    return strtr($instr, $tranmap);
  }


  static function emptyToNull($str)
  {
    return $str === '' ? null: $str;
  }
  
  static function newlinesToSpaces($str)
  {
    return preg_replace('#[\n\r]#', ' ', $str);
  }


  static function split($str,$re = '\s+')
  {
    return preg_split('/' . $re . '/isu',$str,-1,PREG_SPLIT_NO_EMPTY);
  }
  
  static function spacesToSpace($str)
  {
    $str2 = preg_replace('/\s+/is',' ',$str);
    if ($str2)
    {
      $str = trim($str2);
    }    
    return $str;
  }
  
  static function pluralize($str)
  {
    switch (substr($str, -1))
    {
      case 'y':
        return substr($str, 0, -1) . 'ies';
        break;
      case 's':
        return $str . 'es';
        break;
      default:
        return $str . 's';
        break;    
    }
  }


  static function excerpt($string, $len=50, $truncateStr='...', $wholeWords=true)
  {
    if (strlen($string) >= $len)
    {
      $short = substr($string, 0, $len);
      $exerpt = $short;

      if ($wholeWords)
      {
        if (preg_match('/^(.*)\W[\w]*/', $short, $shorter))
        {
          $exerpt = $shorter[1];
        }
      }
  
      if ($exerpt != $string)
      {
        $exerpt .= $truncateStr;
      }
  
      $ret = $exerpt;
    }
    else
    {
      $ret = $string;
    }
  
    return $ret;
  }
  
  static function withinN($subject,$search1, $search2, $n)
  {
    $arr = LsString::split($subject);
    $w = '[^\s]+\s+';
    $re = '/(' . $w . '){0,' . $n . '}' . $search1 . '\b\,?\s*(' . $w . '){0,' . $n . '}/';
    if (preg_match_all($re,$subject,$matches))
    {
      foreach($matches[0] as $match)
      {
        if (preg_match('/\b' . $search2 . '/isu', $match))
        {
          return true;
        }
      }  
    }
    return false;
  }

}