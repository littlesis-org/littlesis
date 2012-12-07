<?php

/**
 * Donation form base class.
 *
 * @package    form
 * @subpackage donation
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseDonationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'bundler_id'      => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => true)),
      'relationship_id' => new sfWidgetFormDoctrineSelect(array('model' => 'Relationship', 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorDoctrineChoice(array('model' => 'Donation', 'column' => 'id', 'required' => false)),
      'bundler_id'      => new sfValidatorDoctrineChoice(array('model' => 'Entity', 'required' => false)),
      'relationship_id' => new sfValidatorDoctrineChoice(array('model' => 'Relationship')),
    ));

    $this->widgetSchema->setNameFormat('donation[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Donation';
  }

}