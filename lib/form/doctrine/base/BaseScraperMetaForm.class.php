<?php

/**
 * ScraperMeta form base class.
 *
 * @package    form
 * @subpackage scraper_meta
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseScraperMetaForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'scraper'    => new sfWidgetFormInput(),
      'namespace'  => new sfWidgetFormInput(),
      'predicate'  => new sfWidgetFormInput(),
      'value'      => new sfWidgetFormInput(),
      'created_at' => new sfWidgetFormDateTime(),
      'updated_at' => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorDoctrineChoice(array('model' => 'ScraperMeta', 'column' => 'id', 'required' => false)),
      'scraper'    => new sfValidatorString(array('max_length' => 100)),
      'namespace'  => new sfValidatorString(array('max_length' => 50)),
      'predicate'  => new sfValidatorString(array('max_length' => 50)),
      'value'      => new sfValidatorString(array('max_length' => 50)),
      'created_at' => new sfValidatorDateTime(array('required' => false)),
      'updated_at' => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('scraper_meta[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ScraperMeta';
  }

}