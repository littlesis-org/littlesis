<?php

/**
 * ElectedRepresentative form.
 *
 * @package    form
 * @subpackage ElectedRepresentative
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class ElectedRepresentativeForm extends BaseElectedRepresentativeForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'bioguide_id'                  => new sfWidgetFormInput(array(), array('size' => 10)),
      'govtrack_id'                  => new sfWidgetFormInput(array(), array('size' => 10)),
      'crp_id'                       => new sfWidgetFormInput(array(), array('size' => 10)),
      'pvs_id'                       => new sfWidgetFormInput(array(), array('size' => 10)),
      'watchdog_id'                  => new sfWidgetFormInput(array(), array('size' => 10))
    ));

    $this->setValidators(array(
      'bioguide_id'                  => new sfValidatorString(array('max_length' => 20, 'required' => false)),
      'govtrack_id'                  => new sfValidatorString(array('max_length' => 20, 'required' => false)),
      'crp_id'                       => new sfValidatorString(array('max_length' => 20, 'required' => false)),
      'pvs_id'                       => new sfValidatorString(array('max_length' => 20, 'required' => false)),
      'watchdog_id'                  => new sfValidatorString(array('max_length' => 50, 'required' => false))
    ));

    $this->widgetSchema->setLabels(array(
      'bioguide_id' => 'Congress bio ID',
      'govtrack_id' => 'GovTrack ID',
      'crp_id' => 'CRP ID',
      'pvs_id' => 'Project VoteSmart ID',
      'watchdog_id' => 'Watchdog.net ID'
    ));

    $this->widgetSchema->setNameFormat('entity[%s]');    
  }
}