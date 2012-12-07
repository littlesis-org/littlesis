<?php

class LsTask extends sfBaseTask
{
  protected $debugMode = false;
  

  protected function execute($arguments = array(), $options = array())
  {  
  }


  protected function printDebug($str, $override=false)
  {
    if ($this->debugMode || $override)
    {
      echo $str . "\n";
      flush();
    }
  }


  public function saveMeta($namespace, $predicate, $value)
  {
    if (!$meta = $this->_getMeta($namespace, $predicate))
    {
      $meta = new TaskMeta;
      $meta->task = get_class($this);
      $meta->namespace = $namespace;
      $meta->predicate = $predicate;
    }
    
    $meta->value = $value;
    $meta->save();
  }
  
  
  public function getMeta($namespace, $predicate)
  {
    if (!$meta = $this->_getMeta($namespace, $predicate))
    {
      throw new Exception(get_class($this) . " doesn't have meta value for " . $namespace . ":" . $predicate);
    }

    return $meta->value;
  }


  public function getAllMeta($namespace=null)
  {
    $ret = array();
    
    $q = LsDoctrineQuery::create()
      ->from('TaskMeta t')
      ->where('t.task = ?', get_class($this))
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);
    
    if ($namespace)
    {
      $q->addWhere('t.namespace = ?', $namespace);
    }
    
    foreach ($q->execute() as $meta)
    {
      if (isset($ret[$meta['namespace']]))
      {
        $ret[$meta['namespace']][$meta['predicate']] = $meta['value'];
      }
      else
      {
        $ret[$meta['namespace']] = array($meta['predicate'] => $meta['value']);
      }
    }  
    
    return $ret;
  }
  
  
  public function removeMeta($namespace, $predicate)
  {
    if (!$meta = $this->_getMeta($namespace, $predicate))
    {
      throw new Exception(get_class($this) . " doesn't have meta value for " . $namespace . ":" . $predicate);
    }
    
    $meta->delete();
  }


  public function removeAllMeta($namespace=null)
  {
    $q = LsDoctrineQuery::create()
      ->from('TaskMeta t')
      ->where('t.task = ?', get_class($this))
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);
    
    if ($namespace)
    {
      $q->addWhere('t.namespace = ?', $namespace);
    }
    
    $q->delete();
  }
  
  
  public function hasMeta($namespace, $predicate)
  {
    return $this->_getMeta($namespace, $predicate) ? true : false;
  }
  
  
  protected function _getMeta($namespace, $predicate)
  {
    return LsDoctrineQuery::create()
      ->from('TaskMeta t')
      ->where('t.task = ?', get_class($this))
      ->andWhere('t.namespace = ?', $namespace)
      ->andWhere('t.predicate = ?', $predicate)
      ->fetchOne();    
  }
}