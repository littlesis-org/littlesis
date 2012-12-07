<?php
/*
 * This file is part of the sfDoctrinePlugin package.
 * (c) 2006-2007 Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base sfDoctrineRecord extends the base Doctrine_Record in Doctrine to provide some
 * symfony specific functionality to Doctrine_Records
 *
 * @package    sfDoctrinePlugin
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineRecord.class.php 12691 2008-11-06 17:24:54Z Jonathan.Wage $
 */
abstract class sfDoctrineRecord extends Doctrine_Record
{
  static protected
    $_initialized    = false,
    $_defaultCulture = 'en';

  /**
   * Custom Doctrine_Record constructor.
   * Used to initialize I18n to make sure the culture is set from symfony
   *
   * @return void
   */
  public function construct()
  {
    self::initializeI18n();

    if ($this->getTable()->hasRelation('Translation')) {
      $this->unshiftFilter(new sfDoctrineRecordI18nFilter());
    }
  }

  /**
   * Initialize I18n culture from symfony sfUser instance
   * Add event listener to change default culture whenever the user changes culture
   *
   * @return void
   */
  public static function initializeI18n()
  {
    if (!self::$_initialized)
    {
      if (!self::$_initialized && class_exists('sfProjectConfiguration', false))
      {
        $dispatcher = sfProjectConfiguration::getActive()->getEventDispatcher();
        $dispatcher->connect('user.change_culture', array('sfDoctrineRecord', 'listenToChangeCultureEvent'));
      }

      if (class_exists('sfContext', false) && sfContext::hasInstance() && $user = sfContext::getInstance()->getUser())
      {
        self::$_defaultCulture = $user->getCulture();
      }
      self::$_initialized = true;
    }
  }

  /**
   * Listens to the user.change_culture event.
   *
   * @param sfEvent An sfEvent instance
   */
  public static function listenToChangeCultureEvent(sfEvent $event)
  {
    self::$_defaultCulture = $event['culture'];
  }

  /**
   * Sets the default culture
   *
   * @param string $culture
   */
  static public function setDefaultCulture($culture)
  {
    self::$_defaultCulture = $culture;
  }

  /**
   * Return the default culture
   *
   * @return string the default culture
   */
  static public function getDefaultCulture()
  {
    self::initializeI18n();

    return self::$_defaultCulture;
  }

  /**
   * __toString
   *
   * @return string $string
   */
  public function __toString()
  {
    // if the current object doesn't exist we return nothing
    if (!$this->exists())
    {
      return '-';
    }

    $guesses = array('name',
                     'title',
                     'description',
                     'subject',
                     'keywords',
                     'id');

    // we try to guess a column which would give a good description of the object
    foreach ($guesses as $descriptionColumn)
    {
      try
      {
        return (string) $this->get($descriptionColumn);
      } catch (Exception $e) {}
    }

    return sprintf('No description for object of class "%s"', $this->getTable()->getComponentName());
  }

  /**
   * Get the primary key of a Doctrine_Record.
   * This a proxy method to Doctrine_Record::identifier() for Propel BC
   *
   * @return mixed $identifier Array for composite primary keys and string for single primary key
   */
  public function getPrimaryKey()
  {
    return $this->identifier();
  }

  /**
   * Get a record attribute. Allows overriding Doctrine record accessors with Propel style functions
   *
   * @param string $name 
   * @param string $load 
   * @return void
   */
  public function get($name, $load = true)
  {
    $getter = 'get' . Doctrine_Inflector::classify($name);

    if (method_exists($this, $getter))
    {
      return $this->$getter($load);
    }
    return parent::get($name, $load);
  }

  /**
   * Set a record attribute. Allows overriding Doctrine record accessors with Propel style functions
   *
   * @param string $name 
   * @param string $value 
   * @param string $load 
   * @return void
   */
  public function set($name, $value, $load = true)
  {
    $setter = 'set' . Doctrine_Inflector::classify($name);

    if (method_exists($this, $setter))
    {
      return $this->$setter($value, $load);
    }
    return parent::set($name, $value, $load);
  }

  /**
   * This magic __call is used to provide propel style accessors to Doctrine models
   *
   * @param string $m 
   * @param string $a 
   * @return void
   */
  public function __call($m, $a)
  {
    try {
      $verb = substr($m, 0, 3);

      if ($verb == 'set' || $verb == 'get')
      {
        $camelColumn = substr($m, 3);

        // If is a relation
        if (in_array($camelColumn, array_keys($this->getTable()->getRelations())))
        {
          $column = $camelColumn;
        } else {
          $column = sfInflector::underscore($camelColumn);
        }

        if ($verb == 'get')
        {
          return $this->get($column);
        } else {
          return $this->set($column, $a[0]);
        }
      } else {
        return parent::__call($m, $a);
      }
    } catch(Exception $e) {
      return parent::__call($m, $a);
    }
  }


  public function getResultValue($fieldName)
  {
    return isset($this->_values[$fieldName]) ? $this->_values[$fieldName] : null;
  }
}