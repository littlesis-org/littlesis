<?php

/**
 * Reference form.
 *
 * @package    form
 * @subpackage Reference
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class ReferenceForm extends BaseReferenceForm
{
  protected $_selectObject = null;
  protected $_sourceHelp = 'original sources only &mdash; <a href="http://blog.littlesis.org/2009/01/07/references-and-wikipedia/" target="_blank">not Wikipedia</a>';
  protected $_nameHelp = 'detailed, eg: <em>Washington Post - BofA to buy Merrill</em> or <em>Carlyle Group - David Rubenstein bio</em>';
    

  public function configure()
  {
    $this->setWidgets(array(
      'nosource'         => new sfWidgetFormInputCheckbox(),
      'source'           => new sfWidgetFormInput(array(), array('size' => 50)),
      'name'             => new sfWidgetFormInput(array(), array('size' => 50)),
      'source_detail'    => new sfWidgetFormInput(),
      'publication_date' => new sfWidgetFormInput(array(), array('size' => 10)),
      'excerpt'          => new sfWidgetFormTextarea(array(), array('cols' => 50, 'rows' => 10))
    ));

    $this->setValidators(array(
      'nosource'         => new sfValidatorBoolean(array('required' => false)),
      'source' => new sfValidatorRegex(array(
                              'pattern' => '/^http(s?)\:\/\/.{3,193}/i',
                              'required' => true),
                              array(
                                'invalid' => 'Source url must be valid url, eg http://www.opencongress.org/person/show/400120_rahm_emanuel')),
      //'source'           => new sfValidator(array('max_length' => 200)),
      'name'             => new sfValidatorString(array('max_length' => 100, 'required' => true)),
      'source_detail'    => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'publication_date' => new LsValidatorDate(array('required' => false)),
      'excerpt'          => new sfValidatorString(array('required' => false))
    ));

    $this->widgetSchema->setLabels(array(
      'source' => 'Source URL',
      'name' => 'Display name',
      'source_detail' => 'Location in source'
    ));

    $this->widgetSchema->setHelps(array(
      'source' => $this->_sourceHelp,
      'name' => $this->_nameHelp,
      'source_detail' => 'eg: Chapter 5, pp 75-80',
      'publication_date' => LsFormHelp::$dateHelp,
      'excerpt' => 'relevant section(s) of the source text'
    ));
    
    $this->widgetSchema->setNameFormat('reference[%s]');
    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->validatorSchema->setOption('allow_extra_fields', true);
    $this->validatorSchema->setOption('filter_extra_fields', false);
  }
  
  
  public function setSelectObject(Doctrine_Record $object, $autoSelect=false, $contactInfo = false)
  {
    $this->_selectObject = $object;  

    if ($object instanceOf Relationship)
    {
      //when editing a Relationship, show all Relationship references
      //plus all references for Entity1 and Entity2
      //plus 5 most recent references for Entity1's Relationships
      //plus 5 most recent references for Entity2's Relationships
      $refs = RelationshipTable::getReferencesForRelationship($object);
    }
    else if ($object instanceOf Entity && $contactInfo == true)
    {
    	$refs = EntityTable::getContactReferencesById($object->id);
    }
    else
    {
      $refs = $object->getReferencesByFields(null, Doctrine::HYDRATE_ARRAY);

      if ($object instanceOf Entity)
      {
        //when editing an Entity, show all Entity references 
        //plus 10 most recent references for the Entity's Relationships
        $refs = array_merge($refs, 
          EntityTable::getRecentRelationshipReferencesQuery($object, 10)
            ->setHydrationMode(Doctrine::HYDRATE_ARRAY)
            ->execute()
        );
      }
    }

    $tmpRefs = array();
    $uniqueRefs = array();

    //consolidate references
    foreach ($refs as $ref)
    {
      if (!isset($tmpRefs[$ref['source']]))
      {
        $tmpRefs[$ref['source']] = array($ref['name']);
        $uniqueRefs[] = $ref;
      }
      else
      {
        if (!in_array($ref['name'], $tmpRefs[$ref['source']]))
        {
          $tmpRefs[$ref['source']][] = $ref['name'];
          $uniqueRefs[] = $ref;
        }
      }
    }

    $refs = $uniqueRefs;
    
    if (count($refs))
    {
      //create choices array
      $choices = array('' => '');
      
      foreach ($refs as $ref)
      {
        $choices[$ref['id']] = ReferenceTable::getDisplayName($ref);
      }

      //add select widget
      $widgets = array();
      $widgets['existing_source'] = new sfWidgetFormSelect(array(
          'choices' => $choices
      ));
      $widgets = array_merge($widgets, $this->getWidgetSchema()->getFields());    
      
      $this->setWidgets($widgets);

      if ($autoSelect && count($directRefs = $object->getReferencesByFields()->getData()) == 1)
      {
        $this->setDefault('existing_source', $directRefs[0]->id);
      }
    
      $this->widgetSchema->setLabels(array(
        'source' => 'Source URL',
        'name' => 'Display name',
        'source_detail' => 'Location in source'
      ));
                
      $this->widgetSchema->setHelps(array(
        'source' => $this->_sourceHelp,
        'name' => $this->_nameHelp,
        'source_detail' => 'eg: Chapter 5, pp 75-80',
        'publication_date' => LsFormHelp::$dateHelp,
        'excerpt' => 'relevant section(s) of the source text'
      ));
    
      $this->widgetSchema->setNameFormat('reference[%s]');


      //make source validator optional
      $this->validatorSchema['existing_source'] = new sfValidatorChoice(array('choices' => array_keys($choices)));
    }
  }
  
  
  public function getSelectObject()
  {
    return $this->_selectObject;
  }
 
 
  public function bind(array $taintedValues = null, array $taintedFiles = null)
  {    
    parent::bind($taintedValues, $taintedFiles);


    if (isset($taintedValues['nosource']) && $taintedValues['nosource'])
    {
      $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema, array());
      
      return;
    }


    if ($this->widgetSchema['existing_source'])
    {
      if (isset($this->errorSchema['existing_source']) && isset($this->errorSchema['source']))
      {
        $errors = $this->errorSchema->getErrors();
        unset($errors['existing_source']);          
        unset($errors['source']);
        unset($errors['name']);
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema, $errors);
        
        $validator = new sfValidatorString(array(), array(
          'invalid' => 'You must select a source or enter a new one (with a valid URL)'
        ));

        $this->getErrorSchema()->addError(new sfValidatorError($validator, 'invalid'));        
      }
      elseif (isset($this->errorSchema['existing_source']))
      {
        $errors = $this->errorSchema->getErrors();
        unset($errors['existing_source']);          
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema, $errors);      
      }      
      elseif (isset($this->errorSchema['source']))
      {
        $errors = $this->errorSchema->getErrors();
        unset($errors['source']);          
        unset($errors['name']);
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema, $errors);
      }
    }   
  }
}