<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Doctrine CRUD generator.
 *
 * This class generates a basic CRUD module with doctrine.
 *
 * @package    symfony
 * @subpackage generator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfPropelCrudGenerator.class.php 9132 2008-05-21 04:28:03Z Carl.Vondrick $
 */

class sfDoctrineCrudGenerator extends sfAdminGenerator
{
  /**
   * Doctrine_Table instance for this crud generator
   *
   * @var Doctrine_Table $table
   */
  protected $table;
  
  /**
   * Initializes the current sfGenerator instance.
   *
   * @param sfGeneratorManager $generatorManager A sfGeneratorManager instance
   */
  public function initialize(sfGeneratorManager $generatorManager)
  {
    parent::initialize($generatorManager);

    $this->setGeneratorClass('sfDoctrineCrud');
  }
  
  /**
   * Get Doctrine_Table instance for admin generator
   *
   * @return Doctrine_Table $table
   */
  protected function getTable()
  {
    return $this->table;
  }
  
  /**
   * Get columns for admin generator instance
   *
   * @param string $paramName 
   * @param string $category 
   * @return array $columns
   */
  public function getColumns($paramName, $category = 'NONE')
  {
    $columns = parent::getColumns($paramName, $category);

    // set the foreign key indicator
    $relations = $this->getTable()->getRelations();
    $relationKeys = array();
    foreach ($relations as $key => $relation)
    {
      $relationKeys[$relation->getLocalFieldName()] = $key;
    }

    $cols = $this->getTable()->getColumns();

    foreach ($columns as $index => $column)
    {
      if (isset($relations[$column->getName()]))
      {
        $relationKey = $column->getName();
      }
      elseif (array_key_exists($column->getName(), $relationKeys)) 
      {
        $relationKey = $relationKeys[$column->getName()];
      }
      else
      {
        $relationKey = false;
      }
      if ($relationKey)
      {
        $fkcolumn = $relations[$relationKey];
        $columnName = $relations[$relationKey]->getLocal();
        if ($columnName != 'id') // i don't know why this is necessary
        {
          $column->setRelatedClassName($fkcolumn->getTable()->getComponentName());
          $column->setColumnName($columnName);

          // if it is not a many2many
          if (isset($cols[$columnName]))
          {
            $column->setColumnInfo($cols[$columnName]);
          }

          $columns[$index] = $column;
        }
      }
    }

    return $columns;
  }

  /**
   * Get array of all columns as sfDoctrineAdminColumn instances
   *
   * @return array $columns
   */
  function getAllColumns()
  {
    $cols = $this->getTable()->getColumns();
    $rels = $this->getTable()->getRelations();
    $columns = array();
    foreach ($cols as $name => $col)
    {
      // we set out to replace the foreign key to their corresponding aliases
      $found = null;
      foreach ($rels as $alias => $rel)
      {
        $relType = $rel->getType();
        if ($rel->getLocal() == $name && $relType != Doctrine_Relation::MANY)
        {
          $found = $alias;
        }
      }

      if ($found)
      {
        $name = $found;
      }

      $columns[] = new sfDoctrineAdminColumn($name, $col);
    }

    return $columns;
  }
  
  /**
   * Load primary key columns as array of sfDoctrineAdminColumn instances
   *
   * @return void
   * @throws sfException
   */
  protected function loadPrimaryKeys()
  {
    $identifier = $this->getTable()->getIdentifier();
    if (is_array($identifier))
    {
      foreach ($identifier as $_key)
      {
        $this->primaryKey[] = new sfDoctrineAdminColumn($_key);
      }
    } else {
      $this->primaryKey[] = new sfDoctrineAdminColumn($identifier);
    }

    if (!empty($this->primaryKeys))
    {
      throw new sfException('You cannot use the admin generator on a model which does not have any primary keys defined');
    }
  }

  /**
   * Load all Doctrine_Table class to generate instance
   *
   * @return void
   */
  protected function loadMapBuilderClasses()
  {
    $this->table = Doctrine::getTable($this->getClassName());
  }

  /**
   * Get symfony php object helper
   *
   * @param string $helperName 
   * @param string $column 
   * @param string $params 
   * @param string $localParams 
   * @return string $helperCode
   */
  function getPHPObjectHelper($helperName, $column, $params, $localParams = array())
  {
    $params = $this->getObjectTagParams($params, $localParams);

    // special treatment for object_select_tag:
    if ($helperName == 'select_tag')
    {
      $column = new sfDoctrineAdminColumn($column->getColumnName(), null, null);
    }

    return sprintf('object_%s($%s, %s, %s)', $helperName, $this->getSingularName(), var_export($this->getColumnGetter($column), true), $params);
  }

  /**
   * Get php code for column getter
   *
   * @param string $column 
   * @param string $developed 
   * @param string $prefix 
   * @return string $getterCode
   */
  function getColumnGetter($column, $developed = false, $prefix = '')
  {
    if ($developed)
    {
      return sprintf("$%s%s['%s']", $prefix, $this->getSingularName(), $column->getName());
    }

    // no parenthesis, we return a method+parameters array
    return array('get', array($column->getName()));
  }
  
  /**
   * Get related class name for a column
   *
   * @param string $column 
   * @return string $className
   */
  function getRelatedClassName($column)
  {
    return $column->getRelatedClassName();
  }
}
