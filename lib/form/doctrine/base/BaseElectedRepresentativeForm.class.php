<?php

/**
 * ElectedRepresentative form base class.
 *
 * @package    form
 * @subpackage elected_representative
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseElectedRepresentativeForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                           => new sfWidgetFormInputHidden(),
      'bioguide_id'                  => new sfWidgetFormInput(),
      'govtrack_id'                  => new sfWidgetFormInput(),
      'crp_id'                       => new sfWidgetFormInput(),
      'pvs_id'                       => new sfWidgetFormInput(),
      'watchdog_id'                  => new sfWidgetFormInput(),
      'entity_id'                    => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
      'elected_district_list' => new sfWidgetFormDoctrineSelectMany(array('model' => 'PoliticalDistrict')),
    ));

    $this->setValidators(array(
      'id'                           => new sfValidatorDoctrineChoice(array('model' => 'ElectedRepresentative', 'column' => 'id', 'required' => false)),
      'bioguide_id'                  => new sfValidatorString(array('max_length' => 20, 'required' => false)),
      'govtrack_id'                  => new sfValidatorString(array('max_length' => 20, 'required' => false)),
      'crp_id'                       => new sfValidatorString(array('max_length' => 20, 'required' => false)),
      'pvs_id'                       => new sfValidatorString(array('max_length' => 20, 'required' => false)),
      'watchdog_id'                  => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'entity_id'                    => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
      'elected_district_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'PoliticalDistrict', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('elected_representative[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ElectedRepresentative';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['elected_district_list']))
    {
      $values = array();
      foreach ($this->object->ElectedDistrict as $obj)
      {
        $values[] = current($obj->identifier());
      }
      $this->object->clearRelated('ElectedDistrict');
      $this->setDefault('elected_district_list', $values);
    }

  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $this->saveElectedDistrictList($con);
  }

  public function saveElectedDistrictList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['elected_district_list']))
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
          ->where('r.representative_id = ?', current($this->object->identifier()))
          ->execute();

    $values = $this->getValue('elected_district_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new RepresentativeDistrict();
        $obj->representative_id = current($this->object->identifier());
        $obj->district_id = $value;
        $obj->save();
      }
    }
  }

}