<?php

/**
 * Ownership form.
 *
 * @package    form
 * @subpackage Ownership
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class OwnershipForm extends BaseOwnershipForm
{
  public function configure()
  {
    $choices = RelationshipTable::getDescriptionsByCategoryId(RelationshipTable::OWNERSHIP_CATEGORY);

    $this->setWidgets(array(
      'start_date'      => new sfWidgetFormInput(array(), array('size' => 10)),
      'end_date'        => new sfWidgetFormInput(array(), array('size' => 10)),
      'is_current'      => new LsWidgetFormSelectRadio(array('is_ternary' => true)),
      'description1'       => new sfWidgetFormSelect(array(
        'choices' => $choices
      )),
      'description_new' => new sfWidgetFormInput(array(), array('size' => 30)),
      'percent_stake'   => new sfWidgetFormInput(array(), array('size' => 3)),
      'shares'          => new sfWidgetFormInput(array(), array('size' => 10)),
      'notes'           => new sfWidgetFormTextarea(array(), array('cols' => 50, 'rows' => 5))
    ));

    $this->setValidators(array(
      'start_date'  => new LsValidatorDate(array('required' => false)),
      'end_date'    => new LsValidatorDate(array('required' => false)),
      'is_current'  => new sfValidatorBoolean(array('required' => false)),
      'description_new' => new sfValidatorString(array('max_length' => 200, 'required' => false)),
      'percent_stake'   => new sfValidatorInteger(array('required' => false)),
      'shares'          => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setLabels(array(
      'description1' => 'Title'
    ));

    $this->widgetSchema->setHelps(array(
      'start_date' => LsFormHelp::$dateHelp,
      'end_date' => LsFormHelp::$dateHelp,
      'is_current' => 'is this relationship ongoing?',
      'percent_stake' => 'eg: 15 or 100',
      'shares' => LsFormHelp::$numberHelp,
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