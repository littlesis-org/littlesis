<?php

/**
 * Reference form base class.
 *
 * @package    form
 * @subpackage reference
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseReferenceForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'fields'           => new sfWidgetFormInput(),
      'name'             => new sfWidgetFormInput(),
      'source'           => new sfWidgetFormInput(),
      'source_detail'    => new sfWidgetFormInput(),
      'publication_date' => new sfWidgetFormInput(),
      'created_at'       => new sfWidgetFormDateTime(),
      'updated_at'       => new sfWidgetFormDateTime(),
      'object_model'     => new sfWidgetFormInput(),
      'object_id'        => new sfWidgetFormDoctrineSelect(array('model' => 'Relationship', 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorDoctrineChoice(array('model' => 'Reference', 'column' => 'id', 'required' => false)),
      'fields'           => new sfValidatorString(array('max_length' => 200)),
      'name'             => new sfValidatorString(array('max_length' => 100)),
      'source'           => new sfValidatorString(array('max_length' => 200)),
      'source_detail'    => new sfValidatorString(array('max_length' => 50)),
      'publication_date' => new sfValidatorString(array('max_length' => 10)),
      'created_at'       => new sfValidatorDateTime(array('required' => false)),
      'updated_at'       => new sfValidatorDateTime(array('required' => false)),
      'object_model'     => new sfValidatorString(array('max_length' => 50)),
      'object_id'        => new sfValidatorDoctrineChoice(array('model' => 'Relationship')),
    ));

    $this->widgetSchema->setNameFormat('reference[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Reference';
  }

}