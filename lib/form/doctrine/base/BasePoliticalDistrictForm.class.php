<?php

/**
 * PoliticalDistrict form base class.
 *
 * @package    form
 * @subpackage political_district
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BasePoliticalDistrictForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                           => new sfWidgetFormInputHidden(),
      'state_id'                     => new sfWidgetFormDoctrineSelect(array('model' => 'AddressState', 'add_empty' => true)),
      'federal_district'             => new sfWidgetFormInput(),
      'state_district'               => new sfWidgetFormInput(),
      'local_district'               => new sfWidgetFormInput(),
      'elected_representative_list' => new sfWidgetFormDoctrineSelectMany(array('model' => 'ElectedRepresentative')),
      'political_candidate_list'      => new sfWidgetFormDoctrineSelectMany(array('model' => 'PoliticalCandidate')),
    ));

    $this->setValidators(array(
      'id'                           => new sfValidatorDoctrineChoice(array('model' => 'PoliticalDistrict', 'column' => 'id', 'required' => false)),
      'state_id'                     => new sfValidatorDoctrineChoice(array('model' => 'AddressState', 'required' => false)),
      'federal_district'             => new sfValidatorString(array('max_length' => 2, 'required' => false)),
      'state_district'               => new sfValidatorString(array('max_length' => 2, 'required' => false)),
      'local_district'               => new sfValidatorString(array('max_length' => 2, 'required' => false)),
      'elected_representative_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'ElectedRepresentative', 'required' => false)),
      'political_candidate_list'      => new sfValidatorDoctrineChoiceMany(array('model' => 'PoliticalCandidate', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('political_district[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'PoliticalDistrict';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['elected_representative_list']))
    {
      $values = array();
      foreach ($this->object->ElectedRepresentative as $obj)
      {
        $values[] = current($obj->identifier());
      }
      $this->object->clearRelated('ElectedRepresentative');
      $this->setDefault('elected_representative_list', $values);
    }

    if (isset($this->widgetSchema['political_candidate_list']))
    {
      $values = array();
      foreach ($this->object->PoliticalCandidate as $obj)
      {
        $values[] = current($obj->identifier());
      }
      $this->object->clearRelated('PoliticalCandidate');
      $this->setDefault('political_candidate_list', $values);
    }

  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $this->saveElectedRepresentativeList($con);
    $this->savePoliticalCandidateList($con);
  }

  public function saveElectedRepresentativeList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['elected_representative_list']))
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
          ->from('RepresentativeDistrict r')
          ->where('r.district_id = ?', current($this->object->identifier()))
          ->execute();

    $values = $this->getValue('elected_representative_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new RepresentativeDistrict();
        $obj->district_id = current($this->object->identifier());
        $obj->representative_id = $value;
        $obj->save();
      }
    }
  }

  public function savePoliticalCandidateList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['political_candidate_list']))
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
          ->where('r.district_id = ?', current($this->object->identifier()))
          ->execute();

    $values = $this->getValue('political_candidate_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new CandidateDistrict();
        $obj->district_id = current($this->object->identifier());
        $obj->candidate_id = $value;
        $obj->save();
      }
    }
  }

}