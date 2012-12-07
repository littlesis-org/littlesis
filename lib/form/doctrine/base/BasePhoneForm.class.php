<?php

/**
 * Phone form base class.
 *
 * @package    form
 * @subpackage phone
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BasePhoneForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'entity_id'    => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
      'number'       => new sfWidgetFormInput(),
      'type'         => new sfWidgetFormSelect(array('choices' => PhoneTable::$types)),
      'created_at'   => new sfWidgetFormDateTime(),
      'updated_at'   => new sfWidgetFormDateTime(),
      'last_user_id' => new sfWidgetFormDoctrineSelect(array('model' => 'sfGuardUser', 'add_empty' => true)),
      'is_deleted'   => new sfWidgetFormInputCheckbox(),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorDoctrineChoice(array('model' => 'Phone', 'column' => 'id', 'required' => false)),
      'entity_id'    => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
      'number'       => new sfValidatorString(array('max_length' => 20)),
      'type'         => new sfValidatorPass(array('required' => false)),
      'created_at'   => new sfValidatorDateTime(array('required' => false)),
      'updated_at'   => new sfValidatorDateTime(array('required' => false)),
      'last_user_id' => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser', 'required' => false)),
      'is_deleted'   => new sfValidatorBoolean(),
    ));

    $this->widgetSchema->setNameFormat('phone[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Phone';
  }

}