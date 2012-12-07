<?php

class LsViewCacheManager extends sfViewCacheManager
{
  protected $viewCache = null;
  
  
  public function __construct(sfViewCacheManager $viewCache)
  {
    $this->viewCache = $viewCache;
  }


  protected function getCacheConfig($internalUri, $key, $defaultValue = null)
  {
    list($route_name, $params) = $this->viewCache->controller->convertUrlStringToParameters($internalUri);

    //need this to distinguish between pages and fragments
    if ($params['_sf_cache_key'])
    {
      $params['action'] .= '.' . $params['_sf_cache_key'];
    }

    $value = $defaultValue;
    if (isset($this->viewCache->cacheConfig[$params['module']][$params['action']][$key]))
    {
      $value = $this->viewCache->cacheConfig[$params['module']][$params['action']][$key];
    }
    else if (isset($this->viewCache->cacheConfig[$params['module']]['DEFAULT'][$key]))
    {
      $value = $this->viewCache->cacheConfig[$params['module']]['DEFAULT'][$key];
    }

    return $value;
  }


  public function isCacheable($internalUri)
  {
    if (count($_GET) || count($_POST))
    {
      return false;
    }

    list($route_name, $params) = $this->viewCache->controller->convertUrlStringToParameters($internalUri);

    //need this to distinguish between pages and fragments
    if ($params['_sf_cache_key'])
    {
      $params['action'] .= '.' . $params['_sf_cache_key'];
    }

    if (isset($this->viewCache->cacheConfig[$params['module']][$params['action']]))
    {
      return ($this->viewCache->cacheConfig[$params['module']][$params['action']]['lifeTime'] > 0);
    }
    else if (isset($this->viewCache->cacheConfig[$params['module']]['DEFAULT']))
    {
      return ($this->viewCache->cacheConfig[$params['module']]['DEFAULT']['lifeTime'] > 0);
    }

    return false;
  }


  public function start($name, $lifeTime, $clientLifeTime = null, $vary = array())
  {
    $internalUri = $this->viewCache->routing->getCurrentInternalUri();

    if (!$clientLifeTime)
    {
      $clientLifeTime = $lifeTime;
    }

    // add cache config to cache manager
    list($route_name, $params) = $this->viewCache->controller->convertUrlStringToParameters($internalUri);
    $this->viewCache->addCache($params['module'], $params['action'] . '.' . $name, array('withLayout' => false, 'lifeTime' => $lifeTime, 'clientLifeTime' => $clientLifeTime, 'vary' => $vary));

    // get data from cache if available
    $data = $this->get($internalUri.(strpos($internalUri, '?') ? '&' : '?').'_sf_cache_key='.$name);
    if ($data !== null)
    {
      return $data;
    }
    else
    {
      ob_start();
      ob_implicit_flush(0);

      return null;
    }
  }  


  public function stop($name)
  {
    $data = ob_get_clean();

    // save content to cache
    $internalUri = $this->viewCache->routing->getCurrentInternalUri();
    try
    {
      $this->set($data, $internalUri.(strpos($internalUri, '?') ? '&' : '?').'_sf_cache_key='.$name);
    }
    catch (Exception $e)
    {
    }

    return $data;
  }
  
  
  public function get($internalUri)
  {
    // no cache or no cache set for this action
    if (!$this->isCacheable($internalUri) || $this->viewCache->ignore())
    {
      return null;
    }

    $retval = $this->viewCache->cache->get($this->viewCache->generateCacheKey($internalUri));

    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->viewCache->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Cache for "%s" %s', $internalUri, $retval !== null ? 'exists' : 'does not exist'))));
    }

    return $retval;
  }


  public function set($data, $internalUri)
  {
    if (!$this->isCacheable($internalUri))
    {
      return false;
    }

    try
    {
      $ret = $this->viewCache->cache->set($this->viewCache->generateCacheKey($internalUri), $data, $this->getCacheConfig($internalUri, 'lifeTime', 0));
    }
    catch (Exception $e)
    {
      return false;
    }

    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->viewCache->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Save cache for "%s"', $internalUri))));
    }

    return true;
  }
}