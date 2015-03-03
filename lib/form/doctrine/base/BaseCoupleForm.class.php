<?php

class BaseCoupleForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'name'           => new sfWidgetFormInput(),
      'partner1_id'    => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => true)),
      'partner2_id'    => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => true)),
      'entity_id'      => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorDoctrineChoice(array('model' => 'Couple', 'column' => 'id', 'required' => false)),
      'name'           => new sfValidatorString(array('max_length' => 200)),
      'partner1_id'    => new sfValidatorDoctrineChoice(array('model' => 'Entity', 'required' => false)),
      'partner2_id'    => new sfValidatorDoctrineChoice(array('model' => 'Entity', 'required' => false)),
      'entity_id'      => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
    ));

    $this->widgetSchema->setNameFormat('person[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Couple';
  }

}