<?php

/**
 * PoliticalCandidate form.
 *
 * @package    form
 * @subpackage PoliticalCandidate
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class PoliticalCandidateForm extends BasePoliticalCandidateForm
{
  public function configure()
  {
    /*
    $this->setWidgets(array(
      'is_federal'    => new LsWidgetFormSelectRadio(array('is_ternary' => true)),
      'is_state'      => new LsWidgetFormSelectRadio(array('is_ternary' => true)),
      'is_local'      => new LsWidgetFormSelectRadio(array('is_ternary' => true)),
      'pres_fec_id'   => new sfWidgetFormInput(array(), array('size' => 10)),
      'senate_fec_id' => new sfWidgetFormInput(array(), array('size' => 10)),
      'house_fec_id'  => new sfWidgetFormInput(array(), array('size' => 10))
    ));

    $this->setValidators(array(
      'is_federal'    => new sfValidatorBoolean(array('required' => false)),
      'is_state'      => new sfValidatorBoolean(array('required' => false)),
      'is_local'      => new sfValidatorBoolean(array('required' => false)),
      'pres_fec_id'   => new sfValidatorString(array('max_length' => 20, 'required' => false), array(
        'invalid' => 'President FEC ID can be 20 characters maximum'
      )),
      'senate_fec_id' => new sfValidatorString(array('max_length' => 20, 'required' => false), array(
        'invalid' => 'Senate FEC ID can be 20 characters maximum'      
      )),
      'house_fec_id'  => new sfValidatorString(array('max_length' => 20, 'required' => false), array(
        'invalid' => 'House FEC ID can be 20 characters maximum'
      ))
    ));

    $this->widgetSchema->setLabels(array(
      'is_federal' => 'Federal candidate',
      'is_state' => 'State candidate',
      'is_local' => 'Local candidate',
      'pres_fec_id' => 'President FEC ID',
      'senate_fec_id' => 'Senate FEC ID',
      'house_fec_id' => 'House FEC ID'
    ));
    */
    
    $this->setWidgets(array());
    $this->setValidators(array());
    $this->widgetSchema->setLabels(array());
    
    $this->widgetSchema->setNameFormat('entity[%s]');
  }
}