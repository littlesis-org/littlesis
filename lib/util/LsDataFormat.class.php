<?php

class LsDataFormat
{
  static function xmlToDoc($xml)
  {
    $doc = new DOMDocument('1.0');
    $doc->preserveWhiteSpace = false;
    $doc->loadXML($xml);
    $doc->formatOutput = true;

    return $doc->saveXML();      
  }


  static function toXmlObject($data, $root=null)
  {
    //structured data
    if (is_array($data))
    {
      if (!$root)
      {
        throw new Exception("Can't convert array to XML without root element name");
      }

      $array = $data;
    }
    elseif (is_object($data))
    {
      $array = (array) $data;
      $root = $root ? $root : get_class($data);
    }
    else
    {
      return self::formatSimpleValueForXml($data);
    }


    //create root element
    $xml = new SimpleXMLElement('<' . $root . ' />');
    
    foreach ($array as $key => $value)
    {
      $xml->addChild($key, self::toXmlObject($value, $key));
    }

    return $xml;
  }


  static function formatSimpleValueForXml($data)
  {
    //non-structured data
    if (is_null($data))
    {
      return '';
    }
    elseif (is_bool($data))
    {
      return (int) $data;
    }
    elseif (is_int($data))
    {
      return $data;
    }
    elseif (is_string($data))
    {
      return self::htmlnumericentities($data);
    }
    else
    {
      return $data;
    }  
  }


  static function toXml($data, $root=null)
  {
    //structured data
    if (is_array($data))
    {
      $array = $data;
    }
    elseif (is_object($data))
    {
      $array = (array) $data;
    }
    else
    {
      return self::formatSimpleValueForXml($data);
    }
    
    $xmlString = $root ? '<' . $root . '>' : '';
    
    foreach ($array as $key => $value)
    {
      $xmlString .= sprintf('<%s>%s</%s>', $key, self::formatSimpleValueForXml($value), $key);
    }
  
    $xmlString .= $root ? '</' . $root . '>' : '';
    
    return $xmlString;
  }


  //turn special characters into numeric html entities, to keep the xml clean
  static function htmlnumericentities($str)
  {
    $str = preg_replace('/.[\000-\010]/', '', $str);  
    return preg_replace('/[^!-%\x27-;=?-~ ]/e', '"&#".ord("$0").chr(59)', $str);
  }


  static function escapeXml($string, $trans='')
  {
    $trans = (is_array($trans)) ? $trans : get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);

    foreach ($trans as $k => $v)
    {
      $trans[$k] = "&#" . ord($k) . ";";
    }
    
    return strtr($string, $trans);
  }


  static function formatXmlString($str) 
  {      
    $doc = new DOMDocument();
    $doc->preserveWhiteSpace = false;
    $doc->formatOutput = true;
    $doc->loadXML($str);
    $doc->saveXML();

    return htmlentities($doc->saveXML());     
  }
}