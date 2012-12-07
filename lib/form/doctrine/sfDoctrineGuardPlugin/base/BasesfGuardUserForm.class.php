<?php

/**
 * sfGuardUser form base class.
 *
 * @package    form
 * @subpackage sf_guard_user
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BasesfGuardUserForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                            => new sfWidgetFormInputHidden(),
      'username'                      => new sfWidgetFormInput(),
      'algorithm'                     => new sfWidgetFormInput(),
      'salt'                          => new sfWidgetFormInput(),
      'password'                      => new sfWidgetFormInput(),
      'is_active'                     => new sfWidgetFormInputCheckbox(),
      'is_super_admin'                => new sfWidgetFormInputCheckbox(),
      'last_login'                    => new sfWidgetFormDateTime(),
      'created_at'                    => new sfWidgetFormDateTime(),
      'updated_at'                    => new sfWidgetFormDateTime(),
      'is_deleted'                    => new sfWidgetFormInputCheckbox(),
      'groups_list'      => new sfWidgetFormDoctrineSelectMany(array('model' => 'sfGuardGroup')),
      'permissions_list' => new sfWidgetFormDoctrineSelectMany(array('model' => 'sfGuardPermission')),
    ));

    $this->setValidators(array(
      'id'                            => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser', 'column' => 'id', 'required' => false)),
      'username'                      => new sfValidatorString(array('max_length' => 128)),
      'algorithm'                     => new sfValidatorString(array('max_length' => 128)),
      'salt'                          => new sfValidatorString(array('max_length' => 128, 'required' => false)),
      'password'                      => new sfValidatorString(array('max_length' => 128, 'required' => false)),
      'is_active'                     => new sfValidatorBoolean(array('required' => false)),
      'is_super_admin'                => new sfValidatorBoolean(array('required' => false)),
      'last_login'                    => new sfValidatorDateTime(array('required' => false)),
      'created_at'                    => new sfValidatorDateTime(array('required' => false)),
      'updated_at'                    => new sfValidatorDateTime(array('required' => false)),
      'is_deleted'                    => new sfValidatorBoolean(),
      'groups_list'      => new sfValidatorDoctrineChoiceMany(array('model' => 'sfGuardGroup', 'required' => false)),
      'permissions_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'sfGuardPermission', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_guard_user[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfGuardUser';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['groups_list']))
    {
      $values = array();
      foreach ($this->object->groups as $obj)
      {
        $values[] = current($obj->identifier());
      }
      $this->object->clearRelated('groups');
      $this->setDefault('groups_list', $values);
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

    $this->savegroupsList($con);
    $this->savepermissionsList($con);
  }

  public function savegroupsList($con = null)
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
          ->from('sfGuardUserGroup r')
          ->where('r.user_id = ?', current($this->object->identifier()))
          ->execute();

    $values = $this->getValue('groups_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new sfGuardUserGroup();
        $obj->user_id = current($this->object->identifier());
        $obj->group_id = $value;
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
          ->from('sfGuardUserPermission r')
          ->where('r.user_id = ?', current($this->object->identifier()))
          ->execute();

    $values = $this->getValue('permissions_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new sfGuardUserPermission();
        $obj->user_id = current($this->object->identifier());
        $obj->permission_id = $value;
        $obj->save();
      }
    }
  }

}