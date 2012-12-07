<?php

/**
 * CustomKey form base class.
 *
 * @package    form
 * @subpackage custom_key
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseCustomKeyForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'name'         => new sfWidgetFormInput(),
      'value'        => new sfWidgetFormTextarea(),
      'description'  => new sfWidgetFormInput(),
      'created_at'   => new sfWidgetFormDateTime(),
      'updated_at'   => new sfWidgetFormDateTime(),
      'object_model' => new sfWidgetFormInput(),
      'object_id'    => new sfWidgetFormInput(),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorDoctrineChoice(array('model' => 'CustomKey', 'column' => 'id', 'required' => false)),
      'name'         => new sfValidatorString(array('max_length' => 50)),
      'value'        => new sfValidatorString(array('required' => false)),
      'description'  => new sfValidatorString(array('max_length' => 200, 'required' => false)),
      'created_at'   => new sfValidatorDateTime(array('required' => false)),
      'updated_at'   => new sfValidatorDateTime(array('required' => false)),
      'object_model' => new sfValidatorString(array('max_length' => 50)),
      'object_id'    => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('custom_key[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'CustomKey';
  }

}