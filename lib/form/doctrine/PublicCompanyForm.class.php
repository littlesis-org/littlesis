<?php

/**
 * PublicCompany form.
 *
 * @package    form
 * @subpackage PublicCompany
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class PublicCompanyForm extends BasePublicCompanyForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'ticker'    => new sfWidgetFormInput(),
      //'sec_cik'   => new sfWidgetFormInput()
    ));

    $this->setValidators(array(
      'ticker'    => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      //'sec_cik'   => new sfValidatorInteger(array('required' => false))
    ));

    $this->widgetSchema->setLabels(array(
      //'sec_cik' => 'SEC CIK'
    ));

    $this->widgetSchema->setNameFormat('entity[%s]');
  }
}