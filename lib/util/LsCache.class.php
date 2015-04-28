<?php

class LsCache
{
  protected static $clearPatternPrefix = '/*/all/';


  static function isCacheEnabled()
  {
    return sfConfig::get('sf_cache');
  }
  

  static function generateCacheKey($internalUri, $hostName = '', $vary = '', $contextualPrefix = '')  
  {
    //hack to avoid infinite loop with sfViewCacheManager::generateCacheKey()
    sfConfig::set('sf_cache_namespace_callable', null);
    
    $cache = sfContext::getInstance()->getViewCacheManager();

    if (!$cache)
    {
      throw new Exception('no cache!');
    }

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
    if (!self::isCacheEnabled())
    {
      return null;
    }
    
    if (!$r->id)
    {
      throw new Exception("Can't clear cache for new record");
    }

    $supportedClasses = array(
      'Entity' => 'Entity', 
      'List' => 'List',
      'Relationship' => 'Relationship', 
      'sfGuardUser' => 'User',
      'NetworkMap' => 'NetworkMap'
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
    if (!self::isCacheEnabled())
    {
      return null;
    }

    $name = sfGuardUserTable::getPublicNameById($id);

    return self::clearUserCacheByName($name);
  }  
  
  
  static function clearUserCacheByName($name)
  {
    if (!self::isCacheEnabled())
    {
      return null;
    }

    $patterns = self::getUserCachePatternsByName($name);
    
    self::clearCachePatterns($patterns);  
    
    return $patterns;
  }
  

  static function getEntityCachePatternsById($id, $action='*', $key='*')
  {
    $entity = Doctrine::getTable('Entity')->find($id);
    $actions = array_merge(array('view'), LsCacheFilter::cachedActionsByModule('entity'));
    $partials = array('_page', '_action', 'leftcol_profileimage', 'leftcol_references', 'leftcol_stats', 'leftcol_lists', 'relationship_tabs_content', 'similarEntities', 'watchers');
    $keys = array();

    foreach ($actions as $action)
    {
      foreach ($partials as $partial)
      {
        $url = 'entity/' . $action . '?id=' . $id . '&slug=' . LsSlug::convertNameToSlug($entity['name']) . '&_sf_cache_key=' . $partial;
        $keys[] = self::generateCacheKey($url);
      }
    }

    return $keys;
  }


  static function clearEntityCacheById($id, $action='*', $key='*')
  {
    if (!self::isCacheEnabled())
    {
      return null;
    }

    $patterns = self::getEntityCachePatternsById($id, $action, $key);
      
    self::clearCachePatterns($patterns);
    
    return $patterns;
  }
  

  static function getListCachePatternsById($id)
  {
    $list = Doctrine::getTable('LsList')->find($id);
    $actions = array_merge(array('view'), LsCacheFilter::cachedActionsByModule('list'));
    $partials = array('_page', '_action');
    $keys = array();

    foreach ($actions as $action)
    {
      foreach ($partials as $partial)
      {
        $url = 'list/' . $action . '?id=' . $id . '&slug=' . LsSlug::convertNameToSlug($list['name']) . '&_sf_cache_key=' . $partial;
        $keys[] = self::generateCacheKey($url);
      }
    }

    return $keys;
  }

  
  static function clearListCacheById($id)
  {
    if (!self::isCacheEnabled())
    {
      return null;
    }

    $patterns = self::getListCachePatternsById($id);
      
    self::clearCachePatterns($patterns);
    
    return $patterns;
  }


  static function getRelationshipCachePatternsById($id)
  {
    $actions = array_merge(array('view'), LsCacheFilter::cachedActionsByModule('relationship'));
    $partials = array('_page', '_action', 'main');
    $keys = array();

    foreach ($actions as $action)
    {
      foreach ($partials as $partial)
      {
        $url = 'relationship/' . $action . '?id=' . $id . '&_sf_cache_key=' . $partial;
        $keys[] = self::generateCacheKey($url);
      }
    }

    return $keys;
  }


  static function clearRelationshipCacheById($id, $entity1Id=null, $entity2Id=null)
  {
    if (!self::isCacheEnabled())
    {
      return null;
    }

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


  static function getNetworkMapCachePatternsById($id)
  {
    return array(self::$clearPatternPrefix . 'map/*/_sf_cache_key/*/id/' . $id);
  }
  

  static function clearNetworkMapCacheById($id)
  {
    if (!self::isCacheEnabled())
    {
      return null;
    }

    $patterns = self::getNetworkMapCachePatternsById($id);

    self::clearCachePatterns($patterns);
    
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
    if (!self::isCacheEnabled())
    {
      return null;
    }

    $patterns = self::getGroupCachePatternsByName($name);
    
    self::clearCachePatterns($patterns);
    
    return $patterns;
  }

    
  static function clearCachePatterns($patterns)
  {
    if (!self::isCacheEnabled())
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