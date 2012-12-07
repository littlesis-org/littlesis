<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony
 * @subpackage form
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfFormDoctrine.class.php 7845 2008-03-12 22:36:14Z fabien $
 */

/**
 * sfFormDoctrine is the base class for forms based on Doctrine objects.
 *
 * @package    symfony
 * @subpackage form
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfFormDoctrine.class.php 7845 2008-03-12 22:36:14Z fabien $
 */
abstract class sfFormDoctrine extends sfForm
{
  protected
    $cultures = array(),
    $object   = null;

  /**
   * Constructor.
   *
   * @param BaseObject A Doctrine object used to initialize default values
   * @param array      An array of options
   * @param string     A CSRF secret (false to disable CSRF protection, null to use the global CSRF secret)
   *
   * @see sfForm
   */
  public function __construct($object = null, $options = array(), $CSRFSecret = null)
  {
    $class = $this->getModelName();
    if (!$object)
    {
      $this->object = new $class();
    }
    else
    {
      if (!$object instanceof $class)
      {
        throw new sfException(sprintf('The "%s" form only accepts a "%s" object.', get_class($this), $class));
      }

      $this->object = $object;
    }

    parent::__construct(array(), $options, $CSRFSecret);

    $this->updateDefaultsFromObject();
  }

  /**
   * Returns the default connection for the current model.
   *
   * @return Connection A database connection
   */
  public function getConnection()
  {
    return Doctrine_Manager::getInstance()->getConnectionForComponent($this->getModelName());
  }

  /**
   * Returns the current model name.
   */
  abstract public function getModelName();

  /**
   * Returns true if the current form embeds a new object.
   *
   * @return Boolean true if the current form embeds a new object, false otherwise
   */
  public function isNew()
  {
    return !$this->object->exists();
  }

  /**
   * Embeds i18n objects into the current form.
   *
   * @param array An array of cultures
   * @param string The format to use for widget name
   * @param string A HTML decorator for the embedded form
   */
  public function embedI18n($cultures, $nameFormat = null, $decorator = null)
  {
    if (!$this->isI18n())
    {
      throw new sfException(sprintf('The model "%s" is not internationalized.', $this->getModelName()));
    }

    if ($this->isI18n() && !isset($this->Translation))
    {
      // lazy load translations
      $this->getObject()->loadReference('Translation');
    }

    $this->cultures = $cultures;
    $class = $this->getI18nFormClass();
    $i18n = new $class();
    foreach ($cultures as $culture)
    {
      $this->embedForm($culture, $i18n, $nameFormat, $decorator);
    }
  }

  /**
   * Returns the current object for this form.
   *
   * @return BaseObject The current object.
   */
  public function getObject()
  {
    return $this->object;
  }

  /**
   * Binds the current form and save the to the database in one step.
   *
   * @param  array      An array of tainted values to use to bind the form
   * @param  array      An array of uploaded files (in the $_FILES or $_GET format)
   * @param  Connection An optional Doctrine Connection object
   *
   * @return Boolean    true if the form is valid, false otherwise
   */
  public function bindAndSave($taintedValues, $taintedFiles = null, $con = null)
  {
    $this->bind($taintedValues, $taintedFiles);
    if ($this->isValid())
    {
      $this->save($con);

      return true;
    }

    return false;
  }

  /**
   * Saves the current object to the database.
   *
   * The object saving is done in a transaction and handled by the doSave() method.
   *
   * If the form is not valid, it throws an sfValidatorError.
   *
   * @param Connection An optional Connection object
   *
   * @return BaseObject The current saved object
   *
   * @see doSave()
   */
  public function save($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    try
    {
      $con->beginTransaction();

      $this->doSave($con);

      $con->commit();
    }
    catch (Exception $e)
    {
      $con->rollback();

      throw $e;
    }

    return $this->object;
  }

  /**
   * Updates the values of the object with the cleaned up values.
   *
   * @return BaseObject The current updated object
   */
  public function updateObject()
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    $values = $this->getValues();

    // remove special columns that are updated automatically
    unset($values['id'], $values['updated_at'], $values['updated_on'], $values['created_at'], $values['created_on']);

    // Move translations to the Translation key so that it will work with Doctrine_Record::fromArray()
    foreach ($this->cultures as $culture)
    {
      $translation = $values[$culture];
      $translation['lang'] = $culture;
      unset($translation['id']);
      $values['Translation'][$culture] = $translation;
      unset($values[$culture]);
    }
    $this->object->fromArray($values);

    return $this->object;
  }

  /**
   * Returns true if the current form has some associated i18n objects.
   *
   * @return Boolean true if the current form has some associated i18n objects, false otherwise
   */
  public function isI18n()
  {
    return $this->getObject()->getTable()->hasTemplate('Doctrine_Template_I18n');
  }

  /**
   * Returns the name of the i18n form class.
   *
   * @return string The name of the i18n form class
   */
  public function getI18nFormClass()
  {
    return $this->getI18nModelName() . 'Form';
  }

  /**
   * Returns the name of the i18n model.
   *
   * @return string The name of the i18n model
   */
  public function getI18nModelName()
  {
    return $this->getObject()->getTable()->getTemplate('Doctrine_Template_I18n')->getI18n()->getOption('className');
  }

  /**
   * Updates and saves the current object.
   *
   * If you want to add some logic before saving or save other associated objects,
   * this is the method to override.
   *
   * @param Connection An optional Connection object
   */
  protected function doSave($con = null)
  {
    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $this->updateObject();

    $this->object->save($con);
  }

  /**
   * Updates the default values of the form with the current values of the current object.
   */
  protected function updateDefaultsFromObject()
  {
    if ($this->isI18n() && !isset($this->Translation))
    {
      // lazy load translations
      $this->getObject()->loadReference('Translation');
    }

    // update defaults for the main object
    if ($this->isNew())
    {
      $this->setDefaults(array_merge($this->object->toArray(true), $this->getDefaults()));
    }
    else
    {
      $this->setDefaults(array_merge($this->getDefaults(), $this->object->toArray(true)));
    }

    if ($this->isI18n())
    {
      $defaults = $this->getDefaults();
      $translations = $defaults['Translation'];
      unset($defaults['Translation']);
      $this->setDefaults(array_merge($defaults, $translations));
    }
  }
}