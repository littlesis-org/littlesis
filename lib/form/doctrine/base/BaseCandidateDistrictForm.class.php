<?php

/**
 * CandidateDistrict form base class.
 *
 * @package    form
 * @subpackage candidate_district
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseCandidateDistrictForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'candidate_id' => new sfWidgetFormDoctrineSelect(array('model' => 'PoliticalCandidate', 'add_empty' => false)),
      'district_id'  => new sfWidgetFormDoctrineSelect(array('model' => 'PoliticalDistrict', 'add_empty' => false)),
      'created_at'   => new sfWidgetFormDateTime(),
      'updated_at'   => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorDoctrineChoice(array('model' => 'CandidateDistrict', 'column' => 'id', 'required' => false)),
      'candidate_id' => new sfValidatorDoctrineChoice(array('model' => 'PoliticalCandidate')),
      'district_id'  => new sfValidatorDoctrineChoice(array('model' => 'PoliticalDistrict')),
      'created_at'   => new sfValidatorDateTime(array('required' => false)),
      'updated_at'   => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('candidate_district[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'CandidateDistrict';
  }

}