<?php

class LsLogic
{
  /**
   * Returns true if two values are equal or one of them is null; otherwise false
   */
  static function areCompatible($value1, $value2)
  {
    return ($value1 === null || $value2 === null || $value1 == $value2) ? true : false;
  }


  static function isNotNull($val)
  {
    return is_null($val) ? false : true;
  }
  
  
  static function nullOrBoolean($val)
  {
    if (is_null($val) || $val === '')
    {
      return null;
    }
    
    return (bool) $val;
  }
}