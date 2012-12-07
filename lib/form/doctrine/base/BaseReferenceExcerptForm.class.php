<?php

/**
 * ReferenceExcerpt form base class.
 *
 * @package    form
 * @subpackage reference_excerpt
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseReferenceExcerptForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'reference_id' => new sfWidgetFormDoctrineSelect(array('model' => 'Reference', 'add_empty' => false)),
      'body'         => new sfWidgetFormTextarea(),
      'created_at'   => new sfWidgetFormDateTime(),
      'updated_at'   => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorDoctrineChoice(array('model' => 'ReferenceExcerpt', 'column' => 'id', 'required' => false)),
      'reference_id' => new sfValidatorDoctrineChoice(array('model' => 'Reference')),
      'body'         => new sfValidatorString(),
      'created_at'   => new sfValidatorDateTime(array('required' => false)),
      'updated_at'   => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('reference_excerpt[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ReferenceExcerpt';
  }

}