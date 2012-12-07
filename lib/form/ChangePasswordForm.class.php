<?php

class ChangePasswordForm extends sfForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'password' => new sfWidgetFormInputPassword(array(), array('size' => 30)),
      'password1'   => new sfWidgetFormInputPassword(array(), array('size' => 30)),
      'password2'   => new sfWidgetFormInputPassword(array(), array('size' => 30)),
    ));

    $this->setValidators(array(
      'password'    => new sfValidatorString(array('required' => true)),
      'password1'   => new sfValidatorRegex(array('pattern' => '/^[a-z0-9]{6,20}$/i')),
      'password2'   => new sfValidatorString(array(), array(
        'required' => 'You must enter the password twice'
      ))
    ));
    
    $postValidator = new sfGuardValidatorUser();
    $postValidator->setMessage('invalid', 'Your current password does not match the one you entered.');
    $postValidator->addOption('throw_global_error', true);
    $this->validatorSchema->setPostValidator($postValidator);
    

    $this->widgetSchema->setLabels(array(
      'password' => 'Current password',
      'password1' => 'New password',
      'password2' => '(again)'
    ));
    
    $this->validatorSchema->setPostValidator(
      new sfValidatorSchemaCompare('password1', sfValidatorSchemaCompare::EQUAL, 'password2'),
      array(),
      array('invalid' => 'You must enter the same password twice')      
    );

    $this->validatorSchema->setOption('allow_extra_fields', true);
    $this->validatorSchema->setOption('filter_extra_fields', false);
    
    $this->widgetSchema->setNameFormat('change_password[%s]');
  }
  

}
