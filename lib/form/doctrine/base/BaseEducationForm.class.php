<?php

/**
 * Education form base class.
 *
 * @package    form
 * @subpackage education
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseEducationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'degree_id'       => new sfWidgetFormDoctrineSelect(array('model' => 'Degree', 'add_empty' => true)),
      'field'           => new sfWidgetFormInput(),
      'is_dropout'      => new sfWidgetFormInputCheckbox(),
      'relationship_id' => new sfWidgetFormDoctrineSelect(array('model' => 'Relationship', 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorDoctrineChoice(array('model' => 'Education', 'column' => 'id', 'required' => false)),
      'degree_id'       => new sfValidatorDoctrineChoice(array('model' => 'Degree', 'required' => false)),
      'field'           => new sfValidatorString(array('max_length' => 30, 'required' => false)),
      'is_dropout'      => new sfValidatorBoolean(array('required' => false)),
      'relationship_id' => new sfValidatorDoctrineChoice(array('model' => 'Relationship')),
    ));

    $this->widgetSchema->setNameFormat('education[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Education';
  }

}