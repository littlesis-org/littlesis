<?php

/**
 * Entity form.
 *
 * @package    form
 * @subpackage Entity
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class EntityAddForm extends BaseEntityForm
{
  public function configure()
  {
    $widgets = array(
      'name'        => new sfWidgetFormInput(array(), array('size' => 50, 'maxlength' => 200)),
      'blurb'       => new sfWidgetFormInput(array(), array('size' => 50, 'maxlength' => 200))
    );      
    
    $validators = array(
      'name'        => new sfValidatorString(array(
                      'max_length' => 200, 
                      'required' => true
                    ), array(
                      'invalid' => 'Name must include a first and last name'
                    )),
      'blurb'       => new sfValidatorString(array(
                      'max_length' => 200, 
                      'required' => false
                    ), array(
                      'invalid' => 'Blurbs are limited to 200 characters'
                    )),
    );  
    
    $helps = array(
      'blurb' => 'a short sentence or phrase'
    );
       
    $labels = array(
      'blurb' => 'Short description'
    );
        
    $this->setWidgets($widgets);
    $this->setValidators($validators);
    $this->widgetSchema->setHelps($helps);
    $this->widgetSchema->setLabels($labels);

    $this->validatorSchema->setOption('allow_extra_fields', true);
    $this->validatorSchema->setOption('filter_extra_fields', false);

    $this->widgetSchema->setNameFormat('entity[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
  }
 
 
  public function setNameHelp($str)
  {
    $this->widgetSchema->setHelp('name', $str);
  }
}