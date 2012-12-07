<?php

/**
 * ExtensionRecord form base class.
 *
 * @package    form
 * @subpackage extension_record
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseExtensionRecordForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'            => new sfWidgetFormInputHidden(),
      'entity_id'     => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
      'definition_id' => new sfWidgetFormDoctrineSelect(array('model' => 'ExtensionDefinition', 'add_empty' => false)),
      'last_user_id'  => new sfWidgetFormDoctrineSelect(array('model' => 'sfGuardUser', 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'            => new sfValidatorDoctrineChoice(array('model' => 'ExtensionRecord', 'column' => 'id', 'required' => false)),
      'entity_id'     => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
      'definition_id' => new sfValidatorDoctrineChoice(array('model' => 'ExtensionDefinition')),
      'last_user_id'  => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('extension_record[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ExtensionRecord';
  }

}