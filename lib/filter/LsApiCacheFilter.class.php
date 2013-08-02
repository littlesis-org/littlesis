<?php

class LsApiCacheFilter extends sfFilter
{
  protected
    $cache        = null,
    $request      = null,
    $response     = null,
    $queryStr     = null;


  public function initialize($context, $parameters = array())
  {
    parent::initialize($context, $parameters);

    $this->request      = $context->getRequest();
    $this->response     = $context->getResponse();
  }


  static function getApiCache()
  {
    return sfContext::getInstance()->getViewCacheManager()->getCache();
  }

  protected function isMapModule()
  {
    return $this->request->getParameter("module") == "map";
  }


	public function execute($filterChain)
	{		
    //skip this if cache is disabled or if it's the map module
    if (!sfConfig::get('sf_cache') || $this->isMapModule())
    {
		  $filterChain->execute();
  		$this->afterExecution();
      return;
    }

    $this->cache = self::getApiCache();

    if (!$this->getCache())
    {
		  $filterChain->execute();
      $this->saveCache();
		}

		$this->afterExecution();
	}


  public function getCache()
  {
    $this->orderParams();    
    $cacheKey = $this->generateCacheKey();

    if ($data = $this->cache->get($cacheKey))
    {      
      $data = unserialize($data);
      
      foreach ($data['headers'] as $name => $value)
      {
        $this->response->setHttpHeader($name, $value);
      }
      
      $this->response->setStatusCode($data['status_code']);
      $this->response->setContentType($data['content_type']);      
      $this->response->setContent($data['content']);
      
      return true;
    }
    
    return false;
  }


  public function orderParams()
  {
    $params = $this->request->getParameterHolder()->getAll();  
    unset($params['module']);
    unset($params['action']);
    unset($params['_key']);
    
    $names = array_keys($params);
    sort($names);
    
    $this->queryStr = http_build_query($params);
  }
  
  
  public function generateCacheKey()
  {
    return $this->request->getPathInfo() . '?' . $this->queryStr;  
  }
  
  
  public function saveCache()
  {
    $data = array(    
      'headers' => $this->response->getHttpHeaders(),
      'status_code' => $this->response->getStatusCode(),
      'content_type' => $this->response->getContentType(),
      'content' => $this->response->getContent()
    );
    $data = serialize($data);
    $key = $this->generateCacheKey();    
    $timeout = sfConfig::get('sf_app_cache_timeout', 2592000);

    $this->cache->set($key, $data, $timeout);
  }
  
  
  public function afterExecution()
  {    
    //report execution time in header (not in response body, that will ruin the etag)
    $this->response->setHttpHeader('Ls-Execution-Time', LsApi::getResponseTime());


    // Etag support
    if (sfConfig::get('sf_etag'))
    {
      $etag = '"'.md5($this->response->getContent()).'"';
      $this->response->setHttpHeader('ETag', $etag);

      if ($this->request->getHttpHeader('IF_NONE_MATCH') == $etag)
      {
        $this->response->setStatusCode(304);
        $this->response->setHeaderOnly(true);

        if (sfConfig::get('sf_logging_enabled'))
        {
          $this->context->getEventDispatcher()->notify(new sfEvent($this, 'application.log', array('ETag matches If-None-Match (send 304)')));
        }
      }
    }


    // conditional GET support
    // never in debug mode
    if ($this->response->hasHttpHeader('Last-Modified') && !sfConfig::get('sf_debug'))
    {
      $last_modified = $this->response->getHttpHeader('Last-Modified');
      if ($this->request->getHttpHeader('IF_MODIFIED_SINCE') == $last_modified)
      {
        $this->response->setStatusCode(304);
        $this->response->setHeaderOnly(true);

        if (sfConfig::get('sf_logging_enabled'))
        {
          $this->context->getEventDispatcher()->notify(new sfEvent($this, 'application.log', array('Last-Modified matches If-Modified-Since (send 304)')));
        }
      }
    }
  }
}