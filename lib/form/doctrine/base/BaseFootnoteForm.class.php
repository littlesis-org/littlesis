<?php

/**
 * Footnote form base class.
 *
 * @package    form
 * @subpackage footnote
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseFootnoteForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'user_id'      => new sfWidgetFormDoctrineSelect(array('model' => 'sfGuardUser', 'add_empty' => false)),
      'body'         => new sfWidgetFormTextarea(),
      'field'        => new sfWidgetFormInput(),
      'created_at'   => new sfWidgetFormDateTime(),
      'updated_at'   => new sfWidgetFormDateTime(),
      'object_model' => new sfWidgetFormInput(),
      'object_id'    => new sfWidgetFormInput(),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorDoctrineChoice(array('model' => 'Footnote', 'column' => 'id', 'required' => false)),
      'user_id'      => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser')),
      'body'         => new sfValidatorString(),
      'field'        => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'created_at'   => new sfValidatorDateTime(array('required' => false)),
      'updated_at'   => new sfValidatorDateTime(array('required' => false)),
      'object_model' => new sfValidatorString(array('max_length' => 50)),
      'object_id'    => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('footnote[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Footnote';
  }

}