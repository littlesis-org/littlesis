<?php

/**
 * Position form base class.
 *
 * @package    form
 * @subpackage position
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BasePositionForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'is_board'        => new sfWidgetFormInputCheckbox(),
      'is_executive'    => new sfWidgetFormInputCheckbox(),
      'is_employee'     => new sfWidgetFormInputCheckbox(),
      'compensation'    => new sfWidgetFormInput(),
      'boss_id'         => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => true)),
      'relationship_id' => new sfWidgetFormDoctrineSelect(array('model' => 'Relationship', 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorDoctrineChoice(array('model' => 'Position', 'column' => 'id', 'required' => false)),
      'is_board'        => new sfValidatorBoolean(array('required' => false)),
      'is_executive'    => new sfValidatorBoolean(array('required' => false)),
      'is_employee'     => new sfValidatorBoolean(array('required' => false)),
      'compensation'    => new sfValidatorInteger(array('required' => false)),
      'boss_id'         => new sfValidatorDoctrineChoice(array('model' => 'Entity', 'required' => false)),
      'relationship_id' => new sfValidatorDoctrineChoice(array('model' => 'Relationship')),
    ));

    $this->widgetSchema->setNameFormat('position[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Position';
  }

}