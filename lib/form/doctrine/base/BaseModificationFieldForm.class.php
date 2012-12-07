<?php

/**
 * ModificationField form base class.
 *
 * @package    form
 * @subpackage modification_field
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseModificationFieldForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'modification_id' => new sfWidgetFormDoctrineSelect(array('model' => 'Modification', 'add_empty' => false)),
      'field_name'      => new sfWidgetFormInput(),
      'old_value'       => new sfWidgetFormTextarea(),
      'new_value'       => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorDoctrineChoice(array('model' => 'ModificationField', 'column' => 'id', 'required' => false)),
      'modification_id' => new sfValidatorDoctrineChoice(array('model' => 'Modification')),
      'field_name'      => new sfValidatorString(array('max_length' => 50)),
      'old_value'       => new sfValidatorString(array('required' => false)),
      'new_value'       => new sfValidatorString(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('modification_field[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ModificationField';
  }

}