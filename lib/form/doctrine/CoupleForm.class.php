<?php

class CoupleForm extends BaseCoupleForm
{
  public function configure()
  {
    $this->setWidgets(array(
    ));

    $this->setValidators(array(
    ));

    $this->widgetSchema->setLabels(array(
    ));
    
    $this->widgetSchema->setHelps(array(
    ));

    $this->widgetSchema->setNameFormat('entity[%s]');
  }
}