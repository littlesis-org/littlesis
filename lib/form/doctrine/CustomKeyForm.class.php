<?php

/**
 * CustomKey form.
 *
 * @package    form
 * @subpackage CustomKey
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class CustomKeyForm extends BaseCustomKeyForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'name' => new sfWidgetFormInput(),
      'value' => new sfWidgetFormTextarea(array(), array('cols' => 100, 'rows' => 8))
    ));

    $this->setValidators(array(
      'name' => new sfValidatorRegex(
        array('required' => true, 'pattern' => '/^[a-z0-9_\-\.]{2,50}$/i'), 
        array('invalid' => 'The field name you entered is duplicate or invalid')
      ),
      'value' => new sfValidatorString(array('required' => true))
    ));

    $this->widgetSchema->setLabels(array(
      'name' => 'Field Name'
    ));
    
    $this->widgetSchema->setHelps(array(
      'name' => 'letters, numbers, hyphens, underscores, or periods'
    ));

    $this->widgetSchema->setNameFormat('custom_key[%s]');

    $this->validatorSchema->setOption('allow_extra_fields', true);
    $this->validatorSchema->setOption('filter_extra_fields', false);
  }
}