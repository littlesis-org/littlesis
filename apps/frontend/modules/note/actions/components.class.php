<?php

class noteComponents extends sfComponents
{
  public function executeRecordSample()
  {
    $model = isset($this->model) ? $this->model : get_class($this->record);

    $this->actions = array(array(
      'text' => 'see all',
      'url' => $this->more_uri ? $this->more_uri : $this->record->getInternalUrl('notes')
    ));

    if ($this->getUser()->isAuthenticated())
    {
      $class = ($model == 'LsList') ? 'List' : $model;
      $this->actions[] = array(
         'text' => 'write a note',
          'url' => 'home/notes?compose=1&' . strtolower($class) . '_id=' . $this->record['id']
        );
      $this->actions[] = array(
         'text' => 'note all',
          'credential' => 'editor',
          'url' => 'home/notes?compose=1&analysts=1&' . strtolower($class) . '_id=' . $this->record['id']
      );
    }
    
    $attribute = strtolower($model) . '_ids';
    
    $s = new LsSphinxClient(1, 5);
    $s->setFilter($attribute, array($this->record['id']));

    if (!$currentUserId = sfGuardUserTable::getCurrentUserId())
    {
      $currentUserId = 0;
    }

    $s->setFilter('visible_to_user_ids', array_unique(array(0, $currentUserId)));
    
    $this->notes = NoteTable::getSphinxRecords($s, null, Doctrine::HYDRATE_ARRAY);
  }
}