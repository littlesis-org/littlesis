<?php

require sfConfig::get('sf_lib_dir') . '/vendor/Predis.php';

/**
 * Cache class that stores cached content in Redis server.
 *
 * @package    LsRedisPlugin
 * @subpackage cache
 * @author     Benjamin Viellard <bicou@bicou.com>
 * @version    SVN: $Id$
 */
class LsRedisCache extends sfCache
{
  /**
   * Predis client instance
   *
   * @var Predis_Client
   * @access protected
   */
  protected $redis = null;

  /**
   * Available options :
   *
   * * connection:   Configuration key to connection parameters
   *
   * @see sfCache
   */
  public function initialize($options = array())
  {
    parent::initialize($options);

    $this->redis = new Predis\Client();
  }

  /**
   * @see sfCache
   */
  public function getBackend()
  {
    return $this->redis;
  }

 /**
  * @see sfCache
  */
  public function get($key, $default = null)
  {
    $value = $this->redis->get($this->getKey($key));

    return null === $value ? $default : $value;
  }

  /**
   * @see sfCache
   */
  public function has($key)
  {
    return $this->redis->exists($this->getKey($key));
  }

  /**
   * @see sfCache
   */
  public function set($key, $data, $lifetime = null)
  {
    $lifetime = $this->getLifetime($lifetime);

    if ($lifetime < 1)
    {
      $response = $this->remove($key);
    }
    else
    {
      $pkey = $this->getKey($key);
      $mkey = $this->getKey($key, '_lastmodified');
      $pipe = $this->redis->pipeline();
      $pipe->mset(array($pkey => $data, $mkey => $_SERVER['REQUEST_TIME']));
      $pipe->expire($pkey, $lifetime);
      $pipe->expire($mkey, $lifetime);
      $response = $pipe->execute();
    }

    return $response;
  }

  /**
   * @see sfCache
   */
  public function remove($key)
  {
    return $this->redis->del($this->getKey($key), $this->getKey($key, '_lastmodified'));
  }

  /**
   * We manually remove keys as the redis glob style * == sfCache ** style
   *
   * @see sfCache
   */
  public function removePattern($pattern)
  {
    $pattern = $this->getKey($pattern);
    $regexp  = self::patternToRegexp($pattern);
    foreach ($this->redis->keys($pattern) as $key)
    {
      if (preg_match($regexp, $key))
      {
        $this->remove(substr($key, strlen($this->getOption('prefix'))));
      }
    }
  }

  /* 
   * Removes multiple patterns in one shot so that $this->getCacheInfo() doesn't have to be 
   * called multiple times
   */
  public function removePatterns(Array $patterns)
  {
    if (!count($patterns))
    {
      return;
    }

    $regexps = array();

    foreach ($patterns as $pattern)
    {
      $this->removePattern($pattern);
    }
  }

  /**
   * @see sfCache
   */
  public function clean($mode = sfCache::ALL)
  {
    if (sfCache::ALL === $mode)
    {
      $this->removePattern('**');
    }
  }

  /**
   * @see sfCache
   */
  public function getTimeout($key)
  {
    return $_SERVER['REQUEST_TIME'] + $this->redis->ttl($this->getKey($key));
  }

  /**
   * @see sfCache
   */
  public function getLastModified($key)
  {
    return $this->redis->get($this->getKey($key, '_lastmodified'));
  }

  /**
   * Optimized getMany with Redis mget method
   *
   * @param array $keys
   * @access public
   * @return array
   */
  public function getMany($keys)
  {
    $cache_keys = array_map(array($this, 'getKey'), $keys);

    return array_combine($keys, $this->redis->getMultiple($cache_keys));
  }

  /**
   * Checks if a key is expired or not
   *
   * @param string $key
   * @access public
   * @return void
   */
  public function isExpired($key)
  {
    return $_SERVER['REQUEST_TIME'] >= $this->getTimeout($key);
  }

  /**
   * Apply prefix and suffix to a value
   *
   * Usefull to be mapped on an array. Faster than foreach
   *
   * @param string $name
   * @param string $suffix
   * @access protected
   * @return string
   */
  protected function getKey($name, $suffix = null)
  {
    $key = $this->getOption('prefix').$name;

    if ($suffix !== null)
    {
      $key .= self::SEPARATOR.$suffix;
    }

    return $key;
  }
}

