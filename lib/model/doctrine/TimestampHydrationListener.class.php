<?php

class TimestampHydrationListener extends Doctrine_Record_Listener
{
  public function preHydrate(Doctrine_Event $event)
  {
    $data = $event->data;

    foreach ($data as $key => $value)
    {
      if ($value === '0000-00-00 00:00:00')
      {
        $data[$key] = NULL;
      }
    }
    
    $event->data = $data;
  }
}