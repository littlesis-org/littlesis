<?php

/**
 * PoliticalFundraising form.
 *
 * @package    form
 * @subpackage PoliticalFundraising
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class PoliticalFundraisingForm extends BasePoliticalFundraisingForm
{
  public function configure()
  {
    /*
    $this->setWidgets(array(
      'fec_id'    => new sfWidgetFormInput(),
      'type_id'   => new sfWidgetFormDoctrineSelect(array('model' => 'PoliticalFundraisingType', 'add_empty' => true)),
      'state_id'  => new sfWidgetFormDoctrineSelect(array('model' => 'AddressState', 'add_empty' => true))
    ));

    $this->setValidators(array(
      'fec_id'    => new sfValidatorString(array('max_length' => 20, 'required' => false)),
      'type_id'   => new sfValidatorDoctrineChoice(array('model' => 'PoliticalFundraisingType', 'required' => false)),
      'state_id'  => new sfValidatorDoctrineChoice(array('model' => 'AddressState', 'required' => false))
    ));

    $this->widgetSchema->setLabels(array(
      'fec_id' => 'FEC ID',
      'type_id' => 'Fundraising Committee Type',
      'state_id' => 'Fundraising Committee State'
    ));
    */
    $this->widgetSchema->setNameFormat('entity[%s]');
    $this->setWidgets(array());
    $this->setValidators(array());
    $this->widgetSchema->setLabels(array());
  }
}