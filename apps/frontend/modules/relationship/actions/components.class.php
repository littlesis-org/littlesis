<?php

class relationshipComponents extends sfComponents
{
  public function executeLobbyFilings()
  {
    $page = $this->page ? $this->page : 1;
    $num = $this->num ? $this->num : 10;
    
    $q = Lobbying::getLobbyFilingsByRelationshipIdQuery($this->relationship['id'])
      ->leftJoin('f.LobbyIssue i')
      ->leftJoin('f.Lobbyist l')
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);

    $this->filing_pager = new LsDoctrinePager($q, $page, $num);
  }
  
  
  public function executeFecFilings()
  {
    $page = $this->page ? $this->page : 1;
    $num = $this->num ? $this->num : 10;
    
    $q = RelationshipTable::getFecFilingsByIdQuery($this->relationship['id']);

    $this->filing_pager = new LsDoctrinePager($q, $page, $num);    
  }


  public function executeFedspendingFilings()
  {
    $page = $this->page ? $this->page : 1;
    $num = $this->num ? $this->num : 10;
    
    $q = RelationshipTable::getFedspendingFilingsByIdQuery($this->relationship['id']);

    $this->filing_pager = new LsDoctrinePager($q, $page, $num);    
  }

  
  public function executeToolbarCreateForm()
  {
    /*
    $db = Doctrine_Manager::connection();
    $sql = "SELECT l.id, l.name, COUNT(le.id) AS num FROM ls_list l " . 
           "LEFT JOIN ls_list_entity le ON le.list_id = l.id " .
           "WHERE l.is_admin = 0 AND l.is_deleted = 0 AND le.is_deleted = 0 " .
           "GROUP BY l.id " .
           "ORDER BY num DESC";
    $stmt = $db->execute($sql);
    $this->lists = $stmt->fetchAll(PDO::FETCH_ASSOC);
    array_unshift($this->lists, array("id" => "", "name" => ""));
    */
  }
}