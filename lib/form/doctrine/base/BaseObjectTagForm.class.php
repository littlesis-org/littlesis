<?php

/**
 * ObjectTag form base class.
 *
 * @package    form
 * @subpackage object_tag
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseObjectTagForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'tag_id'       => new sfWidgetFormDoctrineSelect(array('model' => 'Tag', 'add_empty' => false)),
      'created_at'   => new sfWidgetFormDateTime(),
      'updated_at'   => new sfWidgetFormDateTime(),
      'object_model' => new sfWidgetFormInput(),
      'object_id'    => new sfWidgetFormInput(),
      'last_user_id' => new sfWidgetFormDoctrineSelect(array('model' => 'sfGuardUser', 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorDoctrineChoice(array('model' => 'ObjectTag', 'column' => 'id', 'required' => false)),
      'tag_id'       => new sfValidatorDoctrineChoice(array('model' => 'Tag')),
      'created_at'   => new sfValidatorDateTime(array('required' => false)),
      'updated_at'   => new sfValidatorDateTime(array('required' => false)),
      'object_model' => new sfValidatorString(array('max_length' => 50)),
      'object_id'    => new sfValidatorInteger(),
      'last_user_id' => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('object_tag[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ObjectTag';
  }

}