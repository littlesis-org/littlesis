<?php

class OnePercentSearchForm extends sfForm
{
  public function configure()
  {

    $this->setWidgets(array(
      'url'  => new sfWidgetFormInput(array(), array('size' => 30)),
      'text'   => new sfWidgetFormTextarea(array(), array('cols' => 50, 'rows' => 3)),
    ));

    $this->setValidators(array(
      'url' => new sfValidatorRegex(array(
                              'pattern' => '/^http(s?)\:\/\/.{3,193}/i','required' => false),
                              array(
                                'invalid' => 'Source url must be valid url, eg http://www.opencongress.org/person/show/400120_rahm_emanuel'))
    ));

    $this->widgetSchema->setLabels(array(
      'url' => 'Url',
      'text' => 'Raw Text'
    ));
    $this->widgetSchema->setHelps(array(
      'text' => 'if entering a list of names, separate them with semi-colons, e.g. Robert Rubin; Larry Summers; Alan Greenspan'
    ));
    
    $this->widgetSchema->setNameFormat('onepercent[%s]');

    $this->validatorSchema->setOption('allow_extra_fields', true);
    $this->validatorSchema->setOption('filter_extra_fields', false);
  }
}