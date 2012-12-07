<?php

class userComponents extends sfComponents
{  
  public function executePoints()
  {
    //$this->profile->refreshScore();
    //$this->stats = $this->profile->getShortSummary();
  }


  public function executeGroups()
  {
    $db = Doctrine_Manager::connection();

    //first get list of groups this user belongs to
    $sql = 'SELECT group_id FROM sf_guard_user_group WHERE user_id = ?';
    $stmt = $db->execute($sql, array($this->user->id));
    $groupIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($groupIds))
    {
      $sql = 'SELECT g.*, COUNT(ug.user_id) users FROM sf_guard_group g ' . 
             'LEFT JOIN sf_guard_user_group ug ON (ug.group_id = g.id) ' . 
             'WHERE g.id IN (' . implode(', ', $groupIds) . ') AND g.is_working = 1 AND g.is_private = 0 GROUP BY g.id ORDER BY users DESC';
      $stmt = $db->execute($sql);
      
      $this->groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else
    {
      $this->groups = array();
    }
  }
}