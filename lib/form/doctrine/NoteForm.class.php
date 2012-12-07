<?php

/**
 * Note form.
 *
 * @package    form
 * @subpackage Note
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class NoteForm extends BaseNoteForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'body'       => new sfWidgetFormTextarea(),
      'is_private' => new sfWidgetFormInputCheckbox()
    ));

    $this->setValidators(array(
      'body'       => new sfValidatorString(array('max_length' => 1000), array(
        'required' => "You can't post an empty note!" 
      )),
      'is_private' => new sfValidatorBoolean(array('required' => false))
    ));

    $this->widgetSchema->setNameFormat('note[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->widgetSchema->setLabels(array(
      'body' => 'Note',
      'is_private' => 'Private'
    ));
    
    $this->widgetSchema->setHelps(array(
      'body' => '1000 chars max',
      'is_private' => 'private notes can only be seen by you and alerted analysts'
    ));
  }
}