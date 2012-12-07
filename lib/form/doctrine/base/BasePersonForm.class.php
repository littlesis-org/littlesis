<?php

/**
 * Person form base class.
 *
 * @package    form
 * @subpackage person
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BasePersonForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'name_last'      => new sfWidgetFormInput(),
      'name_first'     => new sfWidgetFormInput(),
      'name_middle'    => new sfWidgetFormInput(),
      'name_prefix'    => new sfWidgetFormInput(),
      'name_suffix'    => new sfWidgetFormInput(),
      'name_nick'      => new sfWidgetFormInput(),
      'birthplace'     => new sfWidgetFormInput(),
      'gender_id'      => new sfWidgetFormDoctrineSelect(array('model' => 'Gender', 'add_empty' => true)),
      'party_id'       => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => true)),
      'is_independent' => new sfWidgetFormInputCheckbox(),
      'net_worth'      => new sfWidgetFormInput(),
      'entity_id'      => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorDoctrineChoice(array('model' => 'Person', 'column' => 'id', 'required' => false)),
      'name_last'      => new sfValidatorString(array('max_length' => 50)),
      'name_first'     => new sfValidatorString(array('max_length' => 50)),
      'name_middle'    => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'name_prefix'    => new sfValidatorString(array('max_length' => 30, 'required' => false)),
      'name_suffix'    => new sfValidatorString(array('max_length' => 30, 'required' => false)),
      'name_nick'      => new sfValidatorString(array('max_length' => 30, 'required' => false)),
      'birthplace'     => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'gender_id'      => new sfValidatorDoctrineChoice(array('model' => 'Gender', 'required' => false)),
      'party_id'       => new sfValidatorDoctrineChoice(array('model' => 'Entity', 'required' => false)),
      'is_independent' => new sfValidatorBoolean(array('required' => false)),
      'net_worth'      => new sfValidatorInteger(array('required' => false)),
      'entity_id'      => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
    ));

    $this->widgetSchema->setNameFormat('person[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Person';
  }

}