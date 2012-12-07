<?php

class AddBulkForm extends sfForm
{
  public function configure()
  { 
    $this->setWidgets(array(
       'file'        => new sfWidgetFormInputFile(array(), array('size' => 30)),
       'add_method' => new sfWidgetFormInputHidden(array(), array('value' => true)),
       'verify_method' => new sfWidgetFormInputHidden(array(), array('value' => true)),
       'confirmed_names' => new sfWidgetFormInputHidden(array(), array('value' => true)),
       'relationship_category_all' => new sfWidgetFormInputHidden(array(), array('value' => true)),
       'relationship_category_one' => new sfWidgetFormInputHidden(array(), array('value' => true))
     ));
    
    $this->setValidators(array(
      'file'        => new sfValidatorFile(array('required' => false)),
      'add_method' => new sfValidatorChoice(array('choices' => array('scrape','summary','upload','text','db_search'), 'required' => false)),
      'verify_method' => new sfValidatorChoice(array('choices' => array('enmasse','onebyone'), 'required' => false)),
      'default_type' => new sfValidatorChoice(array('choices' => array('Person','Org'), 'required' => false)),
      'manual_names' => new sfValidatorString(array('required' => false)),
      'confirmed_names' => new sfValidatorPass(array('required' => false)),
      'relationship_category_all' => new sfValidatorPass(array('required' => false)),
      'relationship_category_one' => new sfValidatorPass(array('required' => false))
    ));
    
    
    $this->validatorSchema->setOption("allow_extra_fields", true);
    
    $this->validatorSchema->setPostValidator(
      new sfValidatorCallback(array('callback' => array($this, 'checkValues')))
    );
    
    

  }
  
  public function bind(array $taintedValues = null, array $taintedFiles = null)
  {
    parent::bind($taintedValues, $taintedFiles);
  }
  
  public function checkFiles($validator, $files)
  {
  
  }
  
  
  public function checkValues($validator, $values)
  {
    $errors = array();
    if (!isset($values['add_method']) && !isset($values['confirmed_names']))
    {
      throw new sfValidatorError($validator, 'You need to pick a method of adding data');
    }

    $add_method = $values['add_method'];
    if ($add_method == 'upload')
    {
      if(!isset($values['file']))
      {
        $errors[] = 'You need to choose a file to upload.';
      }
    }
    if ($add_method == 'text')
    {
      if(!isset($values['manual_names']) || trim($values['manual_names']) == '')
      {
        $errors[] = 'You need to enter some names below.';
      }
    }
    if (!in_array($add_method, array('scrape','summary')))
    {
      if(!$values['verify_method'])
      {
        $errors[] = 'You need to choose how to add the data.';
      }    
      else
      {
        if($values['verify_method'] == 'enmasse')
        {
          if(!$values['default_type'])
          {
            $errors[] = 'You need to choose a default entity type.';
          } 
          if(!$values['relationship_category_all'])
          {
            $errors[] = 'You need to choose a relationship category.';
          } 
        }
      }
    }
    if (count($errors))
    {
      $errors = implode('<br>',$errors);
      throw new sfValidatorError($validator, $errors);
    }
    return $values;
  }
  
}