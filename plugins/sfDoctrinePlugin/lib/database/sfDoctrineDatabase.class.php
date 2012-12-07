<?php
/*
 * This file is part of the sfDoctrinePlugin package.
 * (c) 2006-2007 Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDoctrineDatabase
 *
 * Provides connectivity for the Doctrine.
 *
 * @package    sfDoctrinePlugin
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineDatabase.class.php 8939 2008-05-14 01:54:14Z Jonathan.Wage $
 */
class sfDoctrineDatabase extends sfDatabase
{
  /**
   * @var object Doctrine_Connection
   */
  protected $doctrineConnection = null;

  /**
   * initialize
   *
   * @param array $parameters
   * @return void
   */
  public function initialize($parameters = array())
  {
    if (!$parameters)
    {
      return;
    }

    parent::initialize($parameters);

    // Load default database connection to load if specified
    if ($defaultDatabase = sfConfig::get('sf_default_database'))
    {
      if ($parameters['name'] != $defaultDatabase)
      {
        return;
      }
    }

    // Load the doctrine configuration
    require(sfProjectConfiguration::getActive()->getConfigCache()->checkConfig('config/doctrine.yml'));

    // Load config in to parameter
    $this->setParameter('config', $config);

    // Load schemas information for connection binding
    if ($schemas = sfProjectConfiguration::getActive()->getConfigCache()->checkConfig('config/schemas.yml', true))
    {
      require_once($schemas);
    }

    $this->loadConnections();

    $this->loadAttributes($parameters['name']);
    $this->loadListeners();
  }

  /**
   * loadConnections
   *
   * Create and load the Doctrine connections
   *
   * @return void
   * @author Jonathan H. Wage
   */
  protected function loadConnections()
  {
    // Get Connection method
    $method = $this->getParameter('method', 'dsn');

    // get parameters
    switch ($method)
    {
      case 'dsn':
        $dsn = $this->getParameter('dsn');

        if ($dsn == null)
        {
          // missing required dsn parameter
          $error = 'Database configuration specifies method "dsn", but is missing dsn parameter';

          throw new sfDatabaseException($error);
        }

        break;
    }

    // Make sure we pass non-PEAR style DSNs as an array
    if ( !strpos($dsn, '://'))
    {
      $dsn = array($dsn, $this->getParameter('username'), $this->getParameter('password'));
    }

    // Make the Doctrine connection for $dsn and $name
    $this->doctrineConnection = Doctrine_Manager::connection($dsn, $this->getParameter('name'));
  }

  /**
   * Loads and sets all the Doctrine attributes that we loaded from doctrine.yml
   *
   * @return void
   */
  protected function loadAttributes($name)
  {
    $config = $this->getParameter('config');

    $attributes = $config['global_attributes'];

    $this->setAttributes($attributes, true);

    $connectionAttributesName = $name.'_attributes';
    if (isset($config[$connectionAttributesName]))
    {
      $attributes = $config[$connectionAttributesName];

      $this->setAttributes($attributes);
    }
  }

  /**
   * Set the passed attributes on the Doctrine_Manager or Doctrine_Connection
   *
   * @param  array   $attributes
   * @param  boolean $global
   * @return void
   */
  protected function setAttributes($attributes, $global = false)
  {
    foreach($attributes as $k => $v)
    {
      if ($global)
      {
        Doctrine_Manager::getInstance()->setAttribute(constant('Doctrine::ATTR_'.strtoupper($k)), $v);
      } else {
        $this->doctrineConnection->setAttribute(constant('Doctrine::ATTR_'.strtoupper($k)), $v);
      }
    }
  }

  /**
   * Load all the listeners
   *
   * @return void
   */
  protected function loadListeners()
  {
    // Get encoding
    $encoding = $this->getParameter('encoding', 'UTF8');

    // Add the default sfDoctrineConnectionListener
    $eventListener = new sfDoctrineConnectionListener($this->doctrineConnection, $encoding);
    $this->doctrineConnection->addListener($eventListener);

    // Load Query Logger Listener
    if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $this->doctrineConnection->addListener(new sfDoctrineQueryLogger());
    }

    $config = $this->getParameter('config');

    // Add global listeners
    $this->setListeners($config['global_listeners']);

    // Add record listeners
    $this->setListeners($config['global_record_listeners'], 'addRecordListener');
  }

  /**
   * Set the listeners to the connection
   *
   * @param array $listeners
   * @return void
   */
  protected function setListeners($listeners, $type = 'addListener')
  {
    foreach ($listeners as $listener)
    {
      $this->doctrineConnection->$type(new $listener());
    }
  }

  /**
   * Initializes the connection and sets it to object
   *
   * @return void
   */
  public function connect()
  {
    $this->connection = $this->doctrineConnection->getDbh();
  }

  /**
   * Execute the shutdown procedure.
   *
   * @return void
   */
  public function shutdown()
  {
    if ($this->connection !== null)
    {
      $this->connection = null;
    }
  }
}