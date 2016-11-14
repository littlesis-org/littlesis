<?php

class LsMemcacheCache extends sfMemcacheCache
{
  /**
   * Adds a key to our array of keys, so that we can use removePattern
   */

  public function initialize()
  {
     $options =  array(
          "host" => "memcached"
      )
      parent::initialize($options);
  }
    
  protected function setCacheInfo($key)
  {
    //prepend key with prefix
    $key = $this->getOption('prefix') . $key;

    //get our array of all meta-keys
    $metaKeys = $this->getMetaKeys();
    
    //create the array if it doesn't exist
    if (!is_array($metaKeys))
    {
      $metaKeys = array('_metadata0');
      $this->setMetaKeys($metaKeys);
    }
    
    //by convention, the last meta-key is the one that isn't full
    $currentMetaKey = $metaKeys[count($metaKeys) - 1];

    //fetch non-full key array
    $existingKeys = $this->memcache->get($this->getOption('prefix') . $currentMetaKey);
    $keys = $existingKeys;

    //if it doesn't exist, create it
    if (!is_array($keys))
    {
      $keys = array();
    }

    //add new key to this non-full array
    $keys[] = $key;

    //if saving the new array fails, we assume it's because it's full, so we create a new array
    if (!$this->memcache->set($this->getOption('prefix') . $currentMetaKey, $keys, 0))
    {
      //try again for good measure
      if (!$this->memcache->set($this->getOption('prefix') . $currentMetaKey, $keys, 0))
      {
        //new array too big! reset value to old array and continue...
        $this->memcache->set($this->getOption('prefix') . $currentMetaKey, $existingKeys, 0);

        //generate new meta-key based on previous one
        $newMetaKey = '_metadata' . (substr($currentMetaKey, 9) + 1);
  
        //add this to our array of meta-keys
        $metaKeys[] = $newMetaKey;
        $this->setMetaKeys($metaKeys);
        
        //save the new key to this new array
        $this->memcache->set($this->getOption('prefix') . $newMetaKey, array($key), 0);
      }
    }
  }


  /**
   * Gets our array of keys
   */
  public function getCacheInfo()
  {
    $allKeys = array();

    //get list of meta-keys
    $metaKeys = $this->getMetaKeys();

    if (!is_array($metaKeys))
    {
      return array();
    }
    
    foreach ($metaKeys as $metaKey)
    {
      $keys = $this->memcache->get($this->getOption('prefix') . $metaKey);

      if (is_array($keys))
      {
        $allKeys = array_merge($allKeys, $keys);
      }
    }

    return $allKeys;
  }

  
  public function getMetaKeys()
  {
    return $this->memcache->get($this->getOption('prefix') . '_metakeys');  
  }

  
  public function setMetaKeys(Array $metaKeys)
  {
    return $this->memcache->set($this->getOption('prefix') . '_metakeys', $metaKeys, 0);
  }

  
  /* 
   * Removes multiple patterns in one shot so that $this->getCacheInfo() doesn't have to be 
   * called multiple times
   */
  public function removePatterns(Array $patterns)
  {
    if (!$this->getOption('storeCacheInfo', false))
    {
      throw new sfCacheException('To use the "removePattern" method, you must set the "storeCacheInfo" option to "true".');
    }

    if (!count($patterns))
    {
      return;
    }

    $regexps = array();

    foreach ($patterns as $pattern)
    {
      $regexps[] = self::patternToRegexp($this->getOption('prefix').$pattern);
    }

    foreach ($this->getCacheInfo() as $key)
    {
      foreach ($regexps as $regexp)
      {
        if (preg_match($regexp, $key))
        {
          $this->memcache->delete($key);
          break;
        }
      }
    }
  }

}