<?php

/**
 * sfGuardPermission form base class.
 *
 * @package    form
 * @subpackage sf_guard_permission
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BasesfGuardPermissionForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                             => new sfWidgetFormInputHidden(),
      'name'                           => new sfWidgetFormInput(),
      'description'                    => new sfWidgetFormInput(),
      'created_at'                     => new sfWidgetFormDateTime(),
      'updated_at'                     => new sfWidgetFormDateTime(),
      'groups_list' => new sfWidgetFormDoctrineSelectMany(array('model' => 'sfGuardGroup')),
      'users_list'  => new sfWidgetFormDoctrineSelectMany(array('model' => 'sfGuardUser')),
    ));

    $this->setValidators(array(
      'id'                             => new sfValidatorDoctrineChoice(array('model' => 'sfGuardPermission', 'column' => 'id', 'required' => false)),
      'name'                           => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'description'                    => new sfValidatorString(array('max_length' => 1000, 'required' => false)),
      'created_at'                     => new sfValidatorDateTime(array('required' => false)),
      'updated_at'                     => new sfValidatorDateTime(array('required' => false)),
      'groups_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'sfGuardGroup', 'required' => false)),
      'users_list'  => new sfValidatorDoctrineChoiceMany(array('model' => 'sfGuardUser', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_guard_permission[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfGuardPermission';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['groups_list']))
    {
      $values = array();
      foreach ($this->object->Groups as $obj)
      {
        $values[] = current($obj->identifier());
      }
      $this->object->clearRelated('Groups');
      $this->setDefault('groups_list', $values);
    }

    if (isset($this->widgetSchema['users_list']))
    {
      $values = array();
      foreach ($this->object->Users as $obj)
      {
        $values[] = current($obj->identifier());
      }
      $this->object->clearRelated('Users');
      $this->setDefault('users_list', $values);
    }

  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $this->saveGroupsList($con);
    $this->saveUsersList($con);
  }

  public function saveGroupsList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['groups_list']))
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
          ->where('r.permission_id = ?', current($this->object->identifier()))
          ->execute();

    $values = $this->getValue('groups_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new sfGuardGroupPermission();
        $obj->permission_id = current($this->object->identifier());
        $obj->group_id = $value;
        $obj->save();
      }
    }
  }

  public function saveUsersList($con = null)
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
          ->from('sfGuardUserPermission r')
          ->where('r.permission_id = ?', current($this->object->identifier()))
          ->execute();

    $values = $this->getValue('users_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new sfGuardUserPermission();
        $obj->permission_id = current($this->object->identifier());
        $obj->user_id = $value;
        $obj->save();
      }
    }
  }

}