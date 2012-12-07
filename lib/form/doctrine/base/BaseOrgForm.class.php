<?php

/**
 * Org form base class.
 *
 * @package    form
 * @subpackage org
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseOrgForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                => new sfWidgetFormInputHidden(),
      'name'              => new sfWidgetFormInput(),
      'name_nick'         => new sfWidgetFormInput(),
      'employees'         => new sfWidgetFormInput(),
      'revenue'           => new sfWidgetFormInput(),
      'fedspending_id'    => new sfWidgetFormInput(),
      'lda_registrant_id' => new sfWidgetFormInput(),
      'entity_id'         => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorDoctrineChoice(array('model' => 'Org', 'column' => 'id', 'required' => false)),
      'name'              => new sfValidatorString(array('max_length' => 200)),
      'name_nick'         => new sfValidatorString(array('max_length' => 200)),
      'employees'         => new sfValidatorInteger(array('required' => false)),
      'revenue'           => new sfValidatorInteger(array('required' => false)),
      'fedspending_id'    => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'lda_registrant_id' => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'entity_id'         => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
    ));

    $this->widgetSchema->setNameFormat('org[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Org';
  }

}