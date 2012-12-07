<?php

/**
 * Relationship form.
 *
 * @package    form
 * @subpackage Relationship
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class RelationshipForm extends BaseRelationshipForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'start_date'  => new sfWidgetFormInput(),
      'end_date'    => new sfWidgetFormInput(),
      'is_current'  => new LsWidgetFormSelectRadio(array('is_ternary' => true)),
      'notes'       => new sfWidgetFormTextarea()
    ));

    $this->setValidators(array(
      'start_date'  => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'end_date'    => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'is_current'  => new sfValidatorBoolean(array('required' => false)),
      'notes'       => new sfValidatorString(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('relationship[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
  }
}