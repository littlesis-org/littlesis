<?php

/**
 * Org form.
 *
 * @package    form
 * @subpackage Org
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class OrgForm extends BaseOrgForm
{
  public function configure()
  {
    $this->setWidgets(array(
      //'name'                => new sfWidgetFormInput(array(), array('size' => '30')),
      //'name_nick'           => new sfWidgetFormInput(array(), array('size' => '30')),
      //'employees'           => new sfWidgetFormInput(array(), array('size' => '15')),
      //'revenue'             => new sfWidgetFormInput(array(), array('size' => '15')),
      //'lda_registrant_id'   => new sfWidgetFormInput(array(), array('size' => '10')),
      //'fedspending_id'      => new sfWidgetFormInput(array(), array('size' => '10')),
    ));

    $this->setValidators(array(
      //'name'        => new sfValidatorString(array('max_length' => 100), array(
      //  'invalid' => 'Name can be 100 characters maximum',
      //  'required' => 'Name is required'
      //)),
      //'name_nick'   => new sfValidatorString(array('max_length' => 100, 'required' => false), array(
      //  'invalid' => 'Nick name can be 100 characters maximum',
      //  'required' => 'Nick name is required'
      //)),
      //'employees'   => new sfValidatorNumber(array('required' => false), array(
      //  'invalid' => 'Employees must be a number'
      //)),
      //'revenue'     => new sfValidatorNumber(array('required' => false), array(
      //  'invalid' => 'Revenue must be a number'
      //)),
      //'fedspending_id' => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      //'lda_registrant_id' => new sfValidatorString(array('max_length' => 10, 'required' => false)),
    ));

    $this->widgetSchema->setLabels(array(
      //'name_nick'  => 'Nick name',
      //'is_current' => 'Is active',
      //'fedspending_id' => 'FedSpending.org ID',
      //'lda_registrant_id' => 'LDA Registrant ID'
    ));
    
    $this->widgetSchema->setHelps(array(
      //'employees' => LsFormHelp::$numberHelp,
      //'revenue' => LsFormHelp::$numberHelp
    ));

    $this->widgetSchema->setNameFormat('entity[%s]');
  }
}