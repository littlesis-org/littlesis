<?php

class TrimmableListener extends Doctrine_Record_Listener
{
  protected $_options = array();

  public function __construct(array $options)
  {
    $this->_options = $options;
  }

  private function _trim(Doctrine_Record $record)
  {
    foreach ($this->_options['fields'] as $field)
    {    
      if ($record->rawGet($field) != trim($record->rawGet($field)))
      {
        $record->set($field, trim($record->rawGet($field)), false);
      }
    }  
  }

  public function preSave(Doctrine_Event $event)
  {
    $this->_trim($event->getInvoker());
  }

  public function preUpdate(Doctrine_Event $event)
  {
    $this->_trim($event->getInvoker());
  }

  public function preInsert(Doctrine_Event $event)
  {
    $this->_trim($event->getInvoker());
  }
}
