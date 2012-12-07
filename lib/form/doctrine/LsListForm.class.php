<?php

/**
 * LsList form.
 *
 * @package    form
 * @subpackage LsList
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class LsListForm extends BaseLsListForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'name'                => new sfWidgetFormInput(array(), array('size' => 40)),
      'description'         => new sfWidgetFormTextarea(),
      'is_ranked'           => new sfWidgetFormInputCheckbox(),
      'is_admin'            => new sfWidgetFormInputCheckbox(),
      'is_featured'         => new sfWidgetFormInputCheckbox()
    ));

    $this->setValidators(array(
      'name'                => new sfValidatorRegex(
        array('pattern' => '/^[^:]{5,100}$/'),
        array('invalid' => 'Name must be 5-100 characters with no colons')
      ),
      'description'         => new sfValidatorString(array('required' => false)),
      'is_ranked'           => new sfValidatorBoolean(),
      'is_admin'            => new sfValidatorBoolean(),
      'is_featured'         => new sfValidatorBoolean()
    ));

    $this->widgetSchema->setLabels(array(
      'is_ranked' => 'Ranked'
    ));
    
    $this->widgetSchema->setHelps(array(
      'name' => '5-100 characters, no colons',
      'is_ranked' => 'are list members sorted by a rank?'
    ));
    
    $this->validatorSchema->setOption('allow_extra_fields', true);
    $this->validatorSchema->setOption('filter_extra_fields', false);
    
    $this->widgetSchema->setNameFormat('list[%s]');
  }
}