<?php

class ImageUploadForm extends sfForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'file'        => new sfWidgetFormInputFile(array(), array('size' => 30)),
      'url'         => new sfWidgetFormInput(array(), array('size' => 50)),
      'title'       => new sfWidgetFormInput(array(), array('size' => 30)),
      'caption'     => new sfWidgetFormTextarea(array(), array('cols' => 50, 'rows' => 5)),
      'is_featured' => new sfWidgetFormInputCheckbox(),
      'is_free'     => new sfWidgetFormInputCheckbox()
    ));

    $this->setValidators(array(
      'file'        => new sfValidatorFile(array('required' => false)),
      'url'         => new sfValidatorUrl(array('required' => false), array(
        'invalid' => 'Remote URL must be valid and working'
      )),
      'title'       => new sfValidatorString(array('max_length' => 100)),
      'caption'     => new sfValidatorString(array('required' => false)),
      'is_featured' => new sfValidatorBoolean(array('required' => false)),
      'is_free'     => new sfValidatorBoolean(array('required' => false))
    ));


    $this->widgetSchema->setLabels(array(
      'url' => 'Remote URL',
      'is_featured' => 'Featured'
    ));

    $this->widgetSchema->setHelps(array(
      'caption' => 'a short description',
      'is_featured' => 'put this image on the profile page'
    ));

    $this->widgetSchema->setNameFormat('image[%s]');
  }
  
  public function bind(array $taintedValues = null, array $taintedFiles = null)
  {
    parent::bind($taintedValues, $taintedFiles);

    if (!isset($taintedValues['url']) && !$taintedFiles['file']['name'])
    {
      $v = new sfValidatorString(array(), array(
        'required' => 'You must either upload a file or specify a remote URL'
      ));
      $this->errorSchema->addError(new sfValidatorError($v, 'required'), 'file');
    }
  }
}