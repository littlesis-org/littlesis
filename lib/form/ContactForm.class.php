<?php

class ContactForm extends sfForm
{
  protected $_captcha = true;

  public function __construct($defaults = array(), $options = array(), $CSRFSecret = null, $captcha=true)
  {
    $this->_captcha = $captcha;
    
    parent::__construct($defaults, $options, $CSRFSecret);
  }
 
  public function configure()
  {
    $subjects = array(
      "",
      "Something's broken",
      "Inaccurate data",
      "Feature request",
      "I want to contribute!",
      "Press Inquiry",
      "Other"
    );
    $subjects = array_combine($subjects, $subjects);

    $widgets = array(
      'name'        => new sfWidgetFormInput(array(), array('size' => 30)),      
      'email'       => new sfWidgetFormInput(array(), array('size' => 30)),
      'subject'     => new sfWidgetFormSelect(array('choices' => $subjects)),
      'message'     => new sfWidgetFormTextarea(array(), array('cols' => 50, 'rows' => 5)),
      'file'        => new sfWidgetFormInputFile(array(), array('size' => 30))
    );
    
    if ($this->_captcha)
    {
      $widgets['captcha'] = new sfWidgetFormReCaptcha(array('public_key' => sfConfig::get('app_recaptcha_public_key')));
    }
        
    $this->setWidgets($widgets);

    $validators = array(
      'name'    => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'email'   => new sfValidatorEmail(array('required' => false), array(
        'invalid' => 'You must enter a valid email address'
      )),
      'subject' => new sfValidatorChoice(array('choices' => array_keys($subjects), 'required' => true)),
      'message' => new sfValidatorString(array('required' => true)),
      'file'        => new sfValidatorFile(array('required' => false, 'max_size' => 1048576)),
    );

    if ($this->_captcha)
    {
      $validators['captcha'] = new sfValidatorReCaptcha(array('private_key' => sfConfig::get('app_recaptcha_private_key')), array(
        'invalid' => 'Captcha is invalid'
      ));
    }
    
    $this->setValidators($validators);
    
    if ($this->_captcha)
    {
      $this->validatorSchema['captcha']->addMessage('captcha', 'Captcha is invalid');
    }

    $this->widgetSchema->setLabels(array(
      'file' => 'Attachment'
    ));
    
    $this->widgetSchema->setHelps(array(
      'file' => '1MB maximum'
    ));

    $this->widgetSchema->setNameFormat('contact[%s]');
  }
}