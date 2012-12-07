<?php

/**
 * GovernmentBody form.
 *
 * @package    form
 * @subpackage GovernmentBody
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class GovernmentBodyForm extends BaseGovernmentBodyForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'is_federal' => new LsWidgetFormSelectRadio(array('is_ternary' => true)),
      'state_id'   => new sfWidgetFormDoctrineSelect(array('model' => 'AddressState', 'add_empty' => true)),
      'county'     => new sfWidgetFormInput(),
      'city'       => new sfWidgetFormInput()
    ));

    $this->setValidators(array(
      'is_federal' => new sfValidatorBoolean(array('required' => false)),
      'state_id'   => new sfValidatorDoctrineChoice(array('model' => 'AddressState', 'required' => false)),
      'city'       => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'county'     => new sfValidatorString(array('max_length' => 50, 'required' => false))
    ));

    $this->widgetSchema->setLabels(array(
      'is_federal' => 'Federal Jurisdiction',
      'state_id' => 'State Jurisdiction',
      'county' => 'County Jurisdiction',
      'city' => 'Municipal Jurisdiction'
    ));

    $this->widgetSchema->setNameFormat('entity[%s]');
  }
}