<?php

/**
 * Email form.
 *
 * @package    form
 * @subpackage Email
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class EmailForm extends BaseEmailForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'address'    => new sfWidgetFormInput(array(), array('size' => 30)),
    ));

    $this->setValidators(array(
      'address'    => new sfValidatorAnd(
        array(
          new sfValidatorEmail(),
          new sfValidatorString(array('max_length' => 60))
        ),
        array('required' => true)
      )
    ));
    
    $this->widgetSchema->setLabels(array(
      'address' => 'Email'
    ));

    $this->widgetSchema->setNameFormat('email[%s]');
  }
}