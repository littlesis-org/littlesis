<?php

/**
 * sfGuardUserGroup form base class.
 *
 * @package    form
 * @subpackage sf_guard_user_group
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BasesfGuardUserGroupForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'user_id'    => new sfWidgetFormInputHidden(),
      'group_id'   => new sfWidgetFormInputHidden(),
      'is_owner'   => new sfWidgetFormInputCheckbox(),
      'score'      => new sfWidgetFormInput(),
      'created_at' => new sfWidgetFormDateTime(),
      'updated_at' => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'user_id'    => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUserGroup', 'column' => 'user_id', 'required' => false)),
      'group_id'   => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUserGroup', 'column' => 'group_id', 'required' => false)),
      'is_owner'   => new sfValidatorBoolean(),
      'score'      => new sfValidatorInteger(),
      'created_at' => new sfValidatorDateTime(array('required' => false)),
      'updated_at' => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_guard_user_group[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfGuardUserGroup';
  }

}