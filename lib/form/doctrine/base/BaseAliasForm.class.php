<?php

/**
 * Alias form base class.
 *
 * @package    form
 * @subpackage alias
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseAliasForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'entity_id'    => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
      'name'         => new sfWidgetFormInput(),
      'context'      => new sfWidgetFormInput(),
      'is_primary'   => new sfWidgetFormInputCheckbox(),
      'created_at'   => new sfWidgetFormDateTime(),
      'updated_at'   => new sfWidgetFormDateTime(),
      'last_user_id' => new sfWidgetFormDoctrineSelect(array('model' => 'sfGuardUser', 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorDoctrineChoice(array('model' => 'Alias', 'column' => 'id', 'required' => false)),
      'entity_id'    => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
      'name'         => new sfValidatorString(array('max_length' => 200)),
      'context'      => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'is_primary'   => new sfValidatorBoolean(),
      'created_at'   => new sfValidatorDateTime(array('required' => false)),
      'updated_at'   => new sfValidatorDateTime(array('required' => false)),
      'last_user_id' => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('alias[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Alias';
  }

}