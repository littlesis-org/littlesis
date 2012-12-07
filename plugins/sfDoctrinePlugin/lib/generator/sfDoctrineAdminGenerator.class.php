<?php
/*
 * This file is part of the sfDoctrinePlugin package.
 * (c) 2006-2007 Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Class responsible for Doctrine based symfony admin generators
 *
 * @package    sfDoctrinePlugin
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineAdminGenerator.class.php 11457 2008-09-11 21:03:53Z Jonathan.Wage $
 */
class sfDoctrineAdminGenerator extends sfDoctrineCrudGenerator
{
  /**
   * Initialize a doctrine admin generator instance
   *
   * @param sfGeneratorManager $generatorManager
   * @return void
   */
  public function initialize(sfGeneratorManager $generatorManager)
  {
    parent::initialize($generatorManager);

//    $this->setGeneratorClass('sfDoctrineCrud');
    $this->setGeneratorClass('sfDoctrineAdmin');
  }

  /**
   * Get an sfDoctrineAdminColumn instance for a field/column
   *
   * @param string $field 
   * @param string $flag 
   * @return sfDoctrineAdminColumn $column
   */
  function getAdminColumnForField($field, $flag = null)
  {
    $cols = $this->getTable()->getColumns(); // put this in an internal variable?
    return  new sfDoctrineAdminColumn($field, (isset($cols[$field]) ? $cols[$field] : null), $flag);
  }

  /**
   * Get php code for column setter
   *
   * @param string $column 
   * @param string $value 
   * @param string $singleQuotes 
   * @param string $prefix 
   * @return string $setterCode
   */
  function getColumnSetter($column, $value, $singleQuotes = false, $prefix = 'this->')
  {
    if ($singleQuotes)
    {
      $value = sprintf("'%s'", $value);
    }

    return sprintf('$%s%s->set(\'%s\', %s)', $prefix, $this->getSingularName(), $column->getName(), $value);
  }

  /**
   * Get php code for column edit tag
   *
   * @param string $column 
   * @param string $params 
   * @return string $columnEditTag
   */
  public function getColumnEditTag($column, $params = array())
  {
    if ($column->getDoctrineType() == 'enum')
    {
      $params = array_merge(array('control_name' => $this->getSingularName().'['.$column->getName().']'), $params);

      $values = $this->getTable()->getEnumValues($column->getName());
      $params = array_merge(array('enumValues'=>$values), $params);
      return $this->getPHPObjectHelper('enum_tag', $column, $params);
    }

    return parent::getColumnEditTag($column, $params);
  }
  
  /**
   * Get php code for column filter tag
   *
   * @param string $column 
   * @param string $params 
   * @return string $columnFilterTag
   */
  public function getColumnFilterTag($column, $params = array())
  {
    $defaultValue = "isset(\$filters['".$column->getName()."']) ? \$filters['".$column->getName()."'] : null";
    $unquotedName = 'filters['.$column->getName().']';
    $name = "'$unquotedName'";
    if ($column->getDoctrineType() == 'enum')
    {
      $enumValues = $this->getTable()->getEnumValues($column->getName());
      $enumValues = $this->getObjectTagParams(array_combine($enumValues, $enumValues));
      $defaultIncludeCustom = '__("indifferent")';
      $optionParams = $this->getObjectTagParams($params, array('include_custom' => $defaultIncludeCustom));
      // little hack
      $optionParams = preg_replace("/'".preg_quote($defaultIncludeCustom)."'/", $defaultIncludeCustom, $optionParams);
      $params = $this->getObjectTagParams($params);
      
      return "select_tag($name, options_for_select($enumValues, $defaultValue, $optionParams), $params)";
    }
    else if ($column->getDoctrineType() == 'clob')
    {
      $size = ($column->getSize() < 15 ? $column->getSize() : 15);
      $params = $this->getObjectTagParams($params, array('size' => $size));
      return "input_tag($name, $defaultValue, $params)";
    }

    return parent::getColumnFilterTag($column, $params);
  }
  
}