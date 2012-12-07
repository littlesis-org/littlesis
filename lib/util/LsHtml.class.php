<?php

class LsHtml
{
  
  static $fontStyleTags  = array('i','a','b','font','big','small','strike','strong','sub','sup','u');
  
  static $layoutTags = array('td','tr','table','div','p','br','th','span');
  
  //html_entity_decode but replaces greater than and less than codes with curly brackets rather than > and < to avoid problems with tag recognition
  static function replaceEntities ($str)
  {
    $str = str_ireplace('&lt;','{',$str);
    $str = str_ireplace('&gt;','}',$str);
    //html_entity_decode replaces non-breaking spaces with something stupid
    $str = str_ireplace('&#160;',' ', $str);
    $str = str_ireplace('&#149;','*', $str);    
    $str = str_ireplace('&nbsp;',' ', $str);
    //$str = str_ireplace('&Aacute;','A',$str);
    $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
    return $str;
  }  
  
  //like strip_tags() but allows for custom replacement string (instead of '')
  //setting $incompletes to true means that strings like 'td>little sister</td><td' are returned as 'little sister' rather than 'td>little sister<td' 
  static function stripTags($text, $replace = ' ', $incompletes = false)
  {
    $text = preg_replace('/<[^>]*>/is',$replace, $text);
    $text = str_ireplace("&nbsp;"," ", $text);
    if ($incompletes)
    {
      $text = preg_replace('/[^>]*>/', $replace, $text);
      $text = preg_replace('/<[^>]*/', $replace, $text);
    }
    return $text;
  }
  
  static function getStringInTag($str, $tag, $start, $end = null)
  {
    $ret = null;
    $tag = trim(LsString::stripNonAlpha($tag));
    if (!$end) $end = $start;
    $a = strripos(substr($str,0,$start), '<' . $tag);
    $aa = strripos(substr($str,0,$start), '</' . $tag);
    if ($aa > $a) $a = false;
    $b = stripos($str, '</' . $tag, $end);
    $bb = stripos($str, '<' . $tag, $end);
    if ($b !== false && $b < $bb)
    {
      $b = stripos($str,'>',$b) + 1;
    }
    else $b = false;
    if ($a !== false && $b !== false)
    {
      $ret = substr($str,$a, $b-$a);
    }
    return $ret;
  }
  
  static function replaceFontStyleTags ($str, $replace = ' ')
  {
    foreach(self::$fontStyleTags as $tag)
    {
      $str = preg_replace('/<\/?' . $tag . '\b[^>]*>/isu',$replace,$str);    
    }
    return $str;
  }
  
  static function matchLinks($str)
  {
    preg_match_all('/<a([^>]*)>([^<]*)<\/a/is',$str,$matches,PREG_SET_ORDER);
    $links = array();
    foreach($matches as $match)
    {
      $link['text'] = $match[2];
      $link['url'] = '';
      if (preg_match('/href\s*\=\s*"([^"]*)"/isu',$match[1],$href_match))
      {
        $link['url'] = $href_match[1];
      }
      $link['str'] = $match[0];
      $links[] = $link;
    }
    return $links;
  }
  
  static function matchImages($str)
  {
    $images = array();
    if (preg_match_all('/<img[^>]*>/isu',$str,$matches))
    {
      $image = array('url' => null, 'alt' => null);
      foreach ($matches[0] as $match)
      {
        if (!preg_match('/\.(png|gif|svg|jpg)\b/isu',$match))
        {
          continue;
        }
        if (preg_match('/\balt\s*=\s*["\']([^"\']*)["\']/isu',$match,$m))
        {
          $image['alt'] = trim($m[1]);
        }
        if (preg_match('/\bsrc\s*=\s*["\']([^"\']*)["\']/isu',$match,$m))
        {
          $image['url'] = trim($m[1]);
        }
        $images[] = $image;        
      }
    }
    if (count($images))
    {
      return $images;
    }
    else
    {
      return null;
    }
  }
  
  static function getSurroundingTags($str, $pos, $lim)
  {
    $str1 = substr($str,0,$pos);
    $str2 = substr($str,$pos);
    $re1 = '/<\/?(([A-z0-9]+)[^>]*)>[^<]*$/isu';
    $re2 = '/^[^<]*<\/?(([A-z0-9]+)[^>]*)>/isu';
    $arr1 = array();
    $arr2 = array();
    for ($i = 0; $i < $lim; $i++)
    {
      if (preg_match($re1,$str1,$match))
      {
        $arr1[] = array($match[1],$match[2]);
        $str1 = substr($str1,0,strlen($str1) - strlen($match[0]));
      }      
      else
      {
        break;
      }  
    }  
    for ($i = 0; $i < $lim; $i++)
    {
      if (preg_match($re2,$str2,$match))
      {
        $arr2[] = array($match[1],$match[2]);
        $str2 = substr($str2,strlen($match[0]));
      }      
      else
      {
        break;
      }  
    }
    return array($arr1,$arr2); 
  }
  
  static function tagCount($str)
  {
    return preg_match_all('/<[^>]*>/',$str,$matches);
  }
  
  static function findParagraphs($text)
  {
    preg_match_all('/<p.*?>(.*?)<.p.*?>/is',$text,$matches);
    if (count($matches))
    {
      $text = implode('<br><br>',$matches[1]);
      return $text;
    }
  }

  static function parseNyDonations($str)
  {
    $re2 = '/(<td.*?>(.*?\s).*?<.td>\s*)*?<.tr>/is';
    preg_match_all($re2, $str, $matches);
    $results = array();
    foreach($matches[0] as $match)
    {
      $result = array("name" => "", "street" => "", "city" => "");
      $arr = preg_split('/<.td>\s*<td.*?>/is',$match);
      $name_parts = preg_split('/<br>/is',$arr[0]);
      if (count($name_parts) > 1 && count($arr) > 5)
      {
        if (preg_match('/(inc|llp|llc|p\.c\.|pc)\.?$/is',$name_parts[0],$match))
        {
          $result['name'] = $name_parts[0];
        }
        else
        {
          $np = preg_split('/\,\s*/is',$name_parts[0]);
          if (count($np) > 1)
          {
            if(count($np) == 3 && stripos($np[2],"jr") !== 0)
            {
              $result['name'] = $np[2] . " " . $np[0] . ", " . $np[1];
            }
            else
            {
              $result['name'] = $np[1] . " " . $np[0];
            }
          }
          else $result['name'] = $np[0];
        }
        if (count($name_parts) > 1)
        {
          $result['street'] = $name_parts[1];
          if (count($name_parts) > 2)
          {
            $result['city'] = $name_parts[2];
          }
        }
        $result['amount'] = $arr[1];
        $result['date'] = $arr[2];
        $result['committee'] = $arr[3];
        foreach($result as &$r)
        {
          $r = preg_replace('/(\n|(<.*?>))/is',"",$r);
          $r = trim($r);
          $r = LsString::spacesToSpace($r);
        }
        unset($r);
        $results[] = $result;
      }
    }
    $str = implode("\t",array_keys($results[0])) . "\n";
    if(count($results))
    {
      foreach($results as $r)
      {
        $str .= implode("\t",$r);
        $str .= "\n";
      }
    }
    $str = trim($str);
    return $str;
  }

}
