<?php

class LsVersionableListener extends Doctrine_Record_Listener
{
  private $_incompleteModificationId;
  private $_isVersioning = true;

  static function isSiteVersioningEnabled()
  {
    return sfConfig::get('app_versioning_track_modifications', false);
  }

  public function setVersioning($bool)
  {
    $this->_isVersioning = $bool;
  }
  
  static function getUserId()
  {
    if (sfContext::hasInstance() && $user = sfContext::getInstance()->getUser()->getGuardUser())
    {
      return $user->id;
    }
    
    return sfGuardUserTable::SYSTEM_USER_ID;
  }
  
  
  public function preSave(Doctrine_Event $event)
  {
    //only log changes if versioning is enabled
    if ($this->_isVersioning && self::isSiteVersioningEnabled())
    {
      $record = $event->getInvoker();


      //if record is being soft-deleted, skip all this because it's already been done in preDelete() (see below)
      if (isset($record->is_deleted) && $record->is_deleted)
      {
        return;
      }

      if ($record->exists())
      {
        //record already is saved: log changes between saved version and current version
        $oldRecord = $record->getOldRecord();
        $modificationId = self::logChanges($oldRecord, $record);
      }
      else
      {
        //record is new: log changes between empty record and current record
        $oldRecord = $record->getBlankRecord();

        //we don't know what the record's id is yet, so we create an 'incomplete' modification
        //without an object_id, then set the object_id in postSave()
        $this->_incompleteModificationId = $modificationId = self::logChanges($oldRecord, $record, $new=true);
      }

      //if the modification was created (ie, if there were real changes to log), set the last
      //user to edit the record
      if ($modificationId)
      {
        $record->last_user_id = self::getUserId();
      }

      unset($record);
      unset($oldRecord);
    }    
  }
  
  
  public function postSave(Doctrine_Event $event)
  {
    if ($this->_isVersioning && self::isSiteVersioningEnabled())
    {
      if ($this->_incompleteModificationId)
      {
        $db = Doctrine_Manager::connection();
        $sql = 'UPDATE modification SET object_id = ? WHERE id = ?';
        $stmt = $db->execute($sql, array($event->getInvoker()->id, $this->_incompleteModificationId));
        
        $this->_incompleteModificationId = null;
      }      
    }    
  }
  
    
  public function preDelete(Doctrine_Event $event)
  {
    if ($this->_isVersioning && self::isSiteVersioningEnabled())
    {
      //new record is empty
      $object = $event->getInvoker();

      if (method_exists($object,'isMerge') && $object->isMerge())
      {
        //if merging, skip because LsVersionable::mergeFrom() will create the necessary modification
        $object->setMerge(false);
      }
      else
      {
        self::logDelete($object);
      }      
    }
  }

  
  static function logChanges(Doctrine_Record $oldObject, Doctrine_Record $newObject, $new=false)
  {
    $merged = false;
    $modId = null;

    //isMerge() is used to set the modification's is_merge field, which, for non-delete
    //modifications, is only used to identify the modification as part of a larger merge
    if (method_exists($newObject,'isMerge'))
    {
      $merged = $newObject->isMerge();
    }
    
    if (get_class($oldObject) != get_class($newObject))
    {
      throw new exception("Can't log modifications between objects of different classes");
    }

    $conn = Doctrine_Manager::connection();
    try
    {
      $conn->beginTransaction();
      
      //get clean sets of old and new data
      $oldData = method_exists($oldObject, 'getAllData') ? $oldObject->getAllData() : $oldObject->toArray(false);
      $newData = method_exists($newObject, 'getAllData') ? $newObject->getAllData() : $newObject->toArray(false);

      //if modification is create, set all old data to null
      if ($new)
      {
        $oldData = array_fill_keys(array_keys($oldData), null);
      }

      //remove null and blank keys
      $oldData = array_filter($oldData, array('self', 'notNullOrBlankCallback'));
      $newData = array_filter($newData, array('self', 'notNullOrBlankCallback'));

      //change false values to 0
      $oldData = array_map(array('self', 'falseToZeroCallback'), $oldData);
      $newData = array_map(array('self', 'falseToZeroCallback'), $newData);      

      //create set of possible modified fields
      $fields = array_unique(array_merge(array_keys($oldData), array_keys($newData)));
      $fields = array_diff($fields, array('id', 'created_at', 'updated_at', 'is_deleted', 'last_user_id'));
      $modifiedFields = array();

      foreach ($fields as $field)
      {
        //field is modified if field was or is no longer null, or if old and new values are different
        if (!array_key_exists($field, $oldData) || !array_key_exists($field, $newData) || !self::areSameValues($oldData[$field], $newData[$field]))
        {
          $modifiedFields[] = $field;
        }        
      }

      if ($merged || count($modifiedFields))
      {        
        $db = Doctrine_Manager::connection();
        $sql = 'INSERT INTO modification (object_model, object_id, object_name, is_create, is_merge, user_id, created_at, updated_at) ' .
               'VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

        if (!$name = $newObject->getName())
        {
          $name = get_class($newObject);
        }

        $params = array(
          get_class($oldObject), 
          $newObject->id ? $newObject->id : '', 
          $name, 
          $new, 
          $merged, 
          self::getUserId(), 
          LsDate::getCurrentDateTime(), 
          LsDate::getCurrentDateTime()
        );
        
        $stmt = $db->execute($sql, $params);
        $modId = $db->lastInsertId('modification');

        if (count($modifiedFields))
        {
          //insert all modification_field records at once
          $sql = 'INSERT INTO modification_field (modification_id, field_name, old_value, new_value) VALUES';
          $params = array();
  
          foreach ($modifiedFields as $field)
          {
            $sql .= ' (?, ?, ?, ?),';
            $params = array_merge($params, array($modId, $field, isset($oldData[$field]) ? $oldData[$field] : null, isset($newData[$field]) ? $newData[$field] : null));
          }
          
          $sql = substr($sql, 0, strlen($sql) - 1);
          $stmt = $db->execute($sql, $params);        
        }
      }

      $conn->commit();
    }
    catch (Exception $e)
    {
      $conn->rollback();
      throw $e;
    }
    
    unset($oldObject);
    unset($newObject);
    
    return $modId;  
  }    


  static function logDelete(Doctrine_Record $r, $mergeId=null)
  {
    $mod = new Modification;
    $mod->object_model = get_class($r);
    $mod->object_id = $r->id;
    $mod->object_name = $r->getName();
    $mod->user_id = LsVersionableListener::getUserId();
    $mod->is_delete = true;

    if ($mergeId)
    {
      $mod->is_merge = true;
      $mod->merge_object_id = $mergeId;
    }
    
    $mod->save();
  }
  
  
  static function logMerge(Doctrine_Record $r)
  {
    $mod = new Modification;
    $mod->object_model = get_class($r);
    $mod->object_id = $r->id;
    $mod->object_name = $r->getName();
    $mod->user_id = LsVersionableListener::getUserId();
    $mod->is_merge = true;
    $mod->save();  
  }
  
  
  static function areSameValues($v1, $v2)
  {
    if ($v1 === $v2)
    {
      return true;
    }
    
    if (!is_null($v1) && !is_null($v2) && ((string) $v1 == (string) $v2))
    {
      return true;
    }
    
    return false;
  }
  
  
  static function notNullOrBlankCallback($value)
  {
    return (is_null($value) || $value === '' || $value instanceOf Doctrine_Null) ? false : true;
  }
  
  
  static function falseToZeroCallback($value)
  {
    return ($value === false) ? 0 : $value;
  }
}