<?php

/**
 * School form.
 *
 * @package    form
 * @subpackage School
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class SchoolForm extends BaseSchoolForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'endowment' => new sfWidgetFormInput(array(), array('size' => 10)),
      'students'  => new sfWidgetFormInput(array(), array('size' => 10)),
      'faculty'   => new sfWidgetFormInput(array(), array('size' => 10)),
      'tuition'   => new sfWidgetFormInput(array(), array('size' => 10)),
      'is_private' => new LsWidgetFormSelectRadio(array('is_ternary' => true))
    ));

    $this->setValidators(array(
      'endowment' => new sfValidatorNumber(array('required' => false), array(
        'invalid' => 'Endowment must be a number'
      )),
      'students' => new sfValidatorNumber(array('required' => false), array(
        'invalid' => 'Students must be a number'
      )),
      'faculty' => new sfValidatorNumber(array('required' => false), array(
        'invalid' => 'Faculty must be a number'
      )),
      'tuition' => new sfValidatorNumber(array('required' => false), array(
        'invalid' => 'Tuition must be a number'
      )),
      'is_private' => new sfValidatorBoolean(array('required' => false))
    ));

    $this->widgetSchema->setHelps(array(
      'endowment' => LsFormHelp::$numberHelp,
      'tuition' => LsFormHelp::$numberHelp
    ));

    $this->widgetSchema->setLabels(array(
      'is_private' => 'Private'
    ));

    $this->widgetSchema->setNameFormat('entity[%s]');
  }
}