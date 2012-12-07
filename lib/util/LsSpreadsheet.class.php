<?php

class LsSpreadsheet
{

  static function parse($filename)
  { 
    
    $str = trim(file_get_contents($filename));
    $str = preg_replace('/(\,\"[^\"]*)((\r|\n)([^\"]*)+\"\,)/ise','str_replace(array("\r","\n")," ","\\0")',$str);
    $arr = preg_split("/(\r|\n)/is",$str); 
    if (count($arr) < 2 || count($arr) > 201)
    {
      return false;
    }
    if (preg_match("/\t/",$arr[0]) && preg_match("/\t/", $arr[1]))
    {
      $delimiter = "\t";
    }
    else
    {
      $delimiter = ",";
    }
    $arrResult = array();
    foreach($arr as $a)
    {
      if($csv_arr = self::str_getcsv($a,$delimiter))
      {
        $arrResult[] = $csv_arr;
      }
    } 
    $headers = array_map('strtolower',array_shift($arrResult));
    //cleanup array  
    $rows = array();
    $columns = array();
    foreach($headers as $header)
    {
      $columns[$header] = array();
    }
    foreach($arrResult as $row)
    {
      if (count($row) > 0 && count($headers) > count($row))
      {
        //var_dump($row); die;
        $arr = array_fill(0,count($headers) - count($row),"");
        $row = array_merge($row,$arr);
      }
      else if (count($row) > count($headers))
      {
        $row = array_slice($row,0,count($headers));
      }
      $row = array_change_key_case(array_combine($headers,$row));
      $rows[] = $row;
      foreach($headers as $header)
      {
        $columns[$header][] = $row[$header];
      }
    }
    $result = array('headers' => $headers, 'rows' => $rows, 'columns' => $columns);
    return $result;
  }
  
  static function str_getcsv($input, $delimiter = ",", $enclosure = '"', $escape = "\\") 
  {
    $fp = fopen("php://memory", 'r+');
    fputs($fp, $input);
    rewind($fp);
    $data = fgetcsv($fp, null, $delimiter, $enclosure); // $escape only got added in 5.3.0
    fclose($fp);
    return $data;
  }
  
    
}