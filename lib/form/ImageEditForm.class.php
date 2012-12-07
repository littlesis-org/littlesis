<?php

class ImageEditForm extends BaseImageForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'title'       => new sfWidgetFormInput(array(), array('size' => 30)),
      'caption'     => new sfWidgetFormTextarea(array(), array('cols' => 50, 'rows' => 5)),
      'is_featured' => new sfWidgetFormInputCheckbox(),
      'is_free'     => new sfWidgetFormInputCheckbox()
    ));

    $this->setValidators(array(
      'title'       => new sfValidatorString(array('max_length' => 100)),
      'caption'     => new sfValidatorString(array('required' => false)),
      'is_featured' => new sfValidatorBoolean(array('required' => false)),
      'is_free'     => new sfValidatorBoolean(array('required' => false))
    ));


    $this->widgetSchema->setLabels(array(
      'url' => 'Remote URL',
      'is_featured' => 'Put on profile'
    ));

    $this->widgetSchema->setHelps(array(
      'caption' => 'a short description'
    ));

    $this->widgetSchema->setNameFormat('image[%s]');
  }
}