<?php

/**
 * Education form.
 *
 * @package    form
 * @subpackage Education
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class EducationForm extends BaseEducationForm
{
  public function configure()
  {
    $choices = array(
      '' => '',
      'Graduate' => 'Graduate',
      'Undergraduate' => 'Undergraduate',
      'High School' => 'High School'
    );
    
    $this->setWidgets(array(
      'start_date'      => new sfWidgetFormInput(array(), array('size' => 10)),
      'end_date'        => new sfWidgetFormInput(array(), array('size' => 10)),
      'is_current'      => new LsWidgetFormSelectRadio(array('is_ternary' => true)),
      'description1'       => new sfWidgetFormSelect(array(
        'choices' => $choices
      )),
      'degree_id'       => new sfWidgetFormDoctrineSelect(array('model' => 'Degree', 'add_empty' => true, 'order_by' => array('name', 'ASC'))),
      'field'           => new sfWidgetFormInput(array(), array('size' => 20)),
      'is_dropout'      => new LsWidgetFormSelectRadio(array('is_ternary' => true)),
      'notes'           => new sfWidgetFormTextarea(array(), array('cols' => 50, 'rows' => 5))
    ));

    $this->setValidators(array(
      'start_date'  => new LsValidatorDate(array('required' => false)),
      'end_date'    => new LsValidatorDate(array('required' => false)),
      'is_current'  => new sfValidatorBoolean(array('required' => false)),
      'description1'    => new sfValidatorChoice(array('required' => false, 'choices' => $choices)),
      'degree_id'       => new sfValidatorDoctrineChoice(array('model' => 'Degree', 'required' => false)),
      'field'           => new sfValidatorString(array('max_length' => 30, 'required' => false)),
      'is_dropout'      => new sfValidatorBoolean(array('required' => false)),
    ));

    $this->widgetSchema->setLabels(array(
      'description1' => 'Type',
      'degree_id' => 'Degree',
      'is_dropout' => 'Dropout'
    ));

    $this->widgetSchema->setHelps(array(
      'start_date' => LsFormHelp::$dateHelp,
      'end_date' => LsFormHelp::$dateHelp,
      'is_current' => 'is this relationship ongoing?',
      'field' => 'eg: Chemistry',
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