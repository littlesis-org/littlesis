<?php

class LsVersionable extends Doctrine_Template
{
  protected $_isSaving = false;


  public function setTableDefinition()
  {
    $this->hasColumn('last_user_id', 'integer');
  }
  

  public function setUp()
  {
    $this->hasOne('sfGuardUser as LastUser', array('local' => 'last_user_id',
                                               'foreign' => 'id',
                                               'onDelete' => 'RESTRICT',
                                               'onUpdate' => 'CASCADE'));

    $this->addListener(new LsVersionableListener());
  }

  
  public function isMerge()
  {
    return false;
  }
  
  
  public function setIsSaving($bool)
  {
    $this->_isSaving = (bool) $bool;
  }
  
  
  public function isSaving()
  {
    return $this->_isSaving;
  }
  
  
  
  static function getRelationAliasByRecordAndFieldName($record, $fieldName)
  {
    $table = $record->getTable();
    
    foreach ($table->getRelations() as $name => $relation)
    {
      if ($relation->getLocalFieldName() == $fieldName)
      {
        return $name;
      }
    }
    
    return null;    
  }


  static function getRelationAliasByModelAndFieldName($model, $fieldName)
  {
    if (!$table = Doctrine::getTable($model))
    {
      throw new Exception("Can't get relation alias for invalid model");
    }
    
    foreach ($table->getRelations() as $name => $relation)
    {
      if ($relation->getLocalFieldName() == $fieldName)
      {
        return $name;
      }
    }
    
    return null;    
  }
  
  
  public function getRelationAliasByFieldName($fieldName)
  {
    $object = $this->getInvoker();

    return self::getRelationAliasByRecordAndFieldName($object, $fieldName);
  }


  static function getOldRecordData(Doctrine_Record $record)
  {
    $class = get_class($record);
  
    if (!$record->exists())
    {
      $new = new $class;
      return $new->toArray();
    }


    $q = LsDoctrineQuery::create()
      ->from($class . ' ' . strtolower($class))
      ->where(strtolower($class) . '.id = ?', $record->id)
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);
      
    if ($record->getTable()->hasTemplate('Doctrine_Template_SoftDelete'))
    {
      $q->addWhere(strtolower($class) . '.is_deleted IS NOT NULL');
    }
     
    return $q->fetchOne();    
  }
  

  public function getOldData()
  {
    return self::getOldRecordData($this->getInvoker());
  }


  public function getOldRecord()
  {
    $object = $this->getInvoker();
    $class = get_class($object);
    $record = new $class;
    $record->fromArray($object->getOldData());
    
    return $record;
  }
  
  
  public function getBlankRecord()
  {
    $object = $this->getInvoker();
    $class = get_class($object);
    $record = new $class;
    $emptyData = array_fill_keys(array_keys($record->getData()), null);
    $record->fromArray($emptyData);
        
    return $record;  
  }


  public function mergeFrom(Doctrine_Record $r)
  {
    $object = $this->getInvoker();

    if (!$r->exists() || !$object->exists())
    {  
      return false;
    }

    //create delete record for merged entity
    LsVersionableListener::logDelete($r, $object['id']);
    
    return true;
  }


  public function getName()
  {
    $data = $this->getInvoker()->getData();
    
    $guesses = array('name', 'title', 'description');
    
    foreach ($guesses as $guess)
    {
      if (isset($data[$guess]))
      {
        return $data[$guess];
      }
    }

    return null;
  }
  
  
  public function getRecentUsersQuery()
  {
    $object = $this->getInvoker();

    return LsDoctrineQuery::create()
      ->from('sfGuardUser u')
      ->leftJoin('u.Modification m')
      ->where('m.object_model = ? AND m.object_id = ?', array(get_class($object), $object->id))
      ->orderBy('m.created_at DESC');      
  }
  
  
  public function getLastModifiedUser()
  {
    $object = $this->getInvoker();

    if (!$userId = $object->last_user_id)
    {
      $result = LsDoctrineQuery::create()
        ->select('m.user_id')
        ->from('Modification m')
        ->where('m.object_model = ? AND m.object_id = ?', array(get_class($object), $object->id))
        ->orderBy('m.id DESC')
        ->setHydrationMode(Doctrine::HYDRATE_NONE)
        ->fetchOne();
      
      $userId = $result[0];
    }
      
    if ($userId && $user = Doctrine::getTable('sfGuardUser')->find($userId))
    {
      return $user;
    }
    
    return null;
  }
  
  
  public function getModificationsQuery($userId=null)
  {
    $object = $this->getInvoker();
    
    $q = LsDoctrineQuery::create()
      ->from('Modification m')
      ->leftJoin('m.Field f')
      ->leftJoin('m.User u')
      ->leftJoin('u.Profile p')
      ->addWhere('m.object_model = ? AND m.object_id = ?', array(get_class($object), $object->id))
      ->orderBy('m.created_at DESC');
  
    if ($userId)
    {
      $q->addWhere('m.user_id = ?', $userId);
    }
    
    return $q;
  }


  public function getRecentModificationsQuery()
  {
    $object = $this->getInvoker();
    
    $q = LsDoctrineQuery::create()
      ->from('Modification m')
      ->addWhere('m.object_model = ? AND m.object_id = ?', array(get_class($object), $object->id))
      ->orderBy('m.created_at DESC');
      
    return $q;
  }
  
  
  public function getCreatedByUser()
  {
    $object = $this->getInvoker();

    $result = LsDoctrineQuery::create()
      ->select('m.user_id')
      ->from('Modification m')
      ->addWhere('m.is_create = ? AND m.object_model = ? AND m.object_id = ?', array(true, get_class($object), $object->id))
      ->setHydrationMode(Doctrine::HYDRATE_NONE)
      ->fetchOne();

    if ($userId = $result[0])
    {
      return Doctrine::getTable('sfGuardUser')->find($userId);
    }
    
    return null;
  }



  static function safeRevertByUserId($userId)
  {
    //get all user modifications grouped by object
    $modifications = ModificationTable::getByUserIdGroupedByObjectQuery($userId)
      ->leftJoin('m.Field f')
      ->execute();
    
    foreach ($modifications as $m)
    {
      $q = LsDoctrineQuery::create()
        ->from('Modification m')
        ->where('m.object_model = ? AND m.object_id = ?', array($m->object_model, $m->object_id));
        

      //if nobody else modified this object and it exists, we delete it and continue
      if ($object = $m->getObject())
      {
        if (!$q->addWhere('m.user_id <> ?', $userId)->count())
        {
          $object->delete();
        }
      }
      else
      {
        //if user deleted this object, we recreate it
        if ($delete = $q->addWhere('m.user_id = ? AND m.is_delete = ?', array($userId, true))->execute())
        {
          $object = $m->getObject(true);
          
          foreach ($delete->Field as $f)
          {
            $fieldName = $f->field_name;
            $object->$fieldName = $f->old_value;
          }
        }      
      }

      
      if ($object)
      {
        //get all fields user modified
        $modifiedFields = array();

        foreach ($m->Field as $f)
        {
          $modifiedFields[] = $f->field_name;
        }

        $modifiedFields = array_unique($modifiedFields);


        foreach ($modifiedFields as $fieldName)
        {
          //if user is last to modify, we revert field to last value entered by another user, or null              
          $q = LsDoctrineQuery::create()
            ->from('Modification m')
            ->leftJoin('m.Field f')
            ->where('m.user_id <> ?', $userId)
            ->addWhere('f.field_name = ?', $fielName)
            ->orderBy('m.created_at DESC');
          
          if ($m = $q->fetchOne())
          {
            $object->$fieldName = $m->Field->new_value;
          }
          else
          {
            $object->$fieldName = null;
          }
        
        }
      }            
    }
    
  }


  static function safeRevertModificationById($id, $save=true)
  {
    if (!$m = Doctrine::getTable('Modification')->find($id))
    {
      return false;
    }
    
    if (!$object = $m->getObject())
    {
      return false;
    }

    $revertedFields = array();
    
    foreach ($m->Field as $field)
    {
      //see if there are more recent changes to the field
      $q = LsDoctrineQuery::create()
        ->from('Modification m')
        ->leftJoin('m.Field as f')
        ->where('m.created_at > ?', $m->created_at)
        ->addWhere('f.field_name = ?', $field->field_name);
        
      if (!$q->count())
      {
        //if not, we revert to the value before the modification
        $fieldName = $field->field_name;
        $object->$fieldName = $field->old_value;        
        
        $revertedFields[] = $field;
      }
    }
    
    if ($save)
    {
      $object->save();
    }
    
    return $revertedFields;
  }
}