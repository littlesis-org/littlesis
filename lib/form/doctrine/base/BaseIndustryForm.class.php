<?php

/**
 * Industry form base class.
 *
 * @package    form
 * @subpackage industry
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseIndustryForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                     => new sfWidgetFormInputHidden(),
      'name'                   => new sfWidgetFormInput(),
      'context'                => new sfWidgetFormInput(),
      'code'                   => new sfWidgetFormInput(),
      'created_at'             => new sfWidgetFormDateTime(),
      'updated_at'             => new sfWidgetFormDateTime(),
      'business_list' => new sfWidgetFormDoctrineSelectMany(array('model' => 'Entity')),
    ));

    $this->setValidators(array(
      'id'                     => new sfValidatorDoctrineChoice(array('model' => 'Industry', 'column' => 'id', 'required' => false)),
      'name'                   => new sfValidatorString(array('max_length' => 100)),
      'context'                => new sfValidatorString(array('max_length' => 30, 'required' => false)),
      'code'                   => new sfValidatorString(array('max_length' => 30, 'required' => false)),
      'created_at'             => new sfValidatorDateTime(array('required' => false)),
      'updated_at'             => new sfValidatorDateTime(array('required' => false)),
      'business_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'Entity', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('industry[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Industry';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['business_list']))
    {
      $values = array();
      foreach ($this->object->Business as $obj)
      {
        $values[] = current($obj->identifier());
      }
      $this->object->clearRelated('Business');
      $this->setDefault('business_list', $values);
    }

  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $this->saveBusinessList($con);
  }

  public function saveBusinessList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['business_list']))
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
          ->from('BusinessIndustry r')
          ->where('r.industry_id = ?', current($this->object->identifier()))
          ->execute();

    $values = $this->getValue('business_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new BusinessIndustry();
        $obj->industry_id = current($this->object->identifier());
        $obj->business_id = $value;
        $obj->save();
      }
    }
  }

}