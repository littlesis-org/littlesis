<?php

/**
 * RelationshipCategory form base class.
 *
 * @package    form
 * @subpackage relationship_category
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseRelationshipCategoryForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                   => new sfWidgetFormInputHidden(),
      'name'                 => new sfWidgetFormInput(),
      'display_name'         => new sfWidgetFormInput(),
      'default_description'  => new sfWidgetFormInput(),
      'entity1_requirements' => new sfWidgetFormInput(),
      'entity2_requirements' => new sfWidgetFormInput(),
      'has_fields'           => new sfWidgetFormInputCheckbox(),
      'created_at'           => new sfWidgetFormDateTime(),
      'updated_at'           => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                   => new sfValidatorDoctrineChoice(array('model' => 'RelationshipCategory', 'column' => 'id', 'required' => false)),
      'name'                 => new sfValidatorString(array('max_length' => 30)),
      'display_name'         => new sfValidatorString(array('max_length' => 30)),
      'default_description'  => new sfValidatorString(array('max_length' => 50)),
      'entity1_requirements' => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'entity2_requirements' => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'has_fields'           => new sfValidatorBoolean(),
      'created_at'           => new sfValidatorDateTime(array('required' => false)),
      'updated_at'           => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('relationship_category[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'RelationshipCategory';
  }

}