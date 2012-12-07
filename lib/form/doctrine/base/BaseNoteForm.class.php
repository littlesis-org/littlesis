<?php

/**
 * Note form base class.
 *
 * @package    form
 * @subpackage note
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseNoteForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                 => new sfWidgetFormInputHidden(),
      'user_id'            => new sfWidgetFormDoctrineSelect(array('model' => 'sfGuardUser', 'add_empty' => false)),
      'title'              => new sfWidgetFormInput(),
      'body'               => new sfWidgetFormInput(),
      'body_raw'           => new sfWidgetFormInput(),
      'alerted_user_names' => new sfWidgetFormInput(),
      'alerted_user_ids'   => new sfWidgetFormInput(),
      'entity_ids'         => new sfWidgetFormInput(),
      'relationship_ids'   => new sfWidgetFormInput(),
      'lslist_ids'         => new sfWidgetFormInput(),
      'sfguardgroup_ids'   => new sfWidgetFormInput(),
      'network_ids'        => new sfWidgetFormInput(),
      'is_private'         => new sfWidgetFormInputCheckbox(),
      'created_at'         => new sfWidgetFormDateTime(),
      'updated_at'         => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                 => new sfValidatorDoctrineChoice(array('model' => 'Note', 'column' => 'id', 'required' => false)),
      'user_id'            => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser')),
      'title'              => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'body'               => new sfValidatorString(array('max_length' => 2000)),
      'body_raw'           => new sfValidatorString(array('max_length' => 1000)),
      'alerted_user_names' => new sfValidatorString(array('max_length' => 500, 'required' => false)),
      'alerted_user_ids'   => new sfValidatorString(array('max_length' => 500, 'required' => false)),
      'entity_ids'         => new sfValidatorString(array('max_length' => 200, 'required' => false)),
      'relationship_ids'   => new sfValidatorString(array('max_length' => 200, 'required' => false)),
      'lslist_ids'         => new sfValidatorString(array('max_length' => 200, 'required' => false)),
      'sfguardgroup_ids'   => new sfValidatorString(array('max_length' => 200, 'required' => false)),
      'network_ids'        => new sfValidatorString(array('max_length' => 200, 'required' => false)),
      'is_private'         => new sfValidatorBoolean(),
      'created_at'         => new sfValidatorDateTime(array('required' => false)),
      'updated_at'         => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('note[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Note';
  }

}