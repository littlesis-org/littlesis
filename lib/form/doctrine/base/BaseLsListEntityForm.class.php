<?php

/**
 * LsListEntity form base class.
 *
 * @package    form
 * @subpackage ls_list_entity
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseLsListEntityForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'list_id'      => new sfWidgetFormDoctrineSelect(array('model' => 'LsList', 'add_empty' => false)),
      'entity_id'    => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
      'rank'         => new sfWidgetFormInput(),
      'created_at'   => new sfWidgetFormDateTime(),
      'updated_at'   => new sfWidgetFormDateTime(),
      'last_user_id' => new sfWidgetFormDoctrineSelect(array('model' => 'sfGuardUser', 'add_empty' => true)),
      'is_deleted'   => new sfWidgetFormInputCheckbox(),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorDoctrineChoice(array('model' => 'LsListEntity', 'column' => 'id', 'required' => false)),
      'list_id'      => new sfValidatorDoctrineChoice(array('model' => 'LsList')),
      'entity_id'    => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
      'rank'         => new sfValidatorInteger(array('required' => false)),
      'created_at'   => new sfValidatorDateTime(array('required' => false)),
      'updated_at'   => new sfValidatorDateTime(array('required' => false)),
      'last_user_id' => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser', 'required' => false)),
      'is_deleted'   => new sfValidatorBoolean(),
    ));

    $this->widgetSchema->setNameFormat('ls_list_entity[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'LsListEntity';
  }

}