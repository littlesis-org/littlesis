<?php

class CsvUploadForm extends sfForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'file'        => new sfWidgetFormInputFile(array(), array('size' => 30)),
      'file_type' => new sfWidgetFormInputHidden(array(), array('value' => true))
    ));

    $this->setValidators(array(
      'file'        => new sfValidatorFile(array('required' => true)),
      'file_type'     => new sfValidatorBoolean(array('required' => false))
    ));
    
    $this->widgetSchema->setLabels(array(
      'file' => 'CSV File'
    ));
    
    $this->widgetSchema->setHelps(array(
      'file' => 'must be in .csv format. the first row must be a header row including a <i>name</i> field and at least 2 rows',
      'is_featured' => 'put this image on the profile page'
    ));
    
    $this->validatorSchema->setOption("allow_extra_fields", true);

    $this->widgetSchema->setNameFormat('csv[%s]');
  }
  
  public function bind(array $taintedValues = null, array $taintedFiles = null)
  {
    parent::bind($taintedValues, $taintedFiles);

  }
}