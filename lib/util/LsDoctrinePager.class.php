<?php

class LsDoctrinePager extends Doctrine_Pager
{
  protected 
    $_data = null,
    $_hasQuery = null,
    $_queryResults = null,
    $_isAjax = null,
    $_ajaxUpdateId = null,
    $_ajaxIndicatorId = null,
    $_ajaxHash = null,
    $_subsetWithCount = false;


  public function __construct($mixed, $page, $maxPerPage = 0)
  {
    $this->_setExecuted(false);

    if (($mixed instanceOf Doctrine_Query) || ($mixed instanceOf Doctrine_RawSql))
    {
      $this->_setQuery($mixed);
      $this->_hasQuery = true;
    }
    else
    {
      $this->_setData($mixed);
      $this->_hasQuery = false;
    }

    $this->setPage($page);

    $this->setMaxPerPage($maxPerPage);
  }
  

  public function isSubsetWithCount($bool)
  {
    $this->_subsetWithCount = (boolean) $bool;
  }

  
  public function _setData($data)
  {
    if ($data instanceOf Doctrine_Collection)
    {
      $data = $data->getData();
    }

    if (!is_array($data))
    {
      throw new Exception("Can't set non-array data");
    }
  
    $this->_data = $data;
  }
  
  
  public function setAjax($bool)
  {
    $this->_isAjax = $bool;
  }

  
  public function isAjax()
  {
    return $this->_isAjax;
  }


  public function setAjaxUpdateId($id)
  {
    $this->_ajaxUpdateId = $id;
  }
  
  
  public function getAjaxUpdateId()
  {
    return $this->_ajaxUpdateId;
  }


  public function setAjaxIndicatorId($id)
  {
    $this->_ajaxIndicatorId = $id;
  }
  
  
  public function getAjaxIndicatorId()
  {
    return $this->_ajaxIndicatorId;
  }
  
  
  public function setAjaxHash($str)
  {
    $this->_ajaxHash = $str;
  }
  
  
  public function getAjaxHash()
  {
    return $this->_ajaxHash;
  }


  protected function _initialize($params = array())
  {
    if ($this->_hasQuery)
    {
      // retrieve the number of items found
      $count = $this->getCountQuery()->count($this->getCountQueryParams($params));
    }
    else
    {
      $count = count($this->_data);
    }

    if ($this->_numResults === null)
    {
      $this->_setNumResults($count);
    }
    
    $this->_setExecuted(true); // _adjustOffset relies of _executed equals true = getNumResults()

    if ($this->_hasQuery)
    {
      $this->_adjustOffset();
    }
    else
    {
      $this->_setLastPage(
        max(1, ceil($this->getNumResults() / $this->getMaxPerPage()))
      );
    }      
  }
  
  
  public function execute($params = array(), $hydrationMode = null, $force=false)
  {
    if (!$this->getExecuted()) 
    {
      $this->_initialize($params);
    }
    
    if ($this->_hasQuery)
    {
      if (is_null($this->_queryResults) || $force)
      {
        $this->_queryResults = $this->getQuery()->execute($params, $hydrationMode);
      }
      
      return $this->_queryResults;
    }
    else
    {
      if ($this->_subsetWithCount)
      {
        return $this->_data;
      }
      else
      {
        return array_slice($this->_data, $this->getFirstIndice()-1, $this->getMaxPerPage());
      }
    }
  }
  
  
  public function setNumResults($num)
  {
    $this->_numResults = $num;
  }
}