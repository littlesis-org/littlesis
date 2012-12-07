<?php

class Objectable extends Doctrine_Template
{

  public function setTableDefinition()
  {
    $this->hasColumn('object_model', 'string', 50, array('type' => 'string', 'notnull' => true, 'notblank' => true, 'length' => '50'));
    $this->hasColumn('object_id', 'integer', null, array('type' => 'integer', 'notnull' => true));
    $this->index('object', array('fields' => array('object_model', 'object_id')));
  }


  protected $_objectCache = array();


  static function generateObjectKey(Doctrine_Record $record)
  {
    if ($record->exists())
    {
      return get_class($record) . '_' . $record->id;
    }
    
    return null;
  }


  protected function _getCachedObject(Doctrine_Record $record)
  {
    if ($key = self::generateObjectKey($record))
    {
      if (array_key_exists($key, $this->_objectCache))
      {
        return $this->_objectCache[$key];
      }
    }
    
    return null;
  }
  
  
  protected function _setCachedObject($object)
  {
    $record = $this->getInvoker();
    $key = self::generateObjectKey($record);
    
    $this->_objectCache[$key] = $object;
  }


  public function getObject($includeDeleted=false, $overrideCache=false)
  {
    $record = $this->getInvoker();

    /*
    if (!$overrideCache && $object = $this->_getCachedObject($record))
    {
      return $object;
    }
    */

    $class = $record->object_model;
    $lower = strtolower($class);
    
    $q = LsDoctrineQuery::create()
      ->from($class . ' ' . $lower)
      ->where($lower . '.id = ?', $record->object_id);
    
    if ($includeDeleted && Doctrine::getTable($class)->hasTemplate('Doctrine_Template_SoftDelete'))
    {
      $q->addWhere($lower . '.is_deleted IS NOT NULL');      
    }

    if ($object = $q->fetchOne())
    {
      //$this->_setCachedObject($object);
    }
    
    return $object;
  }
  
  
  public function setObject($object)
  {
    $record = $this->getInvoker();

    if (is_null($object))
    {
      $record->object_model = null;
      $record->object_id = null;
      
      //$this->_setCachedObject($object);
      
      return true;
    }

    if (!$class = get_class($object))
    {
      throw new Exception("Non-object passed to setObject()");
    }
    
    if (!$object->exists())
    {
      throw new Exception("Object passed to setObject() must exist");    
    }
    
    
    $record->object_model = $class;
    $record->object_id = $object->id;

    //$this->_setCachedObject($object);
    
    return true;
  }


  static function getByModelAndObjectQuery($model, Doctrine_Record $object)
  {
    if (!$object->exists())
    {
      throw new Exception("Can't get " . LsString::pluralize($model) . " by new object");
    }


    $alias = substr(strtolower($model), 0, 1);

    return LsDoctrineQuery::create()
      ->from($model . ' ' . $alias)
      ->where($alias . '.object_model = ? AND ' . $alias . '.object_id = ?', array(get_class($object), $object->id));
  }


  static function getObjectByModelAndId($model, $id, $includeDeleted=false)
  {
    return self::getObjectByModelAndIdQuery($model, $id, $includeDeleted)->fetchOne();      
  }


  static function getObjectByModelAndIdQuery($model, $id, $includeDeleted=false)
  {
    $lower = strtolower($model);

    $q = LsDoctrineQuery::create()
      ->from($model . ' ' . $lower)
      ->where($lower . '.id = ?', $id);

    if ($includeDeleted && Doctrine::getTable($model)->hasTemplate('Doctrine_Template_SoftDelete'))
    {
      $q->addWhere($lower . '.is_deleted IS NOT NULL');
    }

    return $q;      
  }

}