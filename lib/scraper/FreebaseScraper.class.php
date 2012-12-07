<?php

class FreebaseScraper extends Scraper
{
  
	protected $years = array('2008');
	protected $refreshDays = 90;	
	protected $lookbackDays = 365;	
	protected $forceScaper = false;	
  
  public function execute()
	{		
		//loop through entities
		$entities = $this->getEntitiesByExtension('Org')->execute();

		if($entities->count()){
			foreach ($entities as $count => $entity)
			{
				//get DB connection for transactions		
				try 
				{
          
					//begin transaction
					$this->db->beginTransaction();

					$this->printDebug("\n***** Searching entity: " . $entity->getName() . " *****");
          $this->printDebug("Memory used: " . LsNumber::makeBytesReadable(memory_get_usage()) );
          $this->printDebug("Now: ". date('l jS \of F Y h:i:s A') );
          
          /*
					if ($this->hasMeta($entity->id, 'refesh_time') && time() < (int)$this->getMeta($entity->id, 'refesh_time') && !$this->forceScaper) 
					{
            $this->printDebug("Refresh time: " . date('l jS \of F Y h:i:s A', (int)$this->getMeta($entity->id, 'refesh_time') ) );
						$this->printDebug($entity->name . " already scraped; skipping");
						$this->db->rollback();
						continue;
					}
          */
					
					$this->import($entity);
	
					
					if ($this->limit === $count) { break; }		
					//if ($this->testMode) { continue; }		
	
					//commit transaction
					//$this->db->commit();
          //die();

          /*
          $refresh_days = time() + ($this->refreshDays * 24 * 60 * 60);
          $last_scraped = time();
          $this->saveMeta($entity->id, 'refesh_time', $refresh_days);					
          $this->saveMeta($entity->id, 'last_scraped', $last_scraped);					
					$this->printDebug( $entity->name . ": OK");
          */
				}
				catch (Exception $e)
				{
					//something bad happened, rollback
					$this->db->rollback();		
          throw $e;
				}
			}
		}
		else{
			$this->printDebug('No entities found on database'); 
		}
		
	}

  
	public function getEntitiesByExtension($extension = 'Person'){
    $q = EntityTable::getByExtensionQuery($extension);
    
    if($this->limit){
      $q->limit($this->limit);
    }
		return $q;
	}
  
  
  protected function import(Entity $entity){

    $freebase = new LsFreebase;
    $respose = $freebase->read( $this->generateQuery($entity) );
    
    print_r($respose);
  }
  
  /*
    "/organization/organization_member"
    "/government/governmental_body"                
    "/government/government_agency"
    "/government/politician"
    "/government/political_appointer"            
    "/business/company_founder"
    "/business/board_member"
    "/business/company"          
    "/people/person"
    "/education/educational_institution"
    "/education/university"
  */
  protected function generateQuery(Entity $entity){

    $extensions = $entity->getExtensions();    
    
    foreach($extensions as $extension){
      
      echo $extension . "\n";
      switch($extension){
        //level 1
        case 'Person': //done
          $json = array('name' => ''.$entity->name.'', 
                        'type' => '/people/person',
                        '*' => null,
                       );
        break;
          
        case 'Org':
          $json = array('name' => ''.$entity->name.'', 
                        'type' => '/organization',
                        'gender' => 'null',
                        'place_of_birth' => 'null',
                       );
        break;

        
        //level 2
        case 'PoliticalCandidate':
          $type = "/government/politician";        
        break;
        
        case 'ElectedRepresentative':
          $type = "/government/politician";        
        break;
        
        case 'Business':
          $type = "/business/company";        
        break;

        case 'GovernmentBody':
          $type = "/government/governmental_body";        
        break;

        case 'School':
          $type = "/government/governmental_body";        
        break;

        case 'Philanthropy':
          $type = "/government/governmental_body";        
        break;

        case 'NonProfit':
          $type = "/government/governmental_body";        
        break;
        
        case 'PoliticalFundraising':
          $type = "/government/governmental_body";        
        break;

        case 'BusinessPerson':
          $type = "/people/person";        
        break;

        case 'Lobbyist':
          $type = "/government/governmental_body";        
        break;
        
        //level 3
        case 'PrivateCompany':
          $type = "/government/governmental_body";        
        break;

        case 'PublicCompany':
          $type = "/government/governmental_body";        
        break;

        case 'IndustryTrade':
          $type = "/government/governmental_body";        
        break;

        case 'LawFirm':
          $type = "/government/governmental_body";        
        break;

        case 'LobbyingFirm':
          $type = "/government/governmental_body";        
        break;

        case 'PublicRelationsFirm':
          $type = "/government/governmental_body";        
        break;

        case 'IndividualCampaignCommittee':
          $type = "/government/governmental_body";        
        break;

        case 'Pac':
          $type = "/government/governmental_body";        
        break;

        case 'MediaOrg':
          $type = "/government/governmental_body";        
        break;

        case 'OtherCampaignCommittee':
          $type = "/government/governmental_body";        
        break;

        case 'ThinkTank':
          $type = "/government/governmental_body";        
        break;

        case 'Cultural':
          $type = "/government/governmental_body";        
        break;

        case 'SocialClub':
          $type = "/government/governmental_body";        
        break;
        
        case 'ProfessionalAssociation':
          $type = "/government/governmental_body";        
        break;
        
        case 'PoliticalParty':
          $type = "/government/governmental_body";        
        break;
        
        case 'LaborUnion':
          $type = "/government/governmental_body";        
        break;
        
        case 'Gse':
          $type = "/government/governmental_body";        
        break;        
      }  
      break;
    }  
    return $json;
  }
}

