<?php

/**
 * Family form.
 *
 * @package    form
 * @subpackage Family
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class FamilyForm extends BaseFamilyForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'start_date'      => new sfWidgetFormInput(array(), array('size' => 10)),
      'end_date'        => new sfWidgetFormInput(array(), array('size' => 10)),
      'is_current'      => new LsWidgetFormSelectRadio(array('is_ternary' => true)),
      'description1' => new sfWidgetFormInput(array(), array('size' => 20)),
      'description2' => new sfWidgetFormInput(array(), array('size' => 20)),
      //'is_nonbiological' => new LsWidgetFormSelectRadio(array('is_ternary' => true)),
      'notes'           => new sfWidgetFormTextarea(array(), array('cols' => 50, 'rows' => 5))
    ));

    $this->setValidators(array(
      'start_date'  => new LsValidatorDate(array('required' => false)),
      'end_date'    => new LsValidatorDate(array('required' => false)),
      'is_current'  => new sfValidatorBoolean(array('required' => false)),
      'description1' => new sfValidatorString(array('max_length' => 200, 'required' => false)),
      'description2' => new sfValidatorString(array('max_length' => 200, 'required' => false)),
      //'is_nonbiological' => new sfValidatorBoolean(array('required' => false)),
    ));

    $this->widgetSchema->setLabels(array(
      //'is_nonbiological' => 'Nonbiological'
    ));

    $this->widgetSchema->setHelps(array(
      'start_date' => LsFormHelp::$dateHelp,
      'end_date' => LsFormHelp::$dateHelp,
      'is_current' => 'is this relationship ongoing?',
      //'is_nonbiological' => 'eg: stepchild, father-in-law',
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