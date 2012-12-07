<?php

/**
 * School form base class.
 *
 * @package    form
 * @subpackage school
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseSchoolForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'endowment'  => new sfWidgetFormInput(),
      'students'   => new sfWidgetFormInput(),
      'faculty'    => new sfWidgetFormInput(),
      'tuition'    => new sfWidgetFormInput(),
      'is_private' => new sfWidgetFormInputCheckbox(),
      'entity_id'  => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorDoctrineChoice(array('model' => 'School', 'column' => 'id', 'required' => false)),
      'endowment'  => new sfValidatorInteger(array('required' => false)),
      'students'   => new sfValidatorInteger(array('required' => false)),
      'faculty'    => new sfValidatorInteger(array('required' => false)),
      'tuition'    => new sfValidatorInteger(array('required' => false)),
      'is_private' => new sfValidatorBoolean(array('required' => false)),
      'entity_id'  => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
    ));

    $this->widgetSchema->setNameFormat('school[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'School';
  }

}