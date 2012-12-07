<?php

/**
 * Reference form.
 *
 * @package    form
 * @subpackage Reference
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class MassiveForm extends BaseReferenceForm
{
  protected $_selectObject = null;
  

  public function configure()
  {
    $this->setWidgets(array(
      'source'           => new sfWidgetFormInput(array(), array('size' => 50)),
      'name'             => new sfWidgetFormInput(array(), array('size' => 30))
    ));

    $this->setValidators(array(
      'source' => new sfValidatorRegex(array(
                              'pattern' => '/^http(s?)\:\/\/.{3,193}/i',
                              'required' => true),
                              array(
                                'invalid' => 'Source url must be valid url, eg http://www.opencongress.org/person/show/400120_rahm_emanuel')),
      //'source'           => new sfValidator(array('max_length' => 200)),
      'name'             => new sfValidatorString(array('max_length' => 100, 'required' => false)),
    ));


    $this->widgetSchema->setHelps(array(
      'source' => 'must be a valid link',
      'name' => 'short display name for this source',
    ));
    
    $this->widgetSchema->setNameFormat('massive[%s]');
    $this->widgetSchema->setLabels(array('source' => 'Source URL', 'name' => 'Source Name'));
    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->validatorSchema->setOption('allow_extra_fields', true);
    $this->validatorSchema->setOption('filter_extra_fields', false);
  }
  
}