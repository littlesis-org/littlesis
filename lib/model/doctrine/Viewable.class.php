<?php

class Viewable extends Doctrine_Template
{
  public function onObjectDelete()
  {
    $this->deleteAllViews(); 
  }
  
  
  public function deleteAllViews()
  {
    $object = $this->getInvoker();

    if (!$object->exists())
    {
      throw new Exception("Can't remove UserViews for new object");
    }

    return LsDoctrineQuery::create()
      ->from('UserView v')
      ->where('v.object_model = ? AND v.object_id = ?', array(get_class($object), $object->id))
      ->delete()
      ->execute();
  }  
}