<?php

/**
 * Position form.
 *
 * @package    form
 * @subpackage Position
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class PositionForm extends BasePositionForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'start_date'      => new sfWidgetFormInput(array(), array('size' => 10)),
      'end_date'        => new sfWidgetFormInput(array(), array('size' => 10)),
      'is_current'      => new LsWidgetFormSelectRadio(array('is_ternary' => true)),
      'description1'    => new sfWidgetFormInput(array(), array('size' => 20)),
      'is_board'        => new LsWidgetFormSelectRadio(array('is_ternary' => true)),
      'is_executive'    => new LsWidgetFormSelectRadio(array('is_ternary' => true)),
      'is_employee'     => new LsWidgetFormSelectRadio(array('is_ternary' => true)),
      'compensation'    => new sfWidgetFormInput(array(), array('size' => 10)),
      'notes'           => new sfWidgetFormTextarea(array(), array('cols' => 50, 'rows' => 5))
    ));

    $this->setValidators(array(
      'start_date'  => new LsValidatorDate(array('required' => false)),
      'end_date'    => new LsValidatorDate(array('required' => false)),
      'is_current'  => new sfValidatorBoolean(array('required' => false)),
      'description1' => new sfValidatorString(array('max_length' => 200, 'required' => false)),
      'is_board'        => new sfValidatorBoolean(array('required' => false)),
      'is_executive'    => new sfValidatorBoolean(array('required' => false)),
      'is_employee'     => new sfValidatorBoolean(array('required' => false)),
      'compensation'    => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setLabels(array(
      'description1' => 'Title',
      'is_board' => 'Board member',
      'is_executive' => 'Executive',
      'is_employee' => 'Employee'
    ));

    $this->widgetSchema->setHelps(array(
      'start_date' => LsFormHelp::$dateHelp,
      'end_date' => LsFormHelp::$dateHelp,
      'is_current' => 'is this relationship ongoing?',
      'is_employee' => 'is it a non-leadership staff position?',
      'compensation' => LsFormHelp::$numberHelp,
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