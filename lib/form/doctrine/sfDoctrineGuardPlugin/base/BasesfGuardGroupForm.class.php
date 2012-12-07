<?php

/**
 * sfGuardGroup form base class.
 *
 * @package    form
 * @subpackage sf_guard_group
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BasesfGuardGroupForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                             => new sfWidgetFormInputHidden(),
      'name'                           => new sfWidgetFormInput(),
      'blurb'                          => new sfWidgetFormInput(),
      'description'                    => new sfWidgetFormInput(),
      'contest'                        => new sfWidgetFormInput(),
      'is_working'                     => new sfWidgetFormInputCheckbox(),
      'is_private'                     => new sfWidgetFormInputCheckbox(),
      'display_name'                   => new sfWidgetFormInput(),
      'home_network_id'                => new sfWidgetFormInput(),
      'created_at'                     => new sfWidgetFormDateTime(),
      'updated_at'                     => new sfWidgetFormDateTime(),
      'users_list'       => new sfWidgetFormDoctrineSelectMany(array('model' => 'sfGuardUser')),
      'permissions_list' => new sfWidgetFormDoctrineSelectMany(array('model' => 'sfGuardPermission')),
    ));

    $this->setValidators(array(
      'id'                             => new sfValidatorDoctrineChoice(array('model' => 'sfGuardGroup', 'column' => 'id', 'required' => false)),
      'name'                           => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'blurb'                          => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'description'                    => new sfValidatorString(array('max_length' => 1000, 'required' => false)),
      'contest'                        => new sfValidatorString(array('max_length' => 1000, 'required' => false)),
      'is_working'                     => new sfValidatorBoolean(),
      'is_private'                     => new sfValidatorBoolean(),
      'display_name'                   => new sfValidatorString(array('max_length' => 255)),
      'home_network_id'                => new sfValidatorInteger(),
      'created_at'                     => new sfValidatorDateTime(array('required' => false)),
      'updated_at'                     => new sfValidatorDateTime(array('required' => false)),
      'users_list'       => new sfValidatorDoctrineChoiceMany(array('model' => 'sfGuardUser', 'required' => false)),
      'permissions_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'sfGuardPermission', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_guard_group[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfGuardGroup';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['users_list']))
    {
      $values = array();
      foreach ($this->object->users as $obj)
      {
        $values[] = current($obj->identifier());
      }
      $this->object->clearRelated('users');
      $this->setDefault('users_list', $values);
    }

    if (isset($this->widgetSchema['permissions_list']))
    {
      $values = array();
      foreach ($this->object->permissions as $obj)
      {
        $values[] = current($obj->identifier());
      }
      $this->object->clearRelated('permissions');
      $this->setDefault('permissions_list', $values);
    }

  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $this->saveusersList($con);
    $this->savepermissionsList($con);
  }

  public function saveusersList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['users_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $q = Doctrine_Query::create()
          ->delete()
          ->from('sfGuardUserGroup r')
          ->where('r.group_id = ?', current($this->object->identifier()))
          ->execute();

    $values = $this->getValue('users_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new sfGuardUserGroup();
        $obj->group_id = current($this->object->identifier());
        $obj->user_id = $value;
        $obj->save();
      }
    }
  }

  public function savepermissionsList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['permissions_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $q = Doctrine_Query::create()
          ->delete()
          ->from('sfGuardGroupPermission r')
          ->where('r.group_id = ?', current($this->object->identifier()))
          ->execute();

    $values = $this->getValue('permissions_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new sfGuardGroupPermission();
        $obj->group_id = current($this->object->identifier());
        $obj->permission_id = $value;
        $obj->save();
      }
    }
  }

}