<?php

/**
 * ExtensionDefinition form base class.
 *
 * @package    form
 * @subpackage extension_definition
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseExtensionDefinitionForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'name'         => new sfWidgetFormInput(),
      'display_name' => new sfWidgetFormInput(),
      'has_fields'   => new sfWidgetFormInputCheckbox(),
      'parent_id'    => new sfWidgetFormDoctrineSelect(array('model' => 'ExtensionDefinition', 'add_empty' => true)),
      'tier'         => new sfWidgetFormInput(),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorDoctrineChoice(array('model' => 'ExtensionDefinition', 'column' => 'id', 'required' => false)),
      'name'         => new sfValidatorString(array('max_length' => 30)),
      'display_name' => new sfValidatorString(array('max_length' => 50)),
      'has_fields'   => new sfValidatorBoolean(),
      'parent_id'    => new sfValidatorDoctrineChoice(array('model' => 'ExtensionDefinition', 'required' => false)),
      'tier'         => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('extension_definition[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ExtensionDefinition';
  }

}