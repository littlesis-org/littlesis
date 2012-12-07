<?php

class LsCache
{
  protected static $clearPatternPrefix = '/*/all/';
  

  static function generateCacheKey($internalUri, $hostName = '', $vary = '', $contextualPrefix = '')  
  {
    //hack to avoid infinite loop with sfViewCacheManager::generateCacheKey()
    sfConfig::set('sf_cache_namespace_callable', null);
    
    $cache = sfContext::getInstance()->getViewCacheManager();
    $key = $cache->generateCacheKey($internalUri, $hostName, $vary, $contextualPrefix);

    //end hack
    ProjectConfiguration::configureCache();
    
    //add scope to avoid namespace collisions between page, action, and fragment caches
    if (!strstr($internalUri, '_sf_cache_key='))
    {
      $keyParts = explode('/', substr($key, 1));
      array_splice($keyParts, 4, 0, array('_sf_cache_key', $cache->withLayout($internalUri) ? '_page' : '_action'));
      $key = '/' . implode('/', $keyParts);
    }

    return $key;
  }
  
  
  static function generateCachePatterns($module, $action, $params=array())
  {
    $pattern = self::$clearPatternPrefix . $module . '/' . $action . '/_sf_cache_key/*';
    
    if (count($params))
    {
      $paramStr = '';
      
      foreach ($params as $key => $value)
      {
        $paramStr .= sprintf('/%s/%s', $key, $value);
      }

      $pattern .= $paramStr;
    }
    
    return array($pattern, $pattern . '/*');
  }


  static function clearRecordCache(Doctrine_Record $r)
  {
    if (!$r->id)
    {
      throw new Exception("Can't clear cache for new record");
    }

    $supportedClasses = array(
      'Entity' => 'Entity', 
      'List' => 'List',
      'Relationship' => 'Relationship', 
      'sfGuardUser' => 'User'
    );

    $recordClass = get_class($r);    

    if (isset($supportedClasses[$recordClass]))
    {
      $func = 'clear' . $supportedClasses[$recordClass] . 'CacheById';
      call_user_func(array('self', $func), $r->id);
    }
  }


  static function getUserCachePatternsByName($name)
  {
    return array(self::$clearPatternPrefix . 'user/*/_sf_cache_key/*/name/' . $name);
  }


  static function clearUserCacheById($id)
  {
    $name = sfGuardUserTable::getPublicNameById($id);

    return self::clearUserCacheByName($name);
  }  
  
  
  static function clearUserCacheByName($name)
  {
    $patterns = self::getUserCachePatternsByName($name);
    
    self::clearCachePatterns($patterns);  
    
    return $patterns;
  }
  

  static function getEntityCachePatternsById($id, $action='*', $key='*')
  {
    return array(self::$clearPatternPrefix . 'entity/' . $action . '/_sf_cache_key/' . $key . '/id/' . $id . '/*');
  }


  static function clearEntityCacheById($id, $action='*', $key='*')
  {
    $patterns = self::getEntityCachePatternsById($id, $action, $key);
      
    self::clearCachePatterns($patterns);
    
    return $patterns;
  }
  

  static function getListCachePatternsById($id)
  {
    return array(self::$clearPatternPrefix . 'list/*/_sf_cache_key/*/id/' . $id . '/*');
  }

  
  static function clearListCacheById($id)
  {
    $patterns = self::getListCachePatternsById($id);
      
    self::clearCachePatterns($patterns);
    
    return $patterns;
  }


  static function getRelationshipCachePatternsById($id)
  {
    return array(self::$clearPatternPrefix . 'relationship/*/_sf_cache_key/*/id/' . $id);
  }


  static function clearRelationshipCacheById($id, $entity1Id=null, $entity2Id=null)
  {
    $patterns = self::getRelationshipCachePatternsById($id);
    
    self::clearCachePatterns($patterns);
    
    if (!$entity1Id || !$entity2Id)
    {
      $db = Doctrine_Manager::connection();
      $sql = 'SELECT entity1_id, entity2_id FROM relationship WHERE id = ?';
      $stmt = $db->execute($sql, array($id));
      list($entity1Id, $entity2Id) = $stmt->fetch(PDO::FETCH_NUM);      
    }
    
    $patterns = array_merge($patterns, self::clearEntityCacheById($entity1Id));
    $patterns = array_merge($patterns, self::clearEntityCacheById($entity2Id));
    
    return $patterns;
  }
  

  static function getGroupCachePatternsByName($name)
  {
    return array(
      self::$clearPatternPrefix . 'group/*/_sf_cache_key/*/name/' . $name,
      self::$clearPatternPrefix . 'group/*/_sf_cache_key/*/name/' . $name . '/*'
    );    
  }

  
  static function clearGroupCacheByName($name)
  {
    $patterns = self::getGroupCachePatternsByName($name);
    
    self::clearCachePatterns($patterns);
    
    return $patterns;
  }

    
  static function clearCachePatterns($patterns)
  {
    if (!sfConfig::get('sf_cache'))
    {
      return null;
    }
    
    $cache = sfContext::getInstance()->getViewCacheManager()->getCache();

    //remove keys directly, save patterns for bulk removal
    $ary = array();

    foreach ((array) $patterns as $pattern)
    {
      if (strpos($pattern, '*') !== false)
      {
        $ary[] = $pattern;
      }
      else
      {
        $cache->remove($pattern);
      }
    }

    //remove all patterns at once
    $cache->removePatterns($ary);
  }
}