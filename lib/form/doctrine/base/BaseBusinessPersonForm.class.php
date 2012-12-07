<?php

/**
 * BusinessPerson form base class.
 *
 * @package    form
 * @subpackage business_person
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseBusinessPersonForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'        => new sfWidgetFormInputHidden(),
      'sec_cik'   => new sfWidgetFormInput(),
      'entity_id' => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'        => new sfValidatorDoctrineChoice(array('model' => 'BusinessPerson', 'column' => 'id', 'required' => false)),
      'sec_cik'   => new sfValidatorInteger(array('required' => false)),
      'entity_id' => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
    ));

    $this->widgetSchema->setNameFormat('business_person[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'BusinessPerson';
  }

}