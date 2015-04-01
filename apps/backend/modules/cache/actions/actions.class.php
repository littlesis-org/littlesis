<?php

class cacheActions extends sfActions
{
  public function executeKeys($request)
  {
    $backendConfig = $this->getContext()->getConfiguration();
    $env = $backendConfig->getEnvironment();
    $app = $request->getParameter('app', 'frontend');
    $frontendConfig = ProjectConfiguration::getApplicationConfiguration($app, $env, true); 
    $cache = sfContext::createInstance($frontendConfig)->getViewCacheManager()->getCache();

    if ($cache instanceOf sfMemcacheCache)
    {
      $this->using_memcache = true;
      $this->keys = $cache->getCacheInfo(); 

      if (!is_array($this->keys))
      {
        $this->keys = array();
      }
      
      if ($cache instanceOf LsMemcacheCache)
      {
        $metakeys = $cache->getMetaKeys();
        $this->metakeys = is_array($metakeys) ? $metakeys : array();
      }
    }
    else
    {
      $this->using_memcache = false;
    }
    
    sfContext::switchTo('backend');
  }


  public function executeRemove($request)
  {
    $this->patterns = array();

    if ($request->isMethod('post'))
    {
      sfContext::switchTo('frontend');
      $frontendContext = sfContext::getInstance();

      if ($page = $request->getParameter('page'))
      {  
        $routing = $frontendContext->getRouting();
                
        if ($route = $routing->findRoute($page))
        {
          $params = $route['parameters'];
    
          $module = $params['module'];
          $action = $params['action'];
          unset($params['module']);
          unset($params['action']);
          unset($params['sf_culture']);
          
          $this->patterns = LsCache::generateCachePatterns($module, $action, $params);

          LsCache::clearCachePatterns($this->patterns);
        }
      }
        
      if ($entityId = $request->getParameter('entity_id'))
      {
        $this->patterns = array_merge($this->patterns, LsCache::clearEntityCacheById($entityId));
      }

      if ($relationshipId = $request->getParameter('relationship_id'))
      {
        $this->patterns = array_merge($this->patterns, LsCache::clearRelationshipCacheById($relationshipId));
      }
      
      if ($listId = $request->getParameter('list_id'))
      {
        $this->patterns = array_merge($this->patterns, LsCache::clearListCacheById($listId));        
      }

      if ($userName = $request->getParameter('username'))
      {
        $this->patterns = array_merge($this->patterns, LsCache::clearUserCacheByName($userName));        
      }

      if ($groupName = $request->getParameter('groupname'))
      {
        $this->patterns = array_merge($this->patterns, LsCache::clearGroupCacheByName($groupName));        
      }

      sfContext::switchTo('backend');
    }
  }
}