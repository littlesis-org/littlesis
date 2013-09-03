<?php

class helpComponents extends sfComponents
{
  public function executeHelpmenu()
  {

    $this->items = array(
          "beginner" => array("name" => "Beginner","children" => array(   
            "beginnerProfiles" => array("name" => "Profiles"),
            "beginnerRelationships" => array("name" => "Relationships"),  
            "sources" => array("name" => "Sources"),
            "beginnerLists" => array("name" => "Lists"),
            "connect" => array("name" => "Connect"),
            "account" => array("name" => "Account"))
           ),
          "advanced" => array("name" => "Advanced","children" => array(          
            "advancedProfiles" => array("name" => "Profiles"),
            "advancedRelationships" => array("name"=>"Relationships"),
            "advancedLists" => array("name"=>"Lists"),
            "addBulk" => array("name" =>"Add Bulk"),
            "matchDonations" => array("name" =>"Match Donations"))
          ),
          "index" => array("name" => "Help Home"),  
          );


  }
}