<?php

/**
 * Person form.
 *
 * @package    form
 * @subpackage Person
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class PersonForm extends BasePersonForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'name_first'  => new sfWidgetFormInput(),
      'name_middle' => new sfWidgetFormInput(),
      'name_last'   => new sfWidgetFormInput(),
      'name_prefix' => new sfWidgetFormInput(),
      'name_suffix' => new sfWidgetFormInput(),
      'name_nick'   => new sfWidgetFormInput(),
      'birthplace'  => new sfWidgetFormInput(),
      'gender_id'   => new sfWidgetFormDoctrineSelect(array('model' => 'Gender', 'add_empty' => true)),
      //'net_worth'   => new sfWidgetFormInput(),
    ));

    $this->setValidators(array(
      'name_last'   => new sfValidatorString(array('max_length' => 50), array(
        'invalid' => 'Last name can be 50 characters maximum',
        'required' => 'Last name is required'
      )),
      'name_first'  => new sfValidatorString(array('max_length' => 50), array(
        'invalid' => 'First name can be 50 characters maximum',
        'required' => 'First name is required'
      )),
      'name_middle' => new sfValidatorString(array('max_length' => 50, 'required' => false), array(
        'invalid' => 'Middle name can be 50 characters maximum'
      )),
      'name_prefix' => new sfValidatorString(array('max_length' => 30, 'required' => false), array(
        'invalid' => 'Prefix can be 30 characters maximum'
      )),
      'name_suffix' => new sfValidatorString(array('max_length' => 30, 'required' => false), array(
        'invalid' => 'Suffix can be 30 characters maximum'
      )),
      'name_nick'   => new sfValidatorString(array('max_length' => 30, 'required' => false), array(
        'invalid' => 'Nick name can be 30 characters maximum'
      )),
      'birthplace'  => new sfValidatorString(array('max_length' => 50, 'required' => false), array(
        'invalid' => 'Birthplace can be 50 characters maximum'
      )),
      'gender_id'   => new sfValidatorDoctrineChoice(array('model' => 'Gender', 'required' => false)),
      //'net_worth'   => new sfValidatorNumber(array('required' => false), array(
      //  'invalid' => 'Net worth must be a number'
      //)),
    ));

    $this->widgetSchema->setLabels(array(
      'name_first' => 'First name',
      'name_middle' => 'Middle name',
      'name_last' => 'Last name',
      'name_nick' => 'Nick name',
      'gender_id' => 'Gender',
      'start_date' => 'Date of birth',
      'end_date' => 'Date of death',
      'is_current' => 'Is living'
    ));
    
    $this->widgetSchema->setHelps(array(
      'net_worth' => LsFormHelp::$numberHelp
    ));

    $this->widgetSchema->setNameFormat('entity[%s]');
  }
}