<?php

/**
 * PoliticalFundraising form base class.
 *
 * @package    form
 * @subpackage political_fundraising
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BasePoliticalFundraisingForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'        => new sfWidgetFormInputHidden(),
      'fec_id'    => new sfWidgetFormInput(),
      'type_id'   => new sfWidgetFormDoctrineSelect(array('model' => 'PoliticalFundraisingType', 'add_empty' => true)),
      'state_id'  => new sfWidgetFormDoctrineSelect(array('model' => 'AddressState', 'add_empty' => true)),
      'entity_id' => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'        => new sfValidatorDoctrineChoice(array('model' => 'PoliticalFundraising', 'column' => 'id', 'required' => false)),
      'fec_id'    => new sfValidatorString(array('max_length' => 20, 'required' => false)),
      'type_id'   => new sfValidatorDoctrineChoice(array('model' => 'PoliticalFundraisingType', 'required' => false)),
      'state_id'  => new sfValidatorDoctrineChoice(array('model' => 'AddressState', 'required' => false)),
      'entity_id' => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
    ));

    $this->widgetSchema->setNameFormat('political_fundraising[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'PoliticalFundraising';
  }

}