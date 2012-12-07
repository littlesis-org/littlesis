<?php

/**
 * AddressState form base class.
 *
 * @package    form
 * @subpackage address_state
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseAddressStateForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'name'         => new sfWidgetFormInput(),
      'abbreviation' => new sfWidgetFormInput(),
      'country_id'   => new sfWidgetFormDoctrineSelect(array('model' => 'AddressCountry', 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorDoctrineChoice(array('model' => 'AddressState', 'column' => 'id', 'required' => false)),
      'name'         => new sfValidatorString(array('max_length' => 50)),
      'abbreviation' => new sfValidatorString(array('max_length' => 2)),
      'country_id'   => new sfValidatorDoctrineChoice(array('model' => 'AddressCountry')),
    ));

    $this->widgetSchema->setNameFormat('address_state[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'AddressState';
  }

}