<?php

/**
 * Phone form.
 *
 * @package    form
 * @subpackage Phone
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class PhoneForm extends BasePhoneForm
{


  public function configure()
  {
    $this->setWidgets(array(
      'number'     => new sfWidgetFormInput(),
      'type'    => new sfWidgetFormSelect(array('choices' => array_combine(PhoneTable::$types,PhoneTable::$types)))
    ));

    $this->setValidators(array(
      'number'     => new sfValidatorString(array('required' => true, 'max_length' => 20)),
      'type'    => new sfValidatorChoice(array('choices' => array_values(PhoneTable::$types)))
    ));
    
    $this->widgetSchema->setLabels(array(
      'type' => 'Type'
    ));
    
    $this->widgetSchema->setHelps(array(
      'number' => '10 digits only'
    ));

    $this->widgetSchema->setNameFormat('phone[%s]');
  }
}