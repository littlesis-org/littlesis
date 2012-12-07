<?php

/**
 * Family form base class.
 *
 * @package    form
 * @subpackage family
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseFamilyForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'is_nonbiological' => new sfWidgetFormInputCheckbox(),
      'relationship_id'  => new sfWidgetFormDoctrineSelect(array('model' => 'Relationship', 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorDoctrineChoice(array('model' => 'Family', 'column' => 'id', 'required' => false)),
      'is_nonbiological' => new sfValidatorBoolean(array('required' => false)),
      'relationship_id'  => new sfValidatorDoctrineChoice(array('model' => 'Relationship')),
    ));

    $this->widgetSchema->setNameFormat('family[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Family';
  }

}