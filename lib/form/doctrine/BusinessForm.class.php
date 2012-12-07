<?php

/**
 * Business form.
 *
 * @package    form
 * @subpackage Business
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class BusinessForm extends BaseBusinessForm
{
  public function configure()
  {/*
    $this->setWidgets(array(
      'industry_id' => new sfWidgetFormDoctrineSelect(array('model' => 'Industry', 'add_empty' => true, 'order_by' => array('name','asc')))
    ));

    $this->setValidators(array(
      'industry_id' => new sfValidatorDoctrineChoice(array('model' => 'Industry', 'required' => false))
    ));

    $this->widgetSchema->setLabels(array(
      'industry_id' => 'Industry'
    ));
    */
    $this->setWidgets(array());
    $this->setValidators(array());
    $this->widgetSchema->setLabels(array());

    $this->widgetSchema->setNameFormat('entity[%s]');
    
  }
}