<?php

class Favoriteable extends Doctrine_Template
{
  public function isFavorite()
  {
    $user = sfContext::getInstance()->getUser();
    
    if (!$user->isAuthenticated())
    {
      return false;
    }
    

    $record = $this->getInvoker();

    if (!$record->exists())
    {
      throw new Exception("Can't check if a new record is a favorite");
    }


    $q = LsDoctrineQuery::create()
      ->from('UserFavorite f')
      ->where('f.user_id = ? AND f.object_model = ? AND f.object_id = ?', array($user->getGuardUser()->id, get_class($record), $record->id));
    
    return (bool) $q->count();
  }


  public function onObjectDelete()
  {
    $this->deleteAllFavorites(); 
  }
  
  
  public function mergeFrom(Doctrine_Record $r)
  {
    $object = $this->getInvoker();

    if (!$r->exists() || !$object->exists())
    {
      foreach ($r->getUserFavoritesQuery()->execute() as $favorite)
      {
        $q = LsDoctrineQuery::create()
          ->from('UserFavorite uf')
          ->where('uf.object_model = ? AND uf.object_id AND uf.user_id = ?', array(get_class($object), $object->id, $favorite->user_id));

        if (!$q->count())
        {
          $favorite->setObject($object);
          $favorite->save();
        }
      }
    }
  }
  
  
  public function getUserFavoritesQuery()
  {
    $object = $this->getInvoker();

    return LsDoctrineQuery::create()
      ->from('UserFavorite uf')
      ->where('uf.object_model = ? AND uf.object_id = ?', array(get_class($object), $object->id));
  }
  
  
  public function deleteAllFavorites()
  {
    $object = $this->getInvoker();

    if (!$object->exists())
    {
      throw new Exception("Can't remove UserFavorites for new object");
    }

    return LsDoctrineQuery::create()
      ->from('UserFavorite f')
      ->where('f.object_model = ? AND f.object_id = ?', array(get_class($object), $object->id))
      ->delete()
      ->execute();
  }
}