<?php

/**
 * EntityAddress form base class.
 *
 * @package    form
 * @subpackage entity_address
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseEntityAddressForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'entity_id'   => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
      'address_id'  => new sfWidgetFormDoctrineSelect(array('model' => 'Address', 'add_empty' => false)),
      'street2'     => new sfWidgetFormInput(),
      'category_id' => new sfWidgetFormDoctrineSelect(array('model' => 'AddressCategory', 'add_empty' => true)),
      'created_at'  => new sfWidgetFormDateTime(),
      'updated_at'  => new sfWidgetFormDateTime(),
      'start_date'  => new sfWidgetFormInput(),
      'end_date'    => new sfWidgetFormInput(),
      'is_current'  => new sfWidgetFormInputCheckbox(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorDoctrineChoice(array('model' => 'EntityAddress', 'column' => 'id', 'required' => false)),
      'entity_id'   => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
      'address_id'  => new sfValidatorDoctrineChoice(array('model' => 'Address')),
      'street2'     => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'category_id' => new sfValidatorDoctrineChoice(array('model' => 'AddressCategory', 'required' => false)),
      'created_at'  => new sfValidatorDateTime(array('required' => false)),
      'updated_at'  => new sfValidatorDateTime(array('required' => false)),
      'start_date'  => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'end_date'    => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'is_current'  => new sfValidatorBoolean(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('entity_address[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'EntityAddress';
  }

}