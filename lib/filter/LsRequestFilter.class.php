<?php

class LsRequestFilter extends sfFilter
{
	public function execute($filterChain)
	{
		if (true || $this->isFirstCall())
		{
			$request = $this->getContext()->getRequest();
			$params = $request->getParameterHolder()->getAll();

			foreach ($params as $key => $val)
			{
				if (strcmp((string) $val, '') == 0)
				{
					$request->setParameter($key, null);
				}
				elseif (is_array($val))
				{
          $val = self::emptyStringsToNull($val);
          $request->setParameter($key, $val);
				}
				
				if ($val === 'on')
				{
				  $request->setParameter($key, true);
				}
				elseif (is_array($val))
				{
				  $val = self::checkboxToBoolean($val);
				  $request->setParameter($key, $val);
				}
			}			
		}
			
		$filterChain->execute();
	}
	
	
	static function emptyStringsToNull(Array $params)
	{
	  foreach ($params as $name => $value)
	  {
	    if (strcmp((string) $value, '') == 0)
	    {
	      $params[$name] = null;
	    }
	  }
	  
	  return $params;
	}


  static function emptyStringToNull($str)
  {
    return ($str === '') ? null : $str;
  }
	
	
	static function checkboxToBoolean(Array $params)
	{
	  foreach ($params as $name => $value)
	  {
      if ($value === 'on')
      {
        $params[$name] = true;
      }
    }
    
    return $params;
	}
}

?>