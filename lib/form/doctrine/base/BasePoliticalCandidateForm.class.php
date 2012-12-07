<?php

/**
 * PoliticalCandidate form base class.
 *
 * @package    form
 * @subpackage political_candidate
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BasePoliticalCandidateForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                      => new sfWidgetFormInputHidden(),
      'is_federal'              => new sfWidgetFormInputCheckbox(),
      'is_state'                => new sfWidgetFormInputCheckbox(),
      'is_local'                => new sfWidgetFormInputCheckbox(),
      'pres_fec_id'             => new sfWidgetFormInput(),
      'senate_fec_id'           => new sfWidgetFormInput(),
      'house_fec_id'            => new sfWidgetFormInput(),
      'crp_id'                  => new sfWidgetFormInput(),
      'entity_id'               => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
      'candidate_district_list' => new sfWidgetFormDoctrineSelectMany(array('model' => 'PoliticalDistrict')),
    ));

    $this->setValidators(array(
      'id'                      => new sfValidatorDoctrineChoice(array('model' => 'PoliticalCandidate', 'column' => 'id', 'required' => false)),
      'is_federal'              => new sfValidatorBoolean(array('required' => false)),
      'is_state'                => new sfValidatorBoolean(array('required' => false)),
      'is_local'                => new sfValidatorBoolean(array('required' => false)),
      'pres_fec_id'             => new sfValidatorString(array('max_length' => 20, 'required' => false)),
      'senate_fec_id'           => new sfValidatorString(array('max_length' => 20, 'required' => false)),
      'house_fec_id'            => new sfValidatorString(array('max_length' => 20, 'required' => false)),
      'crp_id'                  => new sfValidatorString(array('max_length' => 20, 'required' => false)),
      'entity_id'               => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
      'candidate_district_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'PoliticalDistrict', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('political_candidate[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'PoliticalCandidate';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['candidate_district_list']))
    {
      $values = array();
      foreach ($this->object->CandidateDistrict as $obj)
      {
        $values[] = current($obj->identifier());
      }
      $this->object->clearRelated('CandidateDistrict');
      $this->setDefault('candidate_district_list', $values);
    }

  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $this->saveCandidateDistrictList($con);
  }

  public function saveCandidateDistrictList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['candidate_district_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $q = Doctrine_Query::create()
          ->delete()
          ->from('CandidateDistrict r')
          ->where('r.candidate_id = ?', current($this->object->identifier()))
          ->execute();

    $values = $this->getValue('candidate_district_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new CandidateDistrict();
        $obj->candidate_id = current($this->object->identifier());
        $obj->district_id = $value;
        $obj->save();
      }
    }
  }

}