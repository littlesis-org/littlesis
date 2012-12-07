<?php

function cache($name, $lifeTime = 86400)
{
  if (!sfConfig::get('sf_cache'))
  {
    return null;
  }

  $cache = sfContext::getInstance()->getViewCacheManager();
  $viewCache = new LsViewCacheManager($cache);

  if (sfConfig::get('symfony.cache.started'))
  {
    throw new sfCacheException('Cache already started.');
  }

  $data = $viewCache->start($name, $lifeTime);

  if (is_null($data))
  {
    sfConfig::set('symfony.cache.started', true);
    sfConfig::set('symfony.cache.current_name', $name);

    return false;
  }
  else
  {
    echo $data;

    return true;
  }
}

function cache_save()
{
  if (!sfConfig::get('sf_cache'))
  {
    return null;
  }

  if (!sfConfig::get('symfony.cache.started'))
  {
    throw new sfCacheException('Cache not started.');
  }

  $cache = sfContext::getInstance()->getViewCacheManager();
  $viewCache = new LsViewCacheManager($cache);

  $data = $viewCache->stop(sfConfig::get('symfony.cache.current_name', ''));

  sfConfig::set('symfony.cache.started', false);
  sfConfig::set('symfony.cache.current_name', null);

  echo $data;
}
