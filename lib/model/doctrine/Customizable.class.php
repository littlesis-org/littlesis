<?php

class Customizable extends Doctrine_Template
{
  public function getCustomFields(Array $keys=null)
  {
    $object = $this->getInvoker();  
    
    if (!$id = $object->id)
      throw Exception("Can't get custom fields for object without id");

    $model = get_class($object);

    $db = Doctrine_Manager::connection();
    $sql = 'SELECT name, value FROM custom_key WHERE object_model = ? AND object_id = ?';
    $stmt = $db->execute($sql, array($model, $id));
    $fields = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    if ($keys)
    {
      $keys = array_fill_keys($keys, null);
      $fields = array_intersect_key($fields, $keys);
    }

    return $fields;
  }
  
  
  public function getCustomFieldsWithIds()
  {
    $object = $this->getInvoker();  
    
    if (!$id = $object->id)
      throw Exception("Can't get custom fields for object without id");

    $model = get_class($object);

    $db = Doctrine_Manager::connection();
    $sql = 'SELECT id, name, value FROM custom_key WHERE object_model = ? AND object_id = ?';
    $stmt = $db->execute($sql, array($model, $id));
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $fields;
  }
  

  public function getCustomField($key)
  {
    $object = $this->getInvoker();  
    
    if (!$id = $object->id)
      throw Exception("Can't get custom fields for object without id");

    $model = get_class($object);

    $db = Doctrine_Manager::connection();
    $sql = 'SELECT value FROM custom_key WHERE object_model = ? AND object_id = ? AND name = ?';
    $stmt = $db->execute($sql, array($model, $id, $key));
    
    return $stmt->fetch(PDO::FETCH_COLUMN);
  }
  
  
  public function setCustomField($key, $value)
  {
    $object = $this->getInvoker();  
    
    if (!$id = $object->id)
      throw Exception("Can't set custom fields for object without id");

    $model = get_class($object);
    
    $db = Doctrine_Manager::connection();    
    $oldValue = $this->getCustomField($key);

    if ($oldValue === false || $value != $oldValue)
    {
      //save field
      $sql = 'REPLACE INTO custom_key (object_model, object_id, name, value, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)';
      $time = LsDate::getCurrentDateTime();
      $stmt = $db->execute($sql, array($model, $id, $key, $value, $time, $time));

      //log change
      $this->logCustomFieldChange($key, $oldValue, $value);

      return true;
    }
    else
    {
      return false;
    }
  }
  
  
  public function removeCustomField($key)
  {
    $object = $this->getInvoker();  
    
    if (!$id = $object->id)
      throw Exception("Can't set custom fields for object without id");

    $model = get_class($object);
    
    $db = Doctrine_Manager::connection();    
    $oldValue = $this->getCustomField($key);
    
    if ($oldValue !== false)
    {
      $sql = 'DELETE FROM custom_key WHERE object_model = ? AND object_id = ? AND name = ?';
      $stmt = $db->execute($sql, array($model, $id, $key));
      
      $this->logCustomFieldChange($key, $oldValue, null);
    }
  }
  
  
  public function logCustomFieldChange($key, $oldValue, $newValue, $modificationId=null)
  {
    $object = $this->getInvoker();  
    
    if (!$id = $object->id)
      throw Exception("Can't log custom field changes for object without id");

    $model = get_class($object);

    $db = Doctrine_Manager::connection();

    try
    {
      $db->beginTransaction();
  
      if (!$modificationId)
      {
        //insert modification
        $userId = LsVersionableListener::getUserId();
        $time = LsDate::getCurrentDateTime();
        $sql = 'INSERT INTO modification (object_model, object_id, user_id, created_at, updated_at) ' .
               'VALUES (?, ?, ?, ?, ?)';
        $stmt = $db->execute($sql, array($model, $id, $userId, $time, $time));
        $modificationId = $db->lastInsertId('modification');
      }
      
      //insert modification field
      $sql = 'INSERT INTO modification_field (modification_id, field_name, old_value, new_value) VALUES (?, ?, ?, ?)';
      $stmt = $db->execute($sql, array($modificationId, $key, $oldValue, $newValue));    

      $db->commit();
    }
    catch (Exception $e)
    {
      $db->rollback();
      throw $e;
    }
  }  


  public function mergeFrom(Doctrine_Record $r)
  {
    $object = $this->getInvoker();

    if (!$r->exists() || !$object->exists())
    {
      return false;
    }

    foreach ($r->getCustomFields() as $key => $value)
    {
      $existingValue = $object->getCustomField($key);
      
      if ($existingValue === false)
      {
        $object->setCustomField($key, $value);
      }

      $r->removeCustomField($key);
    }

    return true;
  }
}