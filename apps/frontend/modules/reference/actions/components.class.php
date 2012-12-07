<?php

class referenceComponents extends sfComponents
{
  public function executeList()
  {
    if ($this->object instanceOf Doctrine_Record)
    {
      //use special method if possible
      $refs = method_exists($this->object, 'getAllReferences') ? $this->object->getAllReferences() : $this->object->getReferencesByFields(null, Doctrine::HYDRATE_ARRAY);
    }
    else
    {
      //check table for special method in table
      $table = $this->model . 'Table';

      if (method_exists($table, 'getAllReferencesById'))
      {
        $refs = call_user_func(array($table, 'getAllReferencesById'), $this->object['id']);
      }
      else
      {
        $refs = ReferenceTable::getByModelAndId($this->model, $this->object['id']);
      }
    }    
    
    $this->pager = new LsDoctrinePager($refs, $page=1, $num=10);
  }
}