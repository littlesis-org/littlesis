<?php

class LsDoctrineQuery extends Doctrine_Query
{
  protected $_params = array('select' => array(),
                             'join' => array(),
                             'where' => array(),
                             'set' => array(),
                             'having' => array());


  public static function create($conn = null)
  {
    $loggedIn = sfContext::hasInstance() && sfContext::getInstance()->getUser()->isAuthenticated();

    if (!$loggedIn && (sfConfig::get('app_cache_query_enabled') || sfConfig::get('app_cache_result_enabled')))
    {
      if (sfConfig::get('app_cache_driver') == 'Apc')
      {
        $driver = new Doctrine_Cache_Apc();
      }
      else
      {
        $servers = array(
          'host' => '127.0.0.1',
          'port' => 11211,
          'persistent' => true
        );    
        $memcacheAry = array('servers' => $servers, 'compression' => false);
        $driver = new Doctrine_Cache_Memcache($memcacheAry);
      }
    }

    $q = new LsDoctrineQuery($conn);

    if (!$loggedIn && sfConfig::get('app_cache_query_enabled'))
    {
      $q->useQueryCache($driver);
    }
    
    if (!$loggedIn && sfConfig::get('app_cache_result_enabled'))
    {
      $q->useResultCache($driver);
    }

    return $q;
  }


  static function _fixWhere($where, $params=null)
  {
    //if passed null params and clause without a ?, no conversion necessary
    if (is_null($params) && !strstr($where, '?'))
    {
      return array($where, null);
    }


    //convert scalar to array
    if (!is_array($params))
    {
      $params = array($params);
    }


    $whereParts = explode('?', $where);
        
    if (count($params) + 1 != count($whereParts))
    {
      throw new Exception("Number of query values doesn't match number of ?s");
    }

    
    $newWhere = '';
    $newParams = array();
    $count = count($whereParts);
    
    for ($n = 0; $n < $count; $n++)
    {
      if ($n == $count - 1)
      {
        $newWhere .= $whereParts[$n];
        break;
      }

      if ((in_array(substr($whereParts[$n], -3), array('<> ', '!= '))) && is_null($params[$n]))
      {
        //check for not-equals first, otherwise the equals check will work
        $newWhere .= substr($whereParts[$n], 0, strlen($whereParts[$n]) -3) . 'IS NOT NULL';      
      }
      elseif ((substr($whereParts[$n], -2) == '= ') && is_null($params[$n]))
      {
        $newWhere .= substr($whereParts[$n], 0, strlen($whereParts[$n]) -2) . 'IS NULL';
      }
      else
      {
        $newParams[] = $params[$n];
        $newWhere .= $whereParts[$n] . '?';
      }
    }
    
    //convert empty array to null
    if (!count($newParams))
    {
      //$newParams = null;
    }
    
    return array('where' => $newWhere, 'params' => $newParams);
  }
  
  
  public function where($where, $params = array())
  {
    $fixed = self::_fixWhere($where, $params);

    return parent::where($fixed['where'], $fixed['params']);
  }
  
  
  public function addWhere($where, $params = array())
  {
    $fixed = self::_fixWhere($where, $params);

    return parent::addWhere($fixed['where'], $fixed['params']);  
  }

  
  public function andWhere($where, $params = array())
  {
    $fixed = self::_fixWhere($where, $params);

    return parent::andWhere($fixed['where'], $fixed['params']);  
  }


  public function getParams($params = array())
  {
    return array_merge($params, $this->_params['select'], $this->_params['join'], $this->_params['set'], $this->_params['where'], $this->_params['having']);
  }

   
  public function select($select, $params = array())
  {
      if (is_array($params)) {
          $this->_params['select'] = array_merge($this->_params['select'], $params);
      } else {
          $this->_params['select'][] = $params;
      }
      return $this->_addDqlQueryPart('select', $select);
  }
  

  public function count($params = array())
  {
      $q = $this->getCountQuery();

      if ( ! is_array($params)) {
          $params = array($params);
      }

      $params = array_merge($this->_params['select'], $this->_params['join'], $this->_params['where'], $this->_params['having'], $params);

      $results = $this->getConnection()->fetchAll($q, $params);

      if (count($results) > 1) {
          $count = count($results);
      } else {
          if (isset($results[0])) {
              $results[0] = array_change_key_case($results[0], CASE_LOWER);
              $count = $results[0]['num_results'];
          } else {
              $count = 0;
          }
      }

      return (int) $count;
  }


  public function fetch($fetchMode=null)
  {
    $mode = $this->_hydrator->getHydrationMode();
    $this->setHydrationMode(Doctrine::HYDRATE_NONE);    
    $result = $this->getConnection()->execute($this->getSqlQuery(), $this->getParams())->fetch($fetchMode);
    $this->setHydrationMode($mode);
    
    return $result;        
  }

  
  public function fetchAll($fetchMode=null)
  {
    $mode = $this->_hydrator->getHydrationMode();
    $this->setHydrationMode(Doctrine::HYDRATE_NONE);    
    $results = $this->getConnection()->execute($this->getSqlQuery(), $this->getParams())->fetchAll($fetchMode);
    $this->setHydrationMode($mode);
    
    return $results;        
  }
  
  public function whereParenWrap() {
		$where = $this->_dqlParts['where'];
		if (count($where) > 0) {
			array_unshift($where, '(');
			array_push($where, ')');
			$this->_dqlParts['where'] = $where;
		}

		return $this;
	} 
}