<?php

class Dateable extends Doctrine_Template
{
  public function setTableDefinition()
  {
    $this->hasColumn('start_date', 'string', 10);  
    $this->hasColumn('end_date', 'string', 10);
    $this->hasColumn('is_current', 'boolean');
  }

  public function setUp()
  {
  }


  static function convertForDb($str)
  {
    if (preg_match('#^\d{4}-\d{2}-00$#', $str))
    {
      return $str;
    }
    
    if (preg_match('#^\d{4}$#', $str))
    {
      return $str . '-00-00';
    }
    
    if ($time = strtotime($str))
    {
      return date('Y-m-d', $time);
    }  
    
    return $str;
  }


  static function convertForDisplay($date, $separator='-', $abbreviate=false)
  {
    if ($date === null)
    {
      return null;
    }
    else
    {
      if (!LsDate::validate($date))
      {
        throw new Exception("Can't convert: invalid date");
      }
    }


    list($year, $month, $day) = explode('-', $date);
    
    if (intval($year) < 1930)
    {
      $abbreviate = false;
    }
    
    if (intval($year) > 0 && intval($month) > 0 && intval($day) > 0)
    {
      $ret = date('M j \'y', strtotime($date));
    }
    elseif (intval($year) > 0 && intval($month) > 0)
    {
      //the month needs to be incremented because strtotime interprets '2008-12-00' as '2008-11-31'
      $year = ($month > 10) ? $year+1 : $year;
      $month = ($month + 1) % 12;
      $ret = date('M \'y', strtotime(implode('-', array($year, $month, $day))));
    }
    elseif ($year)
    {
      if ($abbreviate)
      {
        $ret = '\'' . substr($year, 2, 2);
      }
      else
      {
        $ret = $year;
      }
    }
    else
    {
      $ret = null;
    }
     
    return $ret;
  }

  
  public function getDatespan($load=true)
  {
    $object = $this->getInvoker();
    
    return self::getRecordDatespan($object, $load);
  }


  static function getRecordDatespan($record, $load=true)
  {
    $start = $record['start_date'];
    $end = $record['end_date'];
    $current = $record['is_current'];


    //if no start or end date, but is_current is false, we say so
    if (is_null($end) && ($current == '0'))
    {
      return "past";
    }


    //if start == end, return single date
    if ($end && $start == $end)
    {
      return self::convertForDisplay($end, '/', true);
    }


    $s = self::convertForDisplay($start, '/', true);
    $e = self::convertForDisplay($end, '/', true);
    $span = "";

    if ($s)
    {
      $span = $s . "&rarr;";

      if ($e)
      {
        $span .= $e;
      }
    }
    else
    {
      if ($e)
      {
        $span = "?&rarr;" . $e;
      }
    }
  
    return $span;      
  }
  
  
  public function generateDateSpan()
  {
    return new LsDatespan(new LsDate($this->getInvoker()->start_date), new LsDate($this->getInvoker()->end_date));
  }
  
  
  public function getStartDateForDisplay()
  {
    return self::convertForDisplay($this->getInvoker()->start_date);
  }


  public function getEndDateForDisplay()
  {
    return self::convertForDisplay($this->getInvoker()->end_date);
  }

}