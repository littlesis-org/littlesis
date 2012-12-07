<?php

/**
 * RelationshipDetail form base class.
 *
 * @package    form
 * @subpackage relationship_detail
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseRelationshipDetailForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'description1' => new sfWidgetFormInput(),
      'description2' => new sfWidgetFormInput(),
      'abbreviation' => new sfWidgetFormInput(),
      'category_id'  => new sfWidgetFormDoctrineSelect(array('model' => 'RelationshipCategory', 'add_empty' => false)),
      'created_at'   => new sfWidgetFormDateTime(),
      'updated_at'   => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorDoctrineChoice(array('model' => 'RelationshipDetail', 'column' => 'id', 'required' => false)),
      'description1' => new sfValidatorString(array('max_length' => 50)),
      'description2' => new sfValidatorString(array('max_length' => 50)),
      'abbreviation' => new sfValidatorString(array('max_length' => 50)),
      'category_id'  => new sfValidatorDoctrineChoice(array('model' => 'RelationshipCategory')),
      'created_at'   => new sfValidatorDateTime(array('required' => false)),
      'updated_at'   => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('relationship_detail[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'RelationshipDetail';
  }

}