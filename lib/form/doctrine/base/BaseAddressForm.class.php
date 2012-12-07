<?php

/**
 * Address form base class.
 *
 * @package    form
 * @subpackage address
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseAddressForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'entity_id'    => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
      'street1'      => new sfWidgetFormInput(),
      'street2'      => new sfWidgetFormInput(),
      'street3'      => new sfWidgetFormInput(),
      'city'         => new sfWidgetFormInput(),
      'county'       => new sfWidgetFormInput(),
      'state_id'     => new sfWidgetFormDoctrineSelect(array('model' => 'AddressState', 'add_empty' => false)),
      'country_id'   => new sfWidgetFormDoctrineSelect(array('model' => 'AddressCountry', 'add_empty' => false)),
      'postal'       => new sfWidgetFormInput(),
      'latitude'     => new sfWidgetFormInput(),
      'longitude'    => new sfWidgetFormInput(),
      'category_id'  => new sfWidgetFormDoctrineSelect(array('model' => 'AddressCategory', 'add_empty' => true)),
      'created_at'   => new sfWidgetFormDateTime(),
      'updated_at'   => new sfWidgetFormDateTime(),
      'last_user_id' => new sfWidgetFormDoctrineSelect(array('model' => 'sfGuardUser', 'add_empty' => true)),
      'is_deleted'   => new sfWidgetFormInputCheckbox(),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorDoctrineChoice(array('model' => 'Address', 'column' => 'id', 'required' => false)),
      'entity_id'    => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
      'street1'      => new sfValidatorString(array('max_length' => 100)),
      'street2'      => new sfValidatorString(array('max_length' => 100)),
      'street3'      => new sfValidatorString(array('max_length' => 100)),
      'city'         => new sfValidatorString(array('max_length' => 50)),
      'county'       => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'state_id'     => new sfValidatorDoctrineChoice(array('model' => 'AddressState')),
      'country_id'   => new sfValidatorDoctrineChoice(array('model' => 'AddressCountry')),
      'postal'       => new sfValidatorString(array('max_length' => 5, 'required' => false)),
      'latitude'     => new sfValidatorString(array('max_length' => 20, 'required' => false)),
      'longitude'    => new sfValidatorString(array('max_length' => 20, 'required' => false)),
      'category_id'  => new sfValidatorDoctrineChoice(array('model' => 'AddressCategory', 'required' => false)),
      'created_at'   => new sfValidatorDateTime(array('required' => false)),
      'updated_at'   => new sfValidatorDateTime(array('required' => false)),
      'last_user_id' => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser', 'required' => false)),
      'is_deleted'   => new sfValidatorBoolean(),
    ));

    $this->widgetSchema->setNameFormat('address[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Address';
  }

}