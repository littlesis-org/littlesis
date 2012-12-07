<?php

/**
 * PrivateSchool form base class.
 *
 * @package    form
 * @subpackage private_school
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BasePrivateSchoolForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'        => new sfWidgetFormInputHidden(),
      'endowment' => new sfWidgetFormInput(),
      'entity_id' => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'        => new sfValidatorDoctrineChoice(array('model' => 'PrivateSchool', 'column' => 'id', 'required' => false)),
      'endowment' => new sfValidatorInteger(),
      'entity_id' => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
    ));

    $this->widgetSchema->setNameFormat('private_school[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'PrivateSchool';
  }

}