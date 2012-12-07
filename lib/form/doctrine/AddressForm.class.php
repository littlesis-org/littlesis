<?php

/**
 * Address form.
 *
 * @package    form
 * @subpackage Address
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class AddressForm extends BaseAddressForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'street1'     => new sfWidgetFormInput(),
      'street2'     => new sfWidgetFormInput(),
      'city'        => new sfWidgetFormInput(),
      'state_id'    => new sfWidgetFormDoctrineSelect(array('model' => 'AddressState', 'add_empty' => true)),
      'country_id'  => new sfWidgetFormDoctrineSelect(array('model' => 'AddressCountry', 'add_empty' => false)),
      'postal'      => new sfWidgetFormInput(),
      'category_id' => new sfWidgetFormDoctrineSelect(array('model' => 'AddressCategory', 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'street1'     => new sfValidatorString(array('max_length' => 100)),
      'street2'     => new LsValidatorString(array('max_length' => 100, 'required' => false)),
      'city'        => new sfValidatorString(array('max_length' => 50)),
      'state_id'    => new sfValidatorDoctrineChoice(array('model' => 'AddressState', 'required' => true)),
      'country_id'  => new sfValidatorDoctrineChoice(array('model' => 'AddressCountry')),
      'postal'      => new LsValidatorString(array('max_length' => 5, 'required' => false)),
      'category_id' => new sfValidatorDoctrineChoice(array('model' => 'AddressCategory', 'required' => false)),
    ));

    $this->widgetSchema->setLabels(array(
      'state_id' => 'State',
      'country_id' => 'Country',
      'category_id' => 'Category'    
    ));

    $this->widgetSchema->setNameFormat('address[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
  }
}