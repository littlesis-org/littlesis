<?php

/**
 * Lobbying form.
 *
 * @package    form
 * @subpackage Lobbying
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class LobbyingForm extends BaseLobbyingForm
{
  public function configure()
  {
    $choices = RelationshipTable::getDescriptionsByCategoryId(RelationshipTable::LOBBYING_CATEGORY);

    $this->setWidgets(array(
      'start_date'      => new sfWidgetFormInput(array(), array('size' => 10)),
      'end_date'        => new sfWidgetFormInput(array(), array('size' => 10)),
      'description1'       => new sfWidgetFormSelect(array(
        'choices' => $choices
      )),
      'description_new' => new sfWidgetFormInput(array(), array('size' => 30)),
      'is_current'      => new LsWidgetFormSelectRadio(array('is_ternary' => true)),
      'amount'          => new sfWidgetFormInput(array(), array('size' => 10)),
      'notes'           => new sfWidgetFormTextarea(array(), array('cols' => 50, 'rows' => 5))
    ));

    $this->setValidators(array(
      'start_date'  => new LsValidatorDate(array('required' => false)),
      'end_date'    => new LsValidatorDate(array('required' => false)),
      'is_current'  => new sfValidatorBoolean(array('required' => false)),
      'description_new' => new sfValidatorString(array('max_length' => 200, 'required' => false)),
      'amount'      => new sfValidatorInteger(array('required' => false))
    ));

    $this->widgetSchema->setLabels(array(
      'description1' => 'Type'
    ));

    $this->widgetSchema->setHelps(array(
      'start_date' => LsFormHelp::$dateHelp,
      'end_date' => LsFormHelp::$dateHelp,
      'is_current' => 'is this relationship ongoing?',
      'amount' => LsFormHelp::$numberHelp,
      'notes' => LsFormHelp::$notesHelp
    ));

    $this->widgetSchema->setNameFormat('relationship[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->validatorSchema->setOption('allow_extra_fields', true);
    $this->validatorSchema->setOption('filter_extra_fields', false);
  }

  
  public function getModelName()
  {
    return 'Relationship';
  }


  public function updateDefaultsFromObject()
  {
    $data = $this->getObject()->getAllData();
    
    $this->setDefaults($data);    
  }  
}