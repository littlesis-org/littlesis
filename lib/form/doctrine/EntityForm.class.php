<?php

/**
 * Entity form.
 *
 * @package    form
 * @subpackage Entity
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class EntityForm extends BaseEntityForm
{

  public function configure()
  {
    $widgets = array(
      'blurb'       => new sfWidgetFormInput(array(), array('size' => 80, 'maxlength' => 200)),
      'summary'     => new sfWidgetFormTextarea(array(), array('cols' => '80', 'rows' => '6')),
      'start_date'  => new sfWidgetFormInput(array(), array('size' => 10)),
      'end_date'    => new sfWidgetFormInput(array(), array('size' => 10)),
      'is_current'  => new LsWidgetFormSelectRadio(array('is_ternary' => true)),
      'website'     => new sfWidgetFormInput(array(), array('size' => '30')),
      //'notes'           => new sfWidgetFormTextarea(array(), array('cols' => 50, 'rows' => 5))
    );      
    
    $validators = array(
      'start_date'  => new LsValidatorDate(array('required' => false)),
      'end_date'    => new LsValidatorDate(array('required' => false)),
      'is_current'  => new sfValidatorBoolean(array('required' => false)),
      'blurb'       => new sfValidatorString(array(
                      'max_length' => 200, 
                      'required' => false
                    ), array(
                      'invalid' => 'Blurbs are limited to 200 characters'
                    )),
      'summary'     => new sfValidatorString(array('required' => false)),
      'website'     => new sfValidatorAnd(
        array(
          new sfValidatorString(array('max_length' => 100, 'required' => false)),
          new sfValidatorUrl(array('required' => false))
        ),
        array('required' => false),
        array('invalid' => 'Website must be a valid URL no more than 100 characters long')
      )
    );  
    
    $entity = $this->getObject();
    $extensions = $entity->getExtensionsHavingFields();

    $labels = array(
      'start_date' => ($entity->getPrimaryExtension() == 'Person') ? 'Birth date' : 'Start date',
      'end_date' => ($entity->getPrimaryExtension() == 'Person') ? 'Death date' : 'End date',
      'is_current' => ($entity->getPrimaryExtension() == 'Person') ? 'Is alive' : 'Is active',
      'blurb' => 'Short description',
      'summary' => ($entity->getPrimaryExtension() == 'Person') ? 'Bio' : 'Summary',
      'notes' => 'Misc notes'
    );

    $helps = array(
      'start_date' => LsFormHelp::$dateHelp,
      'end_date' => LsFormHelp::$dateHelp,
      'blurb' => 'a short sentence or phrase',
      'summary' => '1 or 2 paras about this ' . strtolower($entity->getPrimaryExtension(true)),
      'notes' => LsFormHelp::$notesHelp
    );
    
    foreach ($extensions as $extension)
    {
      $class = $extension . 'Form';
      $form = new $class;
      //$widgets = array_merge($widgets, $form->getWidgetSchema()->getFields());
      $validators = array_merge($validators, $form->getValidatorSchema()->getFields());
      //$labels = array_merge($labels, $form->getWidgetSchema()->getLabels());
    }
    
    $this->setWidgets($widgets);
    $this->setValidators($validators);
    $this->widgetSchema->setLabels($labels);
    $this->widgetSchema->setHelps($helps);

    $this->validatorSchema->setOption('allow_extra_fields', true);
    $this->validatorSchema->setOption('filter_extra_fields', false);

    $this->widgetSchema->setNameFormat('entity[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
  }

  
  public function updateDefaultsFromObject()
  {
    $data = $this->getObject()->getAllData();
    
    $this->setDefaults($data);    
  }
  
  
  public function updateObject()
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }


    $data = LsRequestFilter::emptyStringsToNull($this->getValues());   

    $this->object->fromArray($data);


    unset($data['id']);

    foreach ($this->object->getExtensionObjects() as $object)
    {
      $object->fromArray($data);
    }

    return $this->object;
  }
  
  
  public function doSave($con = null)
  {
    $this->updateObject();
    
    $this->object->save($con);
  }


  public function save($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $this->doSave($con);

    return $this->object;
  }
  
  
  public function getFieldsWithLabels()
  {
    $labels = array();

    foreach ($this->getFormFieldSchema() as $name => $field)
    {
      if ($name != '_csrf_token')
      {
        $labels[$name] = $field->renderLabelName();
      }
    }


    $extensions = $this->object->getExtensionsHavingFields();
    
    foreach ($extensions as $extension)
    {
      $class = $extension . 'Form';
      $form = new $class;
      $labels = array_merge($labels, $form->getFieldsWithLabels());
    }


    return $labels;
  }
}