<?php

class UserProfileForm extends BasesfGuardUserProfileForm
{
  public function configure()
  {
    $this->setWidgets(array(
      //'public_name' => new sfWidgetFormInput(array(), array('size' => 30)),
      'show_full_name' => new sfWidgetFormInputCheckbox(),
      'bio'         => new sfWidgetFormTextarea(array(), array('rows' => 5, 'cols' => 50))
    ));

    $this->setValidators(array(
      //'public_name' => new sfValidatorRegex(array('pattern' => '/^[a-z0-9\.]{4,30}$/i')),
      'show_full_name' => new sfValidatorBoolean(array('required' => false)),
      'bio'         => new sfValidatorString(array('required' => false))
    ));


    $this->widgetSchema->setLabels(array(
      'bio' => 'Bio',
      'show_full_name' => 'Show full name'
    ));
    
    $this->widgetSchema->setHelps(array(
      'show_full_name' => $this->getObject()->getName()
    ));

    $this->widgetSchema->setNameFormat('user_profile[%s]');
  }
}