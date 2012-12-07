<?php

/**
 * Modification form base class.
 *
 * @package    form
 * @subpackage modification
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseModificationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'object_name'     => new sfWidgetFormInput(),
      'user_id'         => new sfWidgetFormDoctrineSelect(array('model' => 'sfGuardUser', 'add_empty' => false)),
      'is_create'       => new sfWidgetFormInputCheckbox(),
      'is_delete'       => new sfWidgetFormInputCheckbox(),
      'is_merge'        => new sfWidgetFormInputCheckbox(),
      'merge_object_id' => new sfWidgetFormInput(),
      'created_at'      => new sfWidgetFormDateTime(),
      'updated_at'      => new sfWidgetFormDateTime(),
      'object_model'    => new sfWidgetFormInput(),
      'object_id'       => new sfWidgetFormInput(),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorDoctrineChoice(array('model' => 'Modification', 'column' => 'id', 'required' => false)),
      'object_name'     => new sfValidatorString(array('max_length' => 100)),
      'user_id'         => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser')),
      'is_create'       => new sfValidatorBoolean(),
      'is_delete'       => new sfValidatorBoolean(),
      'is_merge'        => new sfValidatorBoolean(),
      'merge_object_id' => new sfValidatorInteger(array('required' => false)),
      'created_at'      => new sfValidatorDateTime(array('required' => false)),
      'updated_at'      => new sfValidatorDateTime(array('required' => false)),
      'object_model'    => new sfValidatorString(array('max_length' => 50)),
      'object_id'       => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('modification[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Modification';
  }

}