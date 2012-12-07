<?php

/**
 * Business form base class.
 *
 * @package    form
 * @subpackage business
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseBusinessForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                     => new sfWidgetFormInputHidden(),
      'annual_profit'          => new sfWidgetFormInput(),
      'entity_id'              => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
      'industry_list' => new sfWidgetFormDoctrineSelectMany(array('model' => 'Industry')),
    ));

    $this->setValidators(array(
      'id'                     => new sfValidatorDoctrineChoice(array('model' => 'Business', 'column' => 'id', 'required' => false)),
      'annual_profit'          => new sfValidatorInteger(array('required' => false)),
      'entity_id'              => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
      'industry_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'Industry', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('business[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Business';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['industry_list']))
    {
      $values = array();
      foreach ($this->object->Industry as $obj)
      {
        $values[] = current($obj->identifier());
      }
      $this->object->clearRelated('Industry');
      $this->setDefault('industry_list', $values);
    }

  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $this->saveIndustryList($con);
  }

  public function saveIndustryList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['industry_list']))
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
          ->where('r.business_id = ?', current($this->object->identifier()))
          ->execute();

    $values = $this->getValue('industry_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new BusinessIndustry();
        $obj->business_id = current($this->object->identifier());
        $obj->industry_id = $value;
        $obj->save();
      }
    }
  }

}