<?php

/**
 * sfGuardGroup form.
 *
 * @package    form
 * @subpackage sfGuardGroup
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class sfGuardGroupForm extends PluginsfGuardGroupForm
{
  public function configure()
  {
    $widgets = array(
      //'name'          => new sfWidgetFormInput(array(), array('size' => 20)),
      'display_name'  => new sfWidgetFormInput(array(), array('size' => 50)),
      'blurb'         => new sfWidgetFormTextarea(array(), array('cols' => '50', 'rows' => '3')),
      'description'   => new sfWidgetFormTextarea(array(), array('cols' => '80', 'rows' => '12', 'class' => 'rich_text_editor')),
      'contest'   => new sfWidgetFormTextarea(array(), array('cols' => '80', 'rows' => '12', 'class' => 'rich_text_editor')),
      'is_private'    => new sfWidgetFormInputCheckbox()
    );      
    
    $validators = array(
      /*
      'name'        => new sfValidatorRegex(array(
                      'pattern' => '/^[a-z0-9\.]{4,30}$/i',
                      'required' => true
                    )),
      */
      'display_name'=> new sfValidatorString(array(
                      'max_length' => 255, 
                      'required' => true
                    ), array(
                      'invalid' => 'Names are limited to 200 characters'
                    )),
      'blurb'       => new sfValidatorString(array(
                      'max_length' => 255, 
                      'required' => false
                    ), array(
                      'invalid' => 'Descriptions are limited to 255 characters'
                    ))
    );  
    
    
    $this->setWidgets($widgets);
    $this->setValidators($validators);
    $this->widgetSchema->setLabels(array(
      'name' => 'Short name',
      'blurb' => 'Short description',
      'description' => 'Page text'
    ));
    $this->widgetSchema->setHelps(array(
      'name' => 'for URLs; 4-30 chars; only letters, numbers, and periods',
      'display_name' => 'for the group page and group listings',
      'description' => 'HTML is OK'
    ));


    $this->validatorSchema->setOption('allow_extra_fields', true);
    $this->validatorSchema->setOption('filter_extra_fields', false);

    $this->widgetSchema->setNameFormat('sf_guard_group[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
  }
}