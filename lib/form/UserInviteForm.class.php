<?php

class UserInviteForm extends sfForm
{
  public function configure()
  {
    $groupChoices = array();
    $groups = Doctrine::getTable('sfGuardGroup')->findAll();
    
    foreach ($groups as $group)
    {
      $groupChoices[$group->name] = $group->name;
    }

    $this->setWidgets(array(
      'name_first'  => new sfWidgetFormInput(array(), array('size' => 30)),
      'name_last'   => new sfWidgetFormInput(array(), array('size' => 30)),
      'email'       => new sfWidgetFormInput(array(), array('size' => 50)),
      'group'       => new sfWidgetFormSelect(array('choices' => $groupChoices)),
      'template'    => new sfWidgetFormSelect(array('choices' => array('sendinvite' => 'Basic')))
    ));

    $this->setValidators(array(
      'name_last'   => new sfValidatorString(array('max_length' => 50)),
      'name_first'  => new sfValidatorString(array('max_length' => 50)),
      'email'       => new sfValidatorEmail()
    ));

    $this->widgetSchema->setLabels(array(
      'name_first' => 'First name',
      'name_last' => 'Last name'
    ));

    $this->widgetSchema->setNameFormat('user[%s]');

    $this->validatorSchema->setOption('allow_extra_fields', true);
    $this->validatorSchema->setOption('filter_extra_fields', false);
  }
}