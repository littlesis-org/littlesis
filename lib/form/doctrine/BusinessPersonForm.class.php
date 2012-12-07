<?php

/**
 * BusinessPerson form.
 *
 * @package    form
 * @subpackage BusinessPerson
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class BusinessPersonForm extends BaseBusinessPersonForm
{
  public function configure()
  {
    $this->setWidgets(array(
      //'sec_cik'   => new sfWidgetFormInput(),
    ));

    $this->setValidators(array(
      //'sec_cik'   => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setLabels(array(
      //'sec_cik' => 'SEC CIK'
    ));

    $this->widgetSchema->setNameFormat('entity[%s]');
  }
}