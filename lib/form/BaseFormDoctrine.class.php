<?php

/**
 * Project form base class.
 *
 * @package    form
 * @version    SVN: $Id: sfDoctrineFormBaseTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
abstract class BaseFormDoctrine extends sfFormDoctrine
{
  public function setup()
  {
    $schema = $this->getWidgetSchema();
    $fields = $schema->getFields();

    foreach ($fields as $name => $widget)
    {
      if (in_array($name, array('created_at', 'updated_at', 'created_on', 'updated_on')))
      {
        unset($schema[$name]);
      }
    }
  }

  public function getWidgetsForEntity()
  {
    return array();
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

    return $labels;
  }
  
  public function bind(array $taintedValues = null, array $taintedFiles = null)
  {
    $trimmedValues = array();
    
    foreach ($taintedValues as $key => $value)
    {
      $trimmedValues[$key] = is_string($value) ? trim($value) : $value;
    }
    
    parent::bind($trimmedValues, $taintedFiles);
  }
}