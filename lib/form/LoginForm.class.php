<?php

class LoginForm extends sfGuardFormSignin
{
  public function configure()
  {
    $this->setWidgets(array(
      'username' => new sfWidgetFormInput(array(), array('size' => 40)),
      'password' => new sfWidgetFormInput(array('type' => 'password'), array('size' => 40)),
      'remember' => new sfWidgetFormInputCheckbox()
    ));

    $this->setValidators(array(
      'username' => new sfValidatorString(),
      'password' => new sfValidatorString(),
    ));

    $postValidator = new sfGuardValidatorUser();
    $postValidator->setMessage('invalid', 'The email and/or password is invalid');
    $postValidator->addOption('throw_global_error', true);
    $this->validatorSchema->setPostValidator($postValidator);

    $this->widgetSchema->setLabels(array(
      'username' => 'Email',
      'remember' => 'Remember me'
    ));

    $this->widgetSchema->setNameFormat('signin[%s]');

    $this->validatorSchema->setOption('allow_extra_fields', true);
    $this->validatorSchema->setOption('filter_extra_fields', false);

    $this->setDefaults(array('remember' => true));
  }
}
