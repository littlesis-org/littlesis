<?php

class ReferenceableHydrationListener extends Doctrine_Record_Listener
{
  public function preHydrate(Doctrine_Event $event)
  {
    $table = $event->getInvoker();    
    $columns = $table->getColumns();
    
    $data = $event->data;

    foreach ($data as $key => $value)
    {
      if (!is_null($value) && isset($columns[$key]) && $columns[$key]['type'] == 'integer')
      {
        if ($value < 2147483648)
        {
          $data[$key] = (int) $value;
        }
        else
        {
          $data[$key] = (float) $value;
        }
      }    
    }

    $event->data = $data;    
  }
}

?>