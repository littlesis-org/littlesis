<?php

/**
 * Tag form base class.
 *
 * @package    form
 * @subpackage tag
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseTagForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'name'             => new sfWidgetFormInput(),
      'is_visible'       => new sfWidgetFormInputCheckbox(),
      'triple_namespace' => new sfWidgetFormInput(),
      'triple_predicate' => new sfWidgetFormInput(),
      'triple_value'     => new sfWidgetFormInput(),
      'created_at'       => new sfWidgetFormDateTime(),
      'updated_at'       => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorDoctrineChoice(array('model' => 'Tag', 'column' => 'id', 'required' => false)),
      'name'             => new sfValidatorString(array('max_length' => 100)),
      'is_visible'       => new sfValidatorBoolean(),
      'triple_namespace' => new sfValidatorString(array('max_length' => 30)),
      'triple_predicate' => new sfValidatorString(array('max_length' => 30)),
      'triple_value'     => new sfValidatorString(array('max_length' => 100)),
      'created_at'       => new sfValidatorDateTime(array('required' => false)),
      'updated_at'       => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('tag[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Tag';
  }

}