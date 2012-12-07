<?php

class LsDatespan
{
  private $_start;
  private $_end;
  
  public function __construct($start=null, $end=null)
  {
    if ($start)
    {
      if (!($start instanceOf LsDate))
      {
        $start = new LsDate($start);
      }
      
      $this->setStart($start);
    }
    
    if ($end)
    {
      if (!($end instanceOf LsDate))
      {
        $end = new LsDate($end);
      }
      
      $this->setEnd($end);   
    }  
  }

  public function format($format='Y-m-d', $separator=' - ')
  {
    return $this->_start->format($format) . $separator . $this->_end->format($format);
  }

  public function isValid()
  {
    if ( $this->areYearsSet() && ($this->_start->getYear() > $this->_end->getYear()) )
    {
      var_dump($this->_start->getYear(), $this->_end->getYear());
    
      throw new Exception('Start year is greater than end year: ' . $this->format());
    }  

    if ( $this->areYearsSame() && $this->areMonthsSet() && ($this->_start->getMonth() > $this->_end->getMonth()) )
    {
      throw new Exception('Start month is greater than end month: ' . $this->format());
    }  

    if ( $this->areYearsSame() && $this->areMonthsSame() && $this->areDaysSet() && ($this->_start->getDay() > $this->_end->getDay()) )
    {
      throw new Exception('Start day is greater than end day: ' . $this->format());
    }
    
    return true;
  }
  
  public function areYearsSet()
  {
    return ($this->_start->getYear(true) && $this->_end->getYear(true));
  }

  public function areMonthsSet()
  {
    return ($this->_start->getMonth(true) && $this->_end->getMonth(true));
  }

  public function areDaysSet()
  {
    return ($this->_start->getDay(true) && $this->_end->getDay(true));
  }
  
  public function areYearsSame()
  {
    return ($this->_start->getYear() == $this->_end->getYear());
  }

  public function areMonthsSame()
  {
    return ($this->_start->getMonth() == $this->_end->getMonth());
  }

  public function areDaysSame()
  {
    return ($this->_start->getDay() == $this->_end->getDay());
  }
  
  public function areDatesSame()
  {
    return ($this->_start->format() == $this->_end->format());
  }

  public function setStart(LsDate $start)
  {
    $this->_start = $start;    
  }

  public function setEnd(LsDate $end)
  {
    $this->_end = $end;
  }

  public function getStart()
  {
    return $this->_start; 
  }
  
  public function hasStart()
  {
    return !$this->_start->isBlank();
  }
    
  public function getEnd()
  {
    return $this->_end; 
  }

  public function hasEnd()
  {
    return !$this->_end->isBlank();
  }


  static function getDisambiguated(LsDatespan $span)
  {
    $ret = new LsDatespan;
    
    $ret->setStart(LsDate::getDisambiguatedAsStart($span->getStart()));
    $ret->setEnd(LsDate::getDisambiguatedAsEnd($span->getEnd()));
    
    return $ret;
  }


  /**
   * Determines whether two datespans have overlap. Both start dates must be set.
   */
  static function overlap(LsDatespan $span1, LsDatespan $span2, $disambiguate=true)
  {
    //validity checks
    if (!$span1->hasStart() || !$span2->hasStart())
    {
      throw new Exception('Both start dates must be set');
    }


    //throw exception if invalid dates
    $span1->isValid();
    $span2->isValid();
    

    //disambiguate dates
    if ($disambiguate)
    {
      $span1 = self::getDisambiguated($span1);
      $span2 = self::getDisambiguated($span2);
    }

    //check for overlap
    if ( 
         ( !$span1->hasEnd() && !$span2->hasEnd() ) ||
         ( !$span1->hasEnd() && ($span1->getStart() <= $span2->getStart()) ) ||
         ( !$span2->hasEnd() && ($span1->getstart() >= $span2->getStart()) ) ||
         ( $span1->hasEnd() && ($span1->getStart() <= $span2->getEnd()) && ($span1->getEnd() >= $span2->getStart()) ) ||
         ( $span2->hasEnd() && ($span2->getStart() <= $span1->getEnd()) && ($span2->getEnd() >= $span1->getStart()) ) 
       )
    {
      return true;
    }

    return false;
  }
  
  
  static function merge(LsDatespan $s1, LsDatespan $s2, $disambiguate=true, $mergeAdjacent=false)
  {
    //check for overlap
    if (self::overlap($s1, $s2, $disambiguate))
    {      
      $merged = new LsDatespan;

      $start1 = $s1->getStart();
      $start2 = $s2->getStart();      
      $end1 = $s1->getEnd();
      $end2 = $s2->getEnd();      


      //generate start date
      $comp = LsNumber::compare($start1->howSpecific(), $start2->howSpecific());
      
      switch ($comp)
      {
        //start1 is more specific, use it unless start2's year is earlier
        case 1:
          $merged->setStart( $start1->getYear() <= $start2->getYear() ? $start1 : $start2 );
          break;        
          
        //same specificity, use the earlier date
        case 0:          
          $merged->setStart( $start1->format() <= $start2->format() ? $start1 : $start2 );
          break;
        
        //start2 is more specific, use it unless start1's year is earlier
        case -1:          
          $merged->setStart( $start1->getYear() >= $start2->getYear() ? $start2 : $start1 );
          break;
      }

      
      //generate end date
      if ($end1->isBlank() || $end2->isBlank())
      {
        $merged->setEnd(new LsDate);
      }
      else
      {      
        $comp = LsNumber::compare($end1->howSpecific(), $end2->howSpecific());
        
        switch ($comp)
        {
          //end1 is more specific, use it unless end2's year is later
          case 1:
            $merged->setEnd( $end1->getYear() >= $end2->getYear() ? $end1 : $end2 );
            break;        
            
          //same specificity, use the later date
          case 0:          
            $merged->setEnd( $end1->format() >= $end2->format() ? $end1 : $end2 );
            break;
          
          //end2 is more specific, use it unless end1's year is later
          case -1:          
            $merged->setEnd( $end1->getYear() <= $end2->getYear() ? $end2 : $end1 );
            break;
        }
      }
      
      return $merged;
    }
    else
    {
      return false;
    }
  }
}