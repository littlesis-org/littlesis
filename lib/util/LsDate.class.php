<?php

class LsDate
{
  const BLANK_SPECIFIC = 0;
  const YEAR_SPECIFIC = 1;
  const MONTH_SPECIFIC = 2;
  const DAY_SPECIFIC = 3;
  const COMPARE_AFTER = 1;
  const COMPARE_SAME = 0;
  const COMPARE_BEFORE = -1;
  const COMPARE_UNKNOWN = null;


  protected $_year;
  protected $_month;
  protected $_day;
  
	static $months = array(
		'January', 
		'February',
		'March',
		'April',
		'May',
		'June',
		'July',
		'August',
		'September',
		'October',
		'November',
		'December',
		'Jan',
		'Feb',
		'Mar',
		'Apr',
		'May',
		'Jun',
		'Jul',
		'Aug',
		'Sept',
		'Oct',
		'Nov',
		'Dec'
	);

  public function __construct($date=null)
  {
    $this->setDate($date);    
  }


  public function __toString()
  {
    if ($this->isBlank())
    {
      return null;
    }

    return $this->_year . '-' . $this->_month . '-' . $this->_day;
  }


  public function isBlank()
  {
    return ($this->howSpecific() == self::BLANK_SPECIFIC);
  }


  public function setDate($date)
  {
    //convert null date to zeroes
    if ($date === null)
    {
      $date = '0000-00-00';
    }

    if (preg_match('/\b(\d{1,2})[\/\-](\d{1,2})[\/\-]((\d\d)|((19|20)\d\d))/',$date,$match) == 1)
    {
      $date = '';
      if (strlen($match[3]) == 2)
      {
        if ($match[3][0] == 9) $date .= '19';
        else $date .= '20';
      }
      $date .= $match[3] . '-';
      if (strlen($match[1]) == 1)
      {    
        $date.= '0';
      }
      $date .= $match[1] . '-';
      if (strlen($match[2]) == 1)
      {    
        $date.= '0';
      }
      $date .= $match[2];
    }
    else if (preg_match('/^\d\d\d\d$/',$date,$match))
    {
      $date = $date . '-00-00';
    }
    if (!self::validate($date))
    {
      throw new Exception('Invalid date; must be YYYY-MM-DD; year must be set, and if day is set then month must be set');
    }

    list($this->_year, $this->_month, $this->_day) = explode('-', $date);
  }


  public function setYear($year)
  {
    $year == str_pad($year, 2, '0', STR_PAD_LEFT);

    $this->setDate($year . '-' . $this->_month . '-' . $this->_day);
  }


  public function setMonth($month)
  {
    $month == str_pad($month, 2, '0', STR_PAD_LEFT);

    $this->setDate($this->_year . '-' . $month . '-' . $this->_day);
  }


  public function setDay($day)
  {
    $day == str_pad($day, 2, '0', STR_PAD_LEFT);

    $this->setDate($this->_year . '-' . $this->_month . '-' . $day);
  }


  public function getYear($convert=false)
  {
    if ($convert)
    {
      if ($this->_year == '0000')
      {
        return null;
      }
      else
      {
        return (int) $this->_year;
      }
    }

    return $this->_year;
  }

  
  public function getMonth($convert=false)
  {
    if ($convert)
    {
      if ($this->_month == '00')
      {
        return null;
      }
      else
      {
        return (int) $this->_month;
      }
    }

    return $this->_month;
  }
 
 
  public function getDay($convert=false)
  {
    if ($convert)
    {
      if ($this->_day == '00')
      {
        return null;
      }
      else
      {
        return (int) $this->_day;
      }
    }
    
    return $this->_day;
  }


  static function formatFromText($text, $format='Y-m-d')
  {
    return date($format, strtotime($text));  
  }


  public function format($format='Y-m-d')
  {
    if ($this->howSpecific() < self::DAY_SPECIFIC)
    {
      return $this->__toString();
    }
  
    return self::formatFromText($this->__toString(), $format);
  }


  public function formatForDb()
  {
    if ($this->isBlank())
    {
      return null;
    }
    
    return $this->format();
  }


  public function howSpecific()
  {
    if ((int) $this->_day)
    {
      return self::DAY_SPECIFIC;
    }
    elseif ((int) $this->_month)
    {
      return self::MONTH_SPECIFIC;
    }
    elseif ((int) $this->_year)
    {
      return self::YEAR_SPECIFIC;
    }
    else
    {
      return self::BLANK_SPECIFIC;
    }
  }
  
  
  public function getBySpecificity($specificity)
  {
    if ($specificity == self::DAY_SPECIFIC)
    {
      return $this->_day;
    }
    elseif ($specificity == self::MONTH_SPECIFIC)
    {
      return $this->_month;
    }
    elseif ($specificity == self::YEAR_SPECIFIC)
    {
      return $this->_year;
    }
    else
    {
      throw new Exception("Invalid specificity given");
    }  
  }


  static function getDisambiguatedAsStart(LsDate $date)
  {
    if ($date->isBlank() || $date->howSpecific() == self::DAY_SPECIFIC)
    {
      return $date;
    }

    $month = (int) $date->_month ? $date->_month : '01';
    $day = (int) $date->_day ? $date->_day : '01';
    
    return new LsDate(implode('-', array($date->_year, $month, $day)));
  }
  
  
  static function getDisambiguatedAsEnd(LsDate $date)
  {
    if ($date->isBlank() || $date->howSpecific() == self::DAY_SPECIFIC)
    {
      return $date;
    }

    $month = (int) $date->_month ? $date->_month : '12';
    $day = (int) $date->_day ? $date->_day : self::getLastDayOfMonth($date->_year, $month);
    
    return new LsDate(implode('-', array($date->_year, $month, $day)));
  }


  static function getLastDayOfMonth($month, $year)
  {
    return idate('d', mktime(0, 0, 0, ($month + 1), 0, $year));
  }


  static function validate($date)
  {
    if ($date === null)
    {
      return true;
    }
    elseif (!preg_match('#\d{4}-\d{2}-\d{2}#', $date))
    {
      return false;
    }
    else
    {
      list($year, $month, $day) = explode('-', $date);
      
      if ( (!$year && ($month || $day)) || (!$month && $day) )
      {
        return false;
      }
    }
    
    return true;
  }
  
    
  static function compare(LsDate $date1, LsDate $date2)
  {
    //if exactly one of the dates are blank
    if ($date1->getYear() == '0000' XOR $date2->getYear() == '0000')
    {
      return self::COMPARE_UNKNOWN;
    }

    if ($date1->_year > $date2->_year)
    {
      return self::COMPARE_AFTER; 
    }
    elseif ($date1->_year < $date2->_year)
    {
      return self::COMPARE_BEFORE;
    }
    else
    {
      //years are equal, gotta look closer

      if ( ($date1->_month > $date2->_month) && $date2->_month )
      {
        //first month is greater than nonzero second month
        return self::COMPARE_AFTER;
      }
      elseif ( ($date1->_month < $date2->_month) && $date1->_month )
      {
        //second month is greater than nonzero first month
        return self::COMPARE_BEFORE;
      }
      elseif ($date1->_month == $date2->_month)
      {
        //months are equal, gotta look closer

        if ( ($date1->_day > $date2->_day) && $date2->_day )
        {
          //first day is greater than nonzero second day
          return self::COMPARE_AFTER;
        }
        elseif ( ($date1->_day < $date2->_day) && $date1->_day )
        {
          //second day is greater than nonzero first day
          return self::COMPARE_BEFORE;
        }
        elseif ($date1->_day == $date2->_day)
        {
          //same dates!
          return self::COMPARE_SAME;
        }
        else
        {
          //days are unequal but one of them is zero
          return self::COMPARE_UNKNOWN;
        }
      }
      else
      {
        //months are unequal but one of them is zero
        return self::COMPARE_UNKNOWN;
      }
    }
  }
  
  
  static function lessOrEqual(LsDate $date1, LsDate $date2)
  {
    return in_array(self::compare($date1, $date2), array(self::COMPARE_BEFORE, self::COMPARE_SAME));
  }


  static function greaterOrEqual(LsDate $date1, LsDate $date2)
  {
    return in_array(self::compare($date1, $date2), array(self::COMPARE_AFTER, self::COMPARE_SAME));
  }


  static function areCompatible(LsDate $date1, LsDate $date2)
  {
    return in_array(self::compare($date1, $date2), array(self::COMPARE_SAME, self::COMPARE_UNKNOWN));
  }
  
  static function convertDate($str)
  {
    
    $month = $year = $day = null;
    
    $str = str_replace('.','',$str);
    if (preg_match('/(\p{L}+)\s+(\d\d?)\,\s+(\d\d\d\d)/isu',$str,$match))
    {
      if ($month = array_search($match[1],self::$months))
      {
        $month++;
        if ($month > 12) $month = $month / 2;
      }
      $day = $match[2];
      $year = $match[3];
    }
    
    if ($month && $day && $year)
    {
      if (strlen($month) == 1) $month = '0' . $month;
      if (strlen($day) == 1) $day = '0' . $day;
      $date = $year . '-' . $month . '-' . $day;
      if (strlen($date) == 10)
      {
        return $date;
      }

    }
    
    return null;

  }

  static function getCurrentDateTime()
  {
    return date('Y-m-d H:i:s');
  }
}