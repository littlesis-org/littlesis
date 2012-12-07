<?php

/**
 * Image form base class.
 *
 * @package    form
 * @subpackage image
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseImageForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'entity_id'    => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
      'filename'     => new sfWidgetFormInput(),
      'title'        => new sfWidgetFormInput(),
      'caption'      => new sfWidgetFormTextarea(),
      'is_featured'  => new sfWidgetFormInputCheckbox(),
      'is_free'      => new sfWidgetFormInputCheckbox(),
      'url'          => new sfWidgetFormInput(),
      'width'        => new sfWidgetFormInput(),
      'height'       => new sfWidgetFormInput(),
      'created_at'   => new sfWidgetFormDateTime(),
      'updated_at'   => new sfWidgetFormDateTime(),
      'last_user_id' => new sfWidgetFormDoctrineSelect(array('model' => 'sfGuardUser', 'add_empty' => true)),
      'is_deleted'   => new sfWidgetFormInputCheckbox(),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorDoctrineChoice(array('model' => 'Image', 'column' => 'id', 'required' => false)),
      'entity_id'    => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
      'filename'     => new sfValidatorString(array('max_length' => 100)),
      'title'        => new sfValidatorString(array('max_length' => 100)),
      'caption'      => new sfValidatorString(array('required' => false)),
      'is_featured'  => new sfValidatorBoolean(),
      'is_free'      => new sfValidatorBoolean(array('required' => false)),
      'url'          => new sfValidatorString(array('max_length' => 200, 'required' => false)),
      'width'        => new sfValidatorInteger(array('required' => false)),
      'height'       => new sfValidatorInteger(array('required' => false)),
      'created_at'   => new sfValidatorDateTime(array('required' => false)),
      'updated_at'   => new sfValidatorDateTime(array('required' => false)),
      'last_user_id' => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser', 'required' => false)),
      'is_deleted'   => new sfValidatorBoolean(),
    ));

    $this->widgetSchema->setNameFormat('image[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Image';
  }

}