<?php

class UserJoinForm extends BaseFormDoctrine
{
  public function configure()
  {
    $choices = LsListTable::getNetworksForSelect();
    $this->setWidgets(array(
      'home_network_id'   => new sfWidgetFormSelect(array('choices' => $choices)),
      'name_first'        => new sfWidgetFormInput(array(), array('size' => 30)),
      'name_last'         => new sfWidgetFormInput(array(), array('size' => 30)),
      'public_name'       => new sfWidgetFormInput(array(), array('size' => 30)),
      'email'             => new sfWidgetFormInput(array(), array('size' => 30)),
      'reason'            => new sfWidgetFormTextarea(array(), array('cols' => 50, 'rows' => 3)), 
      'pw1'         => new sfWidgetFormInputPassword(array(), array('size' => 30)),
      'pw2'         => new sfWidgetFormInputPassword(array(), array('size' => 30)),
      'accepts_terms'       => new sfWidgetFormInputCheckbox(),
      'captcha'           => new sfWidgetFormReCaptcha(array('public_key' => sfConfig::get('app_recaptcha_public_key')))
    ));

    $this->setValidators(array(
      'home_network_id'   => new sfValidatorChoice(array('choices' => array_keys($choices), 'required' => true)),
      'name_last'         => new sfValidatorString(array('max_length' => 50)),
      'name_first'        => new sfValidatorString(array('max_length' => 50)),
      'public_name'       => new sfValidatorRegex(array('pattern' => '/^[a-z0-9\.]{4,30}$/i')),
      'email'             => new sfValidatorEmail(array('required' => true), array(
          'invalid' => 'You must enter a valid email address'
      )),
      'reason'            => new sfValidatorString(array('required' => true), array(
        'required' => 'Who are you and why are you signing up?'
      )),
      'pw1'         => new sfValidatorRegex(array('pattern' => '/^[a-z0-9]{6,20}$/i')),
      'pw2'         => new sfValidatorString(array(), array(
        'required' => 'You must enter the password twice'
      )),
      'accepts_terms'       => new sfValidatorBoolean(array('required' => true), array(
        'required' => 'You must accept the user agreement'
      )),
      'captcha'           => new sfValidatorReCaptcha(array('private_key' => sfConfig::get('app_recaptcha_private_key')), array(
        'invalid' => 'You didn\'t pass the spam test!'
      ))
    ));
    
    $this->validatorSchema['captcha']->addMessage('captcha', 'You didn\'t pass the spam test!');

    $this->validatorSchema->setPostValidator(
      new sfValidatorSchemaCompare('password1', sfValidatorSchemaCompare::EQUAL, 'password2'),
      array(),
      array('invalid' => 'You must enter the same password twice')      
    );


    sfLoader::loadHelpers(array('Helper', 'Tag', 'Url'));

    $this->widgetSchema->setLabels(array(
      'home_network_id' => 'Local network',
      'name_first' => 'First name',
      'name_last' => 'Last name',
      'reason' => 'A little about you and why you\'re signing up',
      'analyst_reason' => 'Why you want to be an ' . link_to('analyst', '@howto'),
      'public_name' => 'Public username',
      'pw1' => 'Password',
      'pw2' => 'Password (again)',
      'accepts_terms' => 'Terms of use',
      'captcha' => 'Spam test'
    ));

    $this->widgetSchema->setNameFormat('user[%s]');

    $this->validatorSchema->setOption('allow_extra_fields', true);
    $this->validatorSchema->setOption('filter_extra_fields', false);
  }


  public function getModelName()
  {
    return 'sfGuardUserProfile';
  }
}