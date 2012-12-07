<?php

/**
 * RepresentativeDistrict form base class.
 *
 * @package    form
 * @subpackage representative_district
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseRepresentativeDistrictForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                => new sfWidgetFormInputHidden(),
      'representative_id' => new sfWidgetFormDoctrineSelect(array('model' => 'ElectedRepresentative', 'add_empty' => false)),
      'district_id'       => new sfWidgetFormDoctrineSelect(array('model' => 'PoliticalDistrict', 'add_empty' => false)),
      'created_at'        => new sfWidgetFormDateTime(),
      'updated_at'        => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorDoctrineChoice(array('model' => 'RepresentativeDistrict', 'column' => 'id', 'required' => false)),
      'representative_id' => new sfValidatorDoctrineChoice(array('model' => 'ElectedRepresentative')),
      'district_id'       => new sfValidatorDoctrineChoice(array('model' => 'PoliticalDistrict')),
      'created_at'        => new sfValidatorDateTime(array('required' => false)),
      'updated_at'        => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('representative_district[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'RepresentativeDistrict';
  }

}