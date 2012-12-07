<?php

class ResetPasswordForm extends sfForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'username' => new sfWidgetFormInput(array(), array('size' => 30)),
      'name_first' => new sfWidgetFormInput(array(), array('size' => 30)),
      'name_last' => new sfWidgetFormInput(array(), array('size' => 30)),
      'captcha'     => new sfWidgetFormReCaptcha(array('public_key' => sfConfig::get('app_recaptcha_public_key')))
    ));

    $this->setValidators(array(
      'username' => new sfValidatorString(),
      'name_first' => new sfValidatorString(),
      'name_last' => new sfValidatorString(),
      'captcha'     => new sfValidatorReCaptcha(array('private_key' => sfConfig::get('app_recaptcha_private_key')), array(
        'invalid' => 'Captcha is invalid'
      ))
    ));

    $this->validatorSchema['captcha']->addMessage('captcha', 'Captcha is invalid');

    $this->widgetSchema->setLabels(array(
      'username' => 'Email',
      'name_first' => 'First name',
      'name_last' => 'Last name'
    ));

    $this->widgetSchema->setNameFormat('reset_password[%s]');
  }
}
