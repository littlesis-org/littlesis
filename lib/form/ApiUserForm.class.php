<?php

class ApiUserForm extends sfForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'name_first'  => new sfWidgetFormInput(array(), array('size' => 30)),
      'name_last'   => new sfWidgetFormInput(array(), array('size' => 30)),
      'email'       => new sfWidgetFormInput(array(), array('size' => 30)),
      'reason'      => new sfWidgetFormTextarea(array(), array('cols' => 50, 'rows' => 3)),
      'user_agrees' => new sfWidgetFormInputCheckbox(),
      'captcha'     => new sfWidgetFormReCaptcha(array('public_key' => sfConfig::get('app_recaptcha_public_key')))
    ));

    $this->setValidators(array(
      'name_last'   => new sfValidatorString(array('max_length' => 50)),
      'name_first'  => new sfValidatorString(array('max_length' => 50)),
      'email'       => new sfValidatorEmail(array('required' => true), array(
          'invalid' => 'You must enter a valid email address'
      )),
      'reason'      => new sfValidatorString(array('required' => true), array(
        'required' => 'Why do you want to use the LittleSis API?'
      )),
      'user_agrees' => new sfValidatorBoolean(array('required' => true), array(
        'required' => 'You must accept the user agreement'
      )),
      'captcha'     => new sfValidatorReCaptcha(array('private_key' => sfConfig::get('app_recaptcha_private_key')), array(
        'invalid' => 'You didn\'t pass the spam test!'
      ))
    ));
    
    $this->validatorSchema['captcha']->addMessage('captcha', 'You didn\'t pass the spam test!');

    $this->widgetSchema->setLabels(array(
      'name_first' => 'First name',
      'name_last' => 'Last name',
      'reason' => 'Your purpose for using the LittleSis API',
      'user_agrees' => 'Terms of use',
      'captcha' => 'Spam test'
    ));

    $this->widgetSchema->setNameFormat('api_user[%s]');
  }
}