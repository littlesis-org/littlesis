<?php

class Taggable extends Doctrine_Template
{
  public function onObjectDelete()
  {
    $this->getInvoker()->removeAllTags();
  }


  public function mergeFrom(Doctrine_Record $r)
  {
    $object = $this->getInvoker();

    if (!$r->exists() || !$object->exists())
    {
      return false;
    }
      

    foreach ($r->getObjectTagsQuery()->execute() as $objectTag)
    {
      $q = LsQuery::getByModelAndFieldsQuery('ObjectTag', array(
        'object_model' => get_class($object),
        'object_id' => $object->id,
        'tag_id' => $objectTag->tag_id
      ));
      
      if (!$q->count())
      {
        $objectTag->object_model = get_class($object);
        $objectTag->object_id = $object->id;
        $objectTag->save();
      }
      else
      {
        $objectTag->delete();
      }
    }
    
    return true;
  }


  public function getTagsQuery($sort=true, $visibleOnly=true)
  {
    $object = $this->getInvoker();

    if (!$object->exists())
    {
      throw new Exception("Can't get Tags for new object");
    }


    $q = LsDoctrineQuery::create()
      ->from('Tag t')
      ->innerJoin(
        't.ObjectTag ot ON ot.tag_id = t.id AND ot.object_model = ? AND ot.object_id = ?',
        array(get_class($object), $object->id)
      );

    if ($sort)
    {
      $q->orderBy('t.name ASC, t.triple_namespace ASC, t.triple_predicate ASC, t.triple_value ASC');
    }
    
    if ($visibleOnly)
    {
      $q->where('t.is_visible = ?', true);
    }
    
    return $q;
  }
  

  public function getTagNamesQuery($sort=true, $visibleOnly=true)
  {
    return $q = $this->getTagsQuery($sort, $visibleOnly)
      ->select('t.name')
      ->addWhere('t.name IS NOT NULL')
      ->setHydrationMode(Doctrine::HYDRATE_NONE);
  }


  public function getTripleTagsQuery($namespace=null, $predicate=null, $value=null, $visibleOnly=true, $sort=true)
  {
    $q = $this->getTagsQuery(false, $visibleOnly)
      ->orderBy('t.triple_namespace ASC, t.triple_predicate ASC, t.triple_value ASC');
    
    if ($namespace)
    {
      $q->addWhere('t.triple_namespace = ?', $namespace);
    }
    
    if ($predicate)
    {
      $q->addWhere('t.triple_predicate = ?', $predicate);
    }
    
    if ($value)
    {
      $q->addWhere('t.triple_value = ?', $value);
    }
    
    if ($sort)
    {
      $q->orderBy('t.triple_namespace ASC, t.triple_predicate ASC, t.triple_value ASC');
    }
    
    return $q;      
  }


  public function getObjectTagsQuery($visibleOnly=true)
  {
    $object = $this->getInvoker();

    if (!$object->exists())
    {
      throw new Exception("Can't get ObjectTags for new object");
    }
    
    
    $q = LsDoctrineQuery::create()
      ->from('ObjectTag ot')
      ->where('ot.object_model = ? AND ot.object_id = ?', array(get_class($object), $object->id));
      
    if ($visibleOnly)
    {
      $q->leftJoin('ot.Tag t')
        ->addWhere('t.is_visible = ?', true);
    }
    
    return $q;
  }


  public function hasObjectTag(Tag $tag)
  {
    return $this->getObjectTag($tag) ? true : false;
  }


  public function getObjectTag($tag)
  {
    if (!$tag->exists())
    {
      throw new Exception("Can't look for ObjectTags; given Tag is new");
    }


    $object = $this->getInvoker();

    if (!$object->exists())
    {
      throw new Exception("Can't look for Tag for new object");
    }
    
    
    $objectTag = LsDoctrineQuery::create()
      ->from('ObjectTag ot')
      ->where('ot.object_model = ? AND ot.object_id = ? AND ot.tag_id = ?', array(get_class($object), $object->id, $tag->id))
      ->fetchOne();

    return $objectTag;    
  }


  public function addTagByName($name, $visible=true)
  {
    $object = $this->getInvoker();

    if (!$object->exists())
    {
      throw new Exception("Can't add Tag for new object");
    }
    

    //look for existing tag
    $parts = explode(':', $name);
    
    if (count($parts) == 3)
    {
      $tag = TagTable::getByTripleQuery($parts[0], $parts[1], $parts[2])->fetchOne();
    }
    else
    {
      $tag = Doctrine::getTable('Tag')->findOneByName($name);
    }

    
    $db = Doctrine_Manager::connection();

    try
    {
      $db->beginTransaction();

      if ($tag)
      {
        //make sure obhect does't already have tag
        if ($this->hasObjectTag($tag))
        {
          return false;
        }
      }
      else
      {
        //create new tag
        $tag = new Tag;
        
        if (count($parts) == 3)
        {
          $tag->triple_namespace = $parts[0];
          $tag->triple_predicate = $parts[1];
          $tag->triple_value = $parts[2];
          $tag->is_visible = $visible;
        }
        else
        {
          $tag->name = trim($name);
          $tag->is_visible = $visible;
        }
  
        $tag->save();
      }
       
      //link object to Tag
      $objectTag = new ObjectTag;
      $objectTag->object_model = get_class($object);
      $objectTag->object_id = $object->id;
      $objectTag->Tag = $tag;
      $objectTag->save();
      
      $db->commit();
    }
    catch (Exception $e)
    {
      $db->rollback();
      throw $e;
    }

    
    return $objectTag;
  }
  
  
  public function addTagByTriple($namespace, $predicate, $value, $visible=true)
  {
    $object = $this->getInvoker();

    //check for existing Tag
    $tag = LsQuery::getByModelAndFieldsQuery('Tag', array(
      'triple_namespace' => $namespace,
      'triple_predicate' => $predicate,
      'triple_value' => $value
    ))->fetchOne();


    $db = Doctrine_Manager::connection();
    
    try
    {
      $db->beginTransaction();

      if ($tag)
      {
        if ($this->hasObjectTag($tag))
        {
          return false;
        }
      }
      else
      {
        $tag = new Tag;
        $tag->triple_namespace = $namespace;
        $tag->triple_predicate = $predicate;
        $tag->triple_value = $value;
        $tag->is_visible = $visible;
        $tag->save();
      }
  
      //link object to Tag
      $objectTag = new ObjectTag;
      $objectTag->object_model = get_class($object);
      $objectTag->object_id = $object->id;
      $objectTag->Tag = $tag;
      $objectTag->save();
      
      $db->commit();
    }
    catch (Exception $e)
    {
      $db->rollback();
      throw $e;
    }
    
    
    return $objectTag;
  }

  
  public function removeTagByName($name)
  {
    $parts = explode(':', $name);
    
    if (count($parts) == 3)
    {
      if (!$tag = TagTable::getByTripleQuery($parts[0], $parts[1], $parts[2])->fetchOne())
      {
        return false;
      }
    }
    else
    {
      if (!$tag = Doctrine::getTable('Tag')->findOneByName($name))
      {
        return false;
      }
    }
    
    if (!$objectTag = $this->getObjectTag($tag))
    {
      return false;
    }
    
    $objectTag->delete();
    
    return true;
  }
  
  
  public function removeTagByTriple($namespace, $predicate, $value)
  {
    if (!$tag = $this->getTripleTagsQuery($namespace, $predicate, $value))
    {
      return false;
    }
    
    if (!$objectTag = $this->getObjectTag($tag))
    {
      return false;
    }
    
    $objectTag->delete();
    
    return true;
  }
  
  
  public function removeAllTags()
  {
    $object = $this->getInvoker();

    if (!$object->exists())
    {
      throw new Exception("Can't remove Tags from new object");
    }

    return LsDoctrineQuery::create()
      ->from('ObjectTag ot')
      ->where('ot.object_model = ? AND ot.object_id = ?', array(get_class($object), $object->id))
      ->delete()
      ->execute();
  }


  public function getTagModificationsQuery()
  {
    $object = $this->getInvoker();

    return LsDoctrineQuery::create()
      ->from('Modification m')
      ->leftJoin('m.User u')
      ->leftJoin('m.Field f')
      ->where('m.object_model = ?', 'ObjectTag')
      ->addWhere('f.field_name = ?', 'tag_id') 
      ->addWhere('(m.is_create = ? AND EXISTS( SELECT id FROM modification_field mf WHERE mf.modification_id = m.id AND mf.field_name = ? AND mf.new_value = ?)  AND EXISTS( SELECT id FROM modification_field mf WHERE mf.modification_id = m.id AND mf.field_name = ? AND mf.new_value = ?)) OR (m.is_delete = ? AND EXISTS( SELECT id FROM modification_field mf WHERE mf.modification_id = m.id AND mf.field_name = ? AND mf.old_value = ?) AND EXISTS( SELECT id FROM modification_field mf WHERE mf.modification_id = m.id AND mf.field_name = ? AND mf.old_value = ?))', array(true, 'object_model', get_class($object), 'object_id', $object->id, true, 'object_model', get_class($object), 'object_id', $object->id))
      ->orderBy('m.id DESC');
  }
}