<?php

class Referenceable extends Doctrine_Template
{
  public function setUp()
  {
    $this->addListener(new ReferenceableHydrationListener);
  }

  /* References should NOT be deleted on entity delete...
  
  public function onObjectDelete()
  {
    $this->getInvoker()->removeAllReferences();
  }
  */


  public function mergeFrom(Doctrine_Record $r)
  {
    $object = $this->getInvoker();

    if (!$r->exists() || !$object->exists())
    {
      return false;
    }


    foreach ($r->getReferencesByFields() as $ref)
    {
      if (count($ref->Excerpt))
      {
        foreach ($ref->Excerpt as $excerpt)
        {
          $object->addReference($ref->source, $excerpt->body, $ref->getFieldsArray(), $ref->name, $ref->source_detail, $ref->publication_date);
        }
      }
      else
      {
        $object->addReference($ref->source, null, $ref->getFieldsArray(), $ref->name, $ref->source_detail, $ref->publication_date);
      }

      $ref->delete();
    }

    return true;
  }


  public function getAllFields()
  {
    return array_keys($this->getInvoker()->toArray(false));
  }


  public function getAllModifiedFields()
  {
    $object = $this->getInvoker();
    $formClass = get_class($object) . 'Form';
    $form = new $formClass;
    $fields = array_intersect(array_keys($object->getModified()), array_keys($form->getFieldsWithLabels()));
    
    return $fields;
  }


  public function getReferencesByFields($fields=null, $hydrationMode=Doctrine::HYDRATE_RECORD)
  {
    return self::getReferencesByFieldsQuery($this->getInvoker(), $fields)
      ->setHydrationMode($hydrationMode)
      ->execute();
  }
  
  
  static function getReferencesByFieldsQuery($object, $fields=null)
  {    
    if (!$object->exists())
    {
      throw new Exception("Can't get References for new object");
    }


    $q = Doctrine_Query::create()
      ->from('Reference r')
      ->where('r.object_model = ? AND r.object_id = ?', array(get_class($object), $object->id));
      
    foreach ((array) $fields as $field)
    {
      $q->addWhere('r.fields LIKE \'%' . $field . '%\'');
    }
    
    return $q;
  }

  
  public function addReference($source, $excerpt=null, $fields=null, $name=null, $detail=null, $date=null, $check_existing = true)
  {
    $object = $this->getInvoker();
    
    if (!$object->exists())
    {
      throw new Exception("Can't add Reference to new object");
    }
    

    //make sure provided fields all exist
    if ($fields)
    {
      $entityFields = array_diff($object->getAllFields(), array('id'));
      
      if ($diff = array_diff((array) $fields, $entityFields))
      {
        throw new Exception('Unknown fields: ' . implode(', ', $diff));
      }
    }


    //look for existing ref 
    $ref = null;
    if ($check_existing == true)
    {
      $ref = $this->getReference($source, $detail);
    }
    if (!$ref)
    {
      $ref = new Reference;
      $ref->object_model = get_class($object);
      $ref->object_id = $object->id;
      $ref->source = $source;
      $ref->source_detail = $detail;
      $ref->publication_date = $date;
    }

    if (!$ref->name)
    {
      $ref->name = $name;
    }
    

    //add fields
    $ref->addFields($fields);
        
    
    //save ref and excerpt, if provided
    $db = Doctrine_Manager::connection();

    try
    {
      $db->beginTransaction();

      if ($excerpt)
      {
        $ref->addExcerpt($excerpt);
      }
      
      $ref->save();      
      
      $db->commit();
    }
    catch (Exception $e)
    {
      $db->rollback();
      throw $e;
    }
    
    return $ref;
  }

  
  public function getReference($source, $detail=null)
  {
    $object = $this->getInvoker();
    
    if (!$object->exists())
    {
      throw new Exception("Can't get Reference for new object");
    }


    $source = urldecode($source);
    $q = LsDoctrineQuery::create()
      ->from('Reference r')
      ->where('r.source = ? AND r.source_detail = ?', array($source, $detail))
      ->addWhere('r.object_model = ? AND r.object_id = ?', array(get_class($this->getInvoker()), $this->getInvoker()->id));
      
    return $q->fetchOne();
  }


  public function removeAllReferences()
  {
    $object = $this->getInvoker();
  
    if (!$object->exists())
    {
      throw new Exception("Can't remove References for new object");
    }
    

    return Doctrine_Query::create()
      ->from('Reference r')
      ->where('r.object_model = ? AND r.object_id = ?', array(get_class($object), $object->id))
      ->delete();
  }


  public function saveWithRequiredReference($refParams)
  {
    $record = $this->getInvoker();


    //get modified fields and save record
    $modifiedFields = $record->getAllModifiedFields();
    $record->save();

    //skip reference if nosource is checked
    if (isset($refParams['nosource']) && $refParams['nosource'])
    {
      
    }
    //add reference from request params
    else if (isset($refParams['existing_source']) && ($refId = $refParams['existing_source']))
    {
      $ref = Doctrine::getTable('Reference')->find($refId);

      if (($ref->object_model == get_class($record)) && ($ref->object_id == $record->id))
      {
        $ref->addFields($modifiedFields);
        $ref->save();
      }
      else
      {
        $record->addReference(
          $ref->source,
          $refParams['excerpt'], 
          $modifiedFields, 
          $ref->name,
          $refParams['source_detail'],
          $refParams['publication_date']
        );      
      }
    }
    else
    {
      $record->addReference(
        $refParams['source'],
        $refParams['excerpt'], 
        $modifiedFields, 
        $refParams['name'],
        $refParams['source_detail'],
        $refParams['publication_date']
      );
    }
  }
}