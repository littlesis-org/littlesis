<?php

/**
 * CampaignCommittee form base class.
 *
 * @package    form
 * @subpackage campaign_committee
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseCampaignCommitteeForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'        => new sfWidgetFormInputHidden(),
      'fec_id'    => new sfWidgetFormInput(),
      'entity_id' => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'        => new sfValidatorDoctrineChoice(array('model' => 'CampaignCommittee', 'column' => 'id', 'required' => false)),
      'fec_id'    => new sfValidatorString(array('max_length' => 20, 'required' => false)),
      'entity_id' => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
    ));

    $this->widgetSchema->setNameFormat('campaign_committee[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'CampaignCommittee';
  }

}