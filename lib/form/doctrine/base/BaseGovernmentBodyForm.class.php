<?php

/**
 * GovernmentBody form base class.
 *
 * @package    form
 * @subpackage government_body
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseGovernmentBodyForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'is_federal' => new sfWidgetFormInputCheckbox(),
      'state_id'   => new sfWidgetFormDoctrineSelect(array('model' => 'AddressState', 'add_empty' => true)),
      'city'       => new sfWidgetFormInput(),
      'county'     => new sfWidgetFormInput(),
      'entity_id'  => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorDoctrineChoice(array('model' => 'GovernmentBody', 'column' => 'id', 'required' => false)),
      'is_federal' => new sfValidatorBoolean(array('required' => false)),
      'state_id'   => new sfValidatorDoctrineChoice(array('model' => 'AddressState', 'required' => false)),
      'city'       => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'county'     => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'entity_id'  => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
    ));

    $this->widgetSchema->setNameFormat('government_body[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'GovernmentBody';
  }

}