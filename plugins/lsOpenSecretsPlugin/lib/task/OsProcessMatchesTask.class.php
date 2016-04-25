<?php

require_once(sfConfig::get('sf_root_dir') . '/lib/task/LsTask.class.php');

class OsProcessMatchesTask extends LsTask
{
  protected
    $db = null,
    $rawDb = null,
    $browser = null,
    $matches = array('new' => array(), 'old' => array()),
    $debugMode = null,
    $startTime = null,
    $databaseManager = null,
    $fecImageBaseUrl = 'http://docquery.fec.gov/cgi-bin/fecimg/?',
    $fecCommitteeBaseUrl = 'http://docquery.fec.gov/cgi-bin/com_detail/',
    $fecSearchUrlPattern = 'http://docquery.fec.gov/cgi-bin/qindcont/1/(lname|MATCHES|:%s:)|AND|(fname|MATCHES|:%s*:)';


  protected function configure()
  {
    $this->namespace        = 'opensecrets';
    $this->name             = 'process-matches';
    $this->briefDescription = 'Creates and updates donation relationships based on matches between entities and OpenSecrets donations';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of matches to process', 100);
  }


  protected function execute($arguments = array(), $options = array())
  {
    if (!$this->safeToRun())
    {
      print("Script already running!\n");
      die;
    }
    
    $this->init($arguments, $options);

    //get new and nulled matches
    $matches = $this->getMatches($options['limit']);

    //group matches by entity id
    $entities = $this->groupMatchesByEntity($matches);

    //update entity relationships
    foreach ($entities as $id => $matches)
    {
      $this->processEntity($id, $matches['new'], $matches['old']);
    }
    
    print("\n\nProcessed matches for " . count($entities) . " entities in " . (microtime(true) - $this->startTime) . " s\n");

    $this->unlockEntities();
  }
  
  
  protected function init($arguments, $options)
  {
    $this->startTime = microtime(true);

    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $this->databaseManager = new sfDatabaseManager($configuration);
    $this->databaseManager->initialize($configuration);
    $db = $this->databaseManager->getDatabase('main');
    $this->db = Doctrine_Manager::connection($db->getParameter('dsn'), 'main');
    $rawDb = $this->databaseManager->getDatabase('raw');
    $this->rawDb = Doctrine_Manager::connection($rawDb->getParameter('dsn'), 'raw');  

    //this avoids a context error when clearing the cache
    sfContext::createInstance($configuration);

    $this->debugMode = $options['debug_mode'];
    $this->browser = new sfWebBrowser;
  }
  

  public function getMatches($limit)
  {
    //get entities first so limit can apply to number of entities
    $sql = 'SELECT DISTINCT et.entity_id FROM os_entity_transaction et ' . 
           'LEFT JOIN entity e ON (e.id = et.entity_id) ' . 
           'WHERE et.is_synced = 0 AND e.is_deleted = 0 ' . 
           'LIMIT ' . $limit;
    $stmt = $this->db->execute($sql);
    $entityIds = $stmt->fetchAll(PDO::FETCH_COLUMN);    

    if (!count($entityIds))
    {
      return array();
    }

    //get unprocessed verified matches and deverified processed matches
    $sql = 'SELECT * FROM os_entity_transaction WHERE entity_id IN (' . implode(',', $entityIds) . ') AND is_synced = 0';
    $stmt = $this->db->execute($sql);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }


  public function groupMatchesByEntity($matches)
  {
    $entities = array();
    
    foreach ($matches as $match)
    {
      $type = $match['is_verified'] ? 'new' : 'old';
      $this->matches[$type][] = $match; //saving for later

      if (!isset($entities[$match['entity_id']]))
      {
        $entities[$match['entity_id']] = array('new' => array(), 'old' => array());
      }

      $entities[$match['entity_id']][$type][] = $match['cycle'] . ':' . $match['transaction_id'];
    }
    
    return $entities;
  }
  
  
  public function processEntity($id, $newTrans, $oldTrans)
  {
    //get person names so we can make sure added donations are from the right person
    $sql = 'SELECT * FROM person WHERE entity_id = ?';
    $stmt = $this->db->execute($sql, array($id));

    if (!$donorPerson = $stmt->fetch(PDO::FETCH_ASSOC))
    {
      if ($this->debugMode)
      {
        print("* Can't find Person record for donor with entity_id " . $id . "; skipping...");
      }
      
      return;
    }


    if ($this->debugMode)
    {
      print("\n=== Processing entity " . $id . " (" . PersonTable::getLegalName($donorPerson) . ") ===\n");
    }


    $recipients = array();

    //get donations from all the newly matched transactions
    $newDonations = $this->getDonations($newTrans);

    foreach ($newDonations as $donation)
    {
      if (!$this->namesAreCompatible($donorPerson, $donation))
      {
        if ($this->debugMode)
        {
          print("* Skipping donation with incompatible donor name: " . $donation['donor_name'] . "\n");
        }
        
        continue;
      }

      $cycle = $donation['cycle'];
      $recipientId = $donation['recipient_id'];
      
      if (isset($recipients[$cycle][$recipientId]['new']))
      {
        $recipients[$cycle][$recipientId]['new'][] = $donation;
      }
      else
      {
        if (!isset($recipients[$cycle]))
        {
          $recipients[$cycle] = array();
        }
        
        $recipients[$cycle][$recipientId] = array();
        $recipients[$cycle][$recipientId]['new'] = array($donation);
        $recipients[$cycle][$recipientId]['old'] = array();
      }
    }


    //get donations from all the old transactions
    $oldDonations = $this->getDonations($oldTrans);

    foreach ($oldDonations as $donation)
    {
      $cycle = $donation['cycle'];
      $recipientId = $donation['recipient_id'];
      
      if (isset($recipients[$cycle][$recipientId]['old']))
      {
        $recipients[$cycle][$recipientId]['old'][] = $donation;
      }
      else
      {
        if (!isset($recipients[$cycle]))
        {
          $recipients[$cycle] = array();
        }
        
        $recipients[$cycle][$recipientId] = array();
        $recipients[$cycle][$recipientId]['old'] = array($donation);
        $recipients[$cycle][$recipientId]['new'] = array();
      }
    }


    //if there are NO already-processed matches, and no matches to remove,
    //ie, if we're going from no matches to any number of matches,
    //we can delete existing donation relationships for this entity
    $deleteRels = false;

    if (!count($oldDonations))
    {
      $sql = 'SELECT COUNT(*) FROM os_entity_transaction WHERE entity_id = ? AND is_processed = 1';
      $stmt = $this->db->execute($sql, array($id));
      
      if (!$stmt->fetch(PDO::FETCH_COLUMN))
      {
        $deleteRels = true;
      }
    }
    
    if ($deleteRels)
    {
      if ($this->debugMode)
      {
        print("- Removing old donation relationships...\n");
      }

      //first get ids
      $sql = 'SELECT DISTINCT r.id FROM relationship r ' . 
             'LEFT JOIN fec_filing f ON (f.relationship_id = r.id) ' .
             'WHERE r.entity1_id = ? AND r.category_id = ? AND r.is_deleted = 0 ' . 
             'AND f.id IS NOT NULL';
      $stmt = $this->db->execute($sql, array($id, RelationshipTable::DONATION_CATEGORY));
      $relIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

      if (count($relIds))
      {
        //soft delete them
        $sql = 'UPDATE relationship SET is_deleted = 1, updated_at = ? WHERE id IN (' . implode(',', $relIds) . ')';
        $params = array(LsDate::getCurrentDateTime());
        $this->db->execute($sql, $params);
    
        //create modification records of the deletions
        $sql = 'INSERT INTO modification (object_model, object_id, object_name, is_delete, created_at, updated_at) ' .
               'VALUES ';
        $params = array();
        foreach ($relIds as $relId)
        {
          $sql .= '(?, ?, ?, ?, ?, ?), ';
          $now = LsDate::getCurrentDateTime();
          $params = array_merge($params, array('Relationship', $relId, 'Relationship ' . $relId, true, $now, $now));
        }
        $sql = substr($sql, 0, strlen($sql) - 2);
        $stmt = $this->db->execute($sql, $params);
      }
    }


    //make sure the entity hasn't been deleted in the meantime!
    $sql = 'SELECT id FROM entity WHERE id = ? AND is_deleted = 0';
    $stmt = $this->db->execute($sql, array($id));
    
    if (!$stmt->fetch(PDO::FETCH_COLUMN))
    {
      //skip to the end
      $recipients = array();
    }


    //create filings/relationships for each cycle-recipient pair
    foreach ($recipients as $cycle => $recipients)
    {
      foreach ($recipients as $recipientId => $donations)
      {
        //if it's a committee recipient, try to determine
        //whether it belongs to a candidate
        if (strpos($recipientId, 'C') === 0)
        {
          $recipientId = $this->getFinalRecipientIdByCycleAndCommitteeId($cycle, $recipientId);
        }


        //find the entity with this recipient id, or generate a new one
        if (!$recipientEntity = $this->getEntityByRecipientId($recipientId))
        {
          if ($this->debugMode)
          {
            print("* Couldn't find or create entity for recipient " . $recipientId . "; skipping...\n");
          }

          continue;
        }
  
  
        //create committee entity and position relationship between it and the candidate, if necessary
        //DISABLED, FOR NOW
        //$this->createCampaignCommittee($recipientEntity['id'], $recipientId);
  
  
        if ($this->debugMode)
        {
          print("Updating donation relationship with " . $recipientEntity['name'] . "...\n");
        }


        //see if there's already a relationship
        Doctrine_Manager::getInstance()->setCurrentConnection('main');
        $q = LsDoctrineQuery::create()
          ->from('Relationship r')
          ->where('r.entity1_id = ? AND r.entity2_id = ? AND r.category_id = ?', array($id, $recipientEntity['id'], RelationshipTable::DONATION_CATEGORY));
        $rel = $q->fetchOne();

        //create relationship if there's not already one
        if (!$rel)
        {
          //but if there aren't any new donations, then we skip this recipient
          //THIS SHOULD NOT TYPICALLY HAPPEN, BECAUSE NO NEW DONATIONS MEANS
          //THERE ARE OLD DONATIONS TO REMOVE, WHICH MEANS THERE SHOULD BE
          //EXISTING RELATIONSHIPS... they may have been deleted         
          if (!count($donations['new']))
          {
            if ($this->debugMode)
            {
              print("* No relationships found, and no new donations to process, so skipping it...\n");
            }

            continue;
          }

          if ($this->debugMode)
          {
            print("+ Creating new donation relationship\n");
          }
  
          $rel = new Relationship;
          $rel->entity1_id = $id;
          $rel->entity2_id = $recipientEntity['id'];
          $rel->setCategory('Donation');
          $rel->description1 = 'Campaign Contribution';
          $rel->description2 = 'Campaign Contribution';
          $rel->save();
        }
        
  
        //add new filings and references to the relationship
        foreach ($donations['new'] as $donation)
        {  
          $filing = new FecFiling;
          $filing->relationship_id = $rel->id;
          $filing->amount = $donation['amount'];
          $filing->fec_filing_id = $donation['fec_id'];
          $filing->crp_cycle = $donation['cycle'];
          $filing->crp_id = $donation['row_id'];
          $filing->start_date = $donation['date'];
          $filing->end_date = $donation['date'];
          $filing->is_current = false;
          $filing->save();

          if ($this->debugMode)
          {
            print("+ Added new FEC filing: " . $donation['fec_id'] . " (" . $donation['amount'] . ")\n");
          }
          
          //add reference if there's an fec_id
          if ($donation['fec_id'])
          {
            $ref = new Reference;
            $ref->object_model = 'Relationship';
            $ref->object_id = $rel->id;
            $ref->source = $this->fecImageBaseUrl . $donation['fec_id'];
            $ref->name = 'FEC Filing ' . $donation['fec_id'];
            $ref->save();
          }
        }


        //remove old filings from the relationship
        foreach ($donations['old'] as $donation)
        {
          if ($this->debugMode)
          {
            print("- Deleting FEC filing: {$donation['fec_id']}, {$donation['cycle']}, {$donation['row_id']} ({$donation['amount']})\n");
          }
  
          $sql = 'DELETE FROM fec_filing WHERE relationship_id = ? AND crp_cycle = ? AND crp_id = ?';
          $stmt = $this->db->execute($sql, array($rel->id, $donation['cycle'], $donation['row_id']));
        }

        
        //recompute fields based on filings
        if (!$rel->updateFromFecFilings())
        {
          if ($this->debugMode)
          {
            print("- Deleting donation relationship with no filings: " . $rel->id . "\n");
          }
  
          //no remaining filings for this relationship, so delete it!
          $rel->delete();
        }
        else
        {
          if ($this->debugMode)
          {
            print("Relationship " . $rel->id . " updated with " . $rel->filings . " filings totaling " . $rel->amount . "\n");
          }


          //add a reference to OS donation search for the relationship, if necessary
          $sql = 'SELECT COUNT(*) FROM reference ' . 
                 'WHERE object_model = ? AND object_id = ? AND name = ?';
          $stmt = $this->db->execute($sql, array('Relationship', $rel->id, 'FEC contribution search'));
         
          if (!$stmt->fetch(PDO::FETCH_COLUMN))
          {
            $ref = new Reference;
            $ref->object_model = 'Relationship';
            $ref->object_id = $rel->id;
            $ref->source = sprintf($this->fecSearchUrlPattern, strtoupper($donorPerson['name_last']), strtoupper($donorPerson['name_first']));
            $ref->name = 'FEC contribution search';
            $ref->save();
  
            if ($this->debugMode)
            {
              print("+ Added reference to FEC contribution search\n");
            }
          }
        }
        
        //clear cache for recipient
        LsCache::clearEntityCacheById($recipientEntity['id']);
      }
    }
    
    //update os_entity_transaction
    $sql = 'UPDATE os_entity_transaction SET is_processed = is_verified, is_synced = 1 WHERE entity_id = ?';
    $stmt = $this->db->execute($sql, array($id));      

    //make sure that all removed matches result in deleted fec filings and updated relationships for this entity
    $this->cleanupFecFilings($id, $oldDonations);

    //update opensecrets categories based on matched donations
    $this->printDebug("Updating industry categories based on matched donations...");
    $newCategories = OsPerson::updateCategories($id);
    foreach ($newCategories as $categoryId)
    {
      $this->printDebug("+ Added industry category: " . $categoryId);
    }
        
    //clear cache for donor
    LsCache::clearEntityCacheById($id);
  }


  public function getEntityByRecipientId($id)
  {
    //try to find entity by crp id
    if (!$entity = $this->getEntityByCrpId($id))
    {
      if (substr($id, 0, 1) == 'C')
      {                
        if (!$entity = $this->getCommitteeEntityByFecId($id))
        {
          return null;
        }
      }
      else
      {
        if (!$entity = $this->getCandidateEntityByCrpId($id))
        {
          return null;
        }
      }
    }
    
    return $entity;
  }


  public function getEntityByCrpId($id)
  {
    if (strpos($id, 'C') === 0)
    {
      //try political fundraising committees
      $sql = 'SELECT e.* FROM political_fundraising pf LEFT JOIN entity e ON (pf.entity_id = e.id) WHERE pf.fec_id = ? AND e.is_deleted = 0';
      $stmt = $this->db->execute($sql, array($id));
      
      return  $stmt->fetch(PDO::FETCH_ASSOC);    
    }


    //try political candidates
    $sql = 'SELECT e.* FROM political_candidate pc LEFT JOIN entity e ON (pc.entity_id = e.id) ' . 
           'WHERE pc.crp_id = ? AND e.is_deleted = 0';
    $stmt = $this->db->execute($sql, array($id));
    
    if ($result = $stmt->fetch(PDO::FETCH_ASSOC))
    {
      return $result;
    }

    
    //try elected representatives
    $sql = 'SELECT e.* FROM elected_representative er LEFT JOIN entity e ON (er.entity_id = e.id) ' . 
           'WHERE er.crp_id = ? AND e.is_deleted = 0';
    $stmt = $this->db->execute($sql, array($id));
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }


  //uses the supplied FEC ID to get the current committee name
  //and then return an existing or new entity with that name
  public function getCommitteeEntityByFecId($id)
  {
    $name = null;

    //get name from FEC.gov
    $url = $this->fecCommitteeBaseUrl . $id;
    $this->browser->get($url);
    
    if ($this->browser->responseIsError())
    {
      return null;
    }

    $page = $this->browser->getResponseText();

    if (!preg_match('#<FONT SIZE=5><B>([^<]+)</B></FONT>#', $page, $match))
    {
      return null;
    }
    
    $name = LsLanguage::titleize($match[1]);
        
    //see if there's an entity with PoliticalFundraising extension and this name
    $sql = 'SELECT e.* FROM entity e ' . 
           'LEFT JOIN alias a ON (a.entity_id = e.id) ' .
           'WHERE a.name = ? AND e.primary_ext = ? AND e.is_deleted = 0';
    $stmt = $this->db->execute($sql, array($name, 'Org'));

    if (!$entity = $stmt->fetch(PDO::FETCH_ASSOC))
    {
      if ($this->debugMode)
      {
        print("+ Creating new entity for committee " . $id . " (" . $name . ")\n");
      }

      $entity = new Entity;
      $entity->addExtension('Org');
      $entity->addExtension('PoliticalFundraising');
      $entity->name = $name;
      $entity->fec_id = $id;
      $entity->save();

      //get CRP's names for this committee
      //$sql = 'SELECT DISTINCT name from os_committee WHERE committee_id = ?';
      //$stmt = $this->rawDb->execute($sql, array($id));
      //$names = $stmt->fetchAll(PDO::FETCH_COLUMN);      
      //$this->addAliasesToEntityById($entity['id'], $names);
    }
    
    return $entity;
  }


  public function getCandidateEntityByCrpId($id)
  {
    if (!count($candidates = $this->getOsCandidatesById($id)))
    {
      return null;
    }

    //try to find entity by fec_id
    $fecIds = array();
    foreach ($candidates as $candidate)
    {
      if (!$candidate['fec_id'] || !$candidate['name_last']) { continue; }
      
      if ($entity = $this->getCandidateEntityByFecId($candidate['fec_id'], $candidate['name_last']))
      {
        return $entity;
      }
      
      $fecIds[] = $candidate['fec_id'];
    }

    $candidate = $candidates[0];

    if ($this->debugMode)
    {
      print("+ Creating new entity for person " . $id . " (" . $candidate['name'] . ")\n");
    }

    $entity = new Entity;
    $entity->addExtension('Person');
    $entity->addExtension('PoliticalCandidate');
    $entity->name_last = $candidate['name_last'];
    $entity->name_first = $candidate['name_first'];
    $entity->name_middle = $candidate['name_middle'];
    $entity->name_suffix = $candidate['name_suffix'];
    $entity->crp_id = $id;
    
    foreach ($fecIds as $fecId)
    {
      $map = array('P' => 'pres_fec_id', 'S' => 'senate_fec_id', 'H' => 'house_fec_id');
      $code = substr($fecId, 0, 1);
  
      if (@$field = $map[$code])
      {
        $entity->$field = $fecId;
      }
    }
      
    $entity->save();
              
    if (!$district = PoliticalDistrictTable::getFederalDistrict($state, $district))
    {            
      if ($state = AddressStateTable::retrieveByText($state))
      {
        $district = new PoliticalDistrict;
        $district->state_id = $state['id'];
        $district->federal_district = $district;
        $district->save();
      }
    }
      
    if ($district)
    {
      $pc = $entity->getExtensionObject('PoliticalCandidate');

      $cd = new CandidateDistrict;
      $cd->candidate_id = $pc->id;
      $cd->district_id = $district->id;
      $cd->save();
    }            
    
    return $entity;
  }


  public function getCandidateEntityByFecId($id, $lastName)
  {
    $map = array('P' => 'pres_fec_id', 'S' => 'senate_fec_id', 'H' => 'house_fec_id');
    $code = substr($id, 0, 1);

    if (!in_array($code, array_keys($map)))
    {
      return false;
    }
    
    $field = $map[$code];

    $sql = 'SELECT e.* FROM entity e ' . 
           'LEFT JOIN political_candidate pc ON (pc.entity_id = e.id) ' . 
           'LEFT JOIN person p ON (p.entity_id = e.id) ' .
           'WHERE pc.' . $field . ' = ? AND p.name_last = ? AND e.is_deleted = 0';
    $stmt = $this->db->execute($sql, array($id, $lastName));
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
  

  public function getCandidateEntityByNameAndDistrict($first, $last, $state, $district)
  {
    //try to find entity by first, last, and district
    if (!$first || !$last || !$state || !$district)
    {
      return null;
    }

    $sql = 'SELECT e.* FROM political_district d LEFT JOIN address_state s ON (d.state_id = s.id) ' . 
           'LEFT JOIN representative_district rd ON (rd.district_id = d.id) ' .
           'LEFT JOIN elected_representative er ON (er.id = rd.representative_id) ' .
           'LEFT JOIN candidate_district cd ON (cd.district_id = d.id) ' .           
           'LEFT JOIN political_candidate pc ON (pc.id = cd.candidate_id) ' .
           'LEFT JOIN entity e ON (e.id = er.entity_id OR e.id = pc.entity_id) ' .
           'LEFT JOIN person p ON (p.entity_id = e.id) ' .
           'WHERE d.federal_district = ? AND s.abbreviation = ? AND p.name_last = ? AND p.name_first = ?';
    $stmt = $this->db->execute($sql, array($district, $state, $last, $first));
    
    return $stmt->fetch(PDO::FETCH_ASSOC);    
  }

  
  public function getOsCandidatesById($id)
  {
    $sql = 'SELECT * FROM os_candidate WHERE candidate_id = ?';
    $stmt = $this->rawDb->execute($sql, array($id));
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  
  //remove all existing donation relationships from an entity
  protected function removeExistingDonations($id)
  {
    if ($this->debugMode)
    {
      print("+ First match for entity; removing existing donations...\n");
    }

    $sql = 'DELETE FROM relationship r WHERE entity1_id = ? AND category_id = ?';

    if (!$stmt = $db->execute($sql, array($id, RelationshipTable::DONATION_CATEGORY)))
    {
      throw new Exception("Couldn't delete donation relationships for entity " . $id);
    }    
  }
    
  
  protected function getDonations(Array $trans)
  {
    if (!count($trans))
    {
      return array();
    }

    $sql = 'SELECT * FROM os_donation FORCE INDEX(PRIMARY) WHERE (' . OsDonationTable::generateKeyClause('cycle', 'row_id', $trans) . ') AND recipient_id IS NOT NULL AND transaction_type <> ?';

    if (!$stmt = $this->rawDb->execute($sql, array('22y')))
    {
      throw new Exception("Couldn't get donations for " . implode(', ', $trans));
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  
  protected function getFinalRecipientIdByCycleAndCommitteeId($cycle, $committeeId)
  {
    $sql = 'SELECT recipient_id, candidate_id FROM os_committee WHERE cycle = ? AND committee_id = ?';
    $stmt = $this->rawDb->execute($sql, array($cycle, $committeeId));

    //if nothing found, return original
    if (!$row = $stmt->fetch(PDO::FETCH_NUM))
    {
      return $committeeId;
    }

    list($recipientId, $candidateId) = $row;

    //committee found, recipient id is different than original, return recipient id
    if ($recipientId != $committeeId)
    {
      return $recipientId;
    }
    
    //recipient id is same as original, and there's no candidate_id, so return original
    if (!$candidateId)
    {
      return $committeeId;
    }
    
    //get candidate id from candidate table
    $sql = 'SELECT candidate_id FROM os_candidate WHERE fec_id = ? AND cycle = ?';
    $stmt = $this->rawDb->execute($sql, array($candidateId, $cycle));
    
    if ($candidateId = $stmt->fetch(PDO::FETCH_COLUMN))
    {
      return $candidateId;
    }
    
    return $committeeId;
  }
  
  
  protected function createCampaignCommittee($entityId, $recipientId)
  {
    $sql = 'SELECT cycle, name, committee_id FROM os_committee WHERE recipient_id = ? AND committee_id <> recipient_id AND name IS NOT NULL';
    $stmt = $this->rawDb->execute($sql, array($recipientId));

    /*
    if (!count($committees = $stmt->fetchAll(PDO::FETCH_ASSOC)))
    {
      $sql = 'SELECT fec_id FROM os_candidate WHERE candidate_id = ?';
      $stmt = $this->rawDb->execute($sql, array($recipientId));
      
      if (count($fecIds = $stmt->fetchAll(PDO::FETCH_COLUMN)))
      {
        $sql = 'SELECT name, committee_id FROM os_committee WHERE candidate_id IN (\'' . implode('\',\'', $fecIds) . '\')';
        $stmt = $this->rawDb->execute($sql);
        $committees = $stmt->fetchAll(PDO::FETCH_ASSOC);
      }
    }
    */

    $committees = array();

    //group committees by committee_id, and get most recent name
    foreach ($stmt->fetchAll(PDO::FETCH_NUM) as $row)
    {
      list($cycle, $name, $committeeId) = $row;

      if (isset($committees[$committeeId]))
      {
        if ($cycle > $committees[$committeeId]['cycle'])
        {
          $committees[$committeeId]['cycle'] = $cycle;
          $committees[$committeeId]['name'] = $name;
        }

        $committees[$committeeId]['aliases'][] = $name;
      }
      else
      {
        $committees[$committeeId] = array('cycle' => $cycle, 'name' => $name, 'aliases' => array());
      }
    }


    foreach ($committees as $committeeId => $ary)
    {
      $name = $ary['name'];
      $aliases = array_unique($ary['aliases']);

      $sql = 'SELECT e.* FROM entity e LEFT JOIN political_fundraising p ON (p.entity_id = e.id) ' .
             'WHERE p.fec_id = ? AND e.is_deleted = 0';
      $stmt = $this->db->execute($sql, array($committeeId));
      
      if ($entity = $stmt->fetch(PDO::FETCH_ASSOC))
      {
        //check for an existing relationship
        $sql = 'SELECT COUNT(*) FROM relationship r ' . 
               'WHERE r.entity1_id = ? AND r.entity2_id = ? AND r.category_id = ? AND r.is_deleted = 0';
        $stmt = $this->db->execute($sql, array($entityId, $entity['id'], RelationshipTable::POSITION_CATEGORY));        

        $createRel = $stmt->fetch(PDO::FETCH_COLUMN) ? false : true;
      }
      else
      {
        Doctrine_Manager::getInstance()->setCurrentConnection('main');

        //create new entity and relationship
        $entity = new Entity;
        $entity->addExtension('Org');
        $entity->addExtension('PoliticalFundraising');
        $entity->name = $name;
        $entity->fec_id = $committeeId;
        $entity->save();

        if ($this->debugMode)
        {
          print("+ Created new entity for " . $name . " (" . $committeeId . ")\n");
        }
        
        $createRel = true;
      }
      
      if ($createRel)
      {
        Doctrine_Manager::getInstance()->setCurrentConnection('main');

        //create relationship
        $rel = new Relationship;
        $rel->setCategory('Position');
        $rel->entity1_id = $entityId;
        $rel->entity2_id = $entity['id'];
        $rel->description1 = 'Candidate';
        $rel->description2 = 'Political Fundraising Committee';
        $rel->is_executive = true;
        $rel->save();
        
        //create reference for the relationship
        $refName = 'FEC.gov - ' . $name;
        $refSource = $this->fecCommitteeBaseUrl . $committeeId;

        $sql = 'INSERT INTO reference (object_model, object_id, name, source) VALUES (?, ?, ?, ?)';
        $params = array('Relationship', $rel->id, $refName, $refSource);
        $stmt = $this->db->execute($sql, $params);
       
        if (!$stmt->rowCount())
        {
          throw new Exception("Couldn't insert Reference (" . implode(', ', $params) . ")");
        }
        
        if ($this->debugMode)
        {
          print("+ Created position relationship between candidate (entity " . $entityId . ") and committee (entity " . $entity['id'] . ")\n");
        }        
      }
      
      //create aliases if necessary
      $this->addAliasesToEntityById($entity['id'], $aliases);
    }            
  }
  
  
  public function addAliasesToEntityById($id, Array $aliases)
  {
    Doctrine_Manager::getInstance()->setCurrentConnection('main');

    $existingAliases = array_map('strtolower', EntityTable::getAliasNamesById($id, $includePrimary=true, $excludeContext=true));
    
    foreach ($aliases as $alias)
    {
      if (!in_array(strtolower($alias), $existingAliases))
      {
        $a = new Alias;
        $a->entity_id = $id;
        $a->name = $alias;
        $a->is_primary = false;
        $a->save();

        if ($this->debugMode)
        {
          print("+ Added alias " . $alias . " to entity " . $id . "\n");
        }
      }
    }        
  }
  
  
  public function unlockEntities()
  {
    $tenMinutesAgo = date('Y-m-d H:i:s', strtotime('-10 minutes'));
    $sql = 'UPDATE os_entity_transaction SET locked_by_user_id = NULL, locked_at = NULL WHERE locked_at < ? AND locked_at IS NOT NULL';
    $stmt = $this->db->execute($sql, array($tenMinutesAgo));
  } 
  
  
  public function namesAreCompatible($donorPerson, $donation)
  {
    //try last names
    if (trim(strtolower($donorPerson['name_last'])) != trim(strtolower($donation['donor_name_last'])))
    {
      return false;
    }
    
    //if last names match, it's decided by middle names
    return PersonTable::middleNamesAreCompatible($donorPerson['name_middle'], $donation['donor_name_middle']);
  }  
  
  
  protected function safeToRun()
  {
    $proc_id = getmypid();
    exec('ps aux -ww | grep symfony | grep :process-matches | grep -v ' . $proc_id . ' | grep -v grep', $status_arr);

    foreach ($status_arr as $status)
    {
      //sometimes the shell startup command also appears, which is fine (script is still safe to run)
      if (preg_match('/sh\s+\-c/isu', $status) == 0)
      {
        return false;
      }
    }

    return true;    
  }
  
  public function cleanupFecFilings($entity_id, $old_donations)
  {
    foreach ($old_donations as $donation)
    {
      $rel_ids = array();
      $filing_ids = array();

      //get relevant relationships to update and fec filings to delete
      $sql = "SELECT r.id rel_id, f.id filing_id FROM fec_filing f LEFT JOIN relationship r ON (f.relationship_id = r.id) " .
             "WHERE f.crp_id = ? AND f.crp_cycle = ? AND r.is_deleted = 0 AND (r.entity1_id = ? OR r.entity2_id = ?)";
      $params = array($donation["row_id"], $donation["cycle"], $entity_id, $entity_id);
      $stmt = $this->db->execute($sql, $params);
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach ($rows as $row)
      {
        $rel_ids[] = $row["rel_id"];
        $filing_ids[] = $row["filing_id"];
      }
      
      $rel_ids = array_unique($rel_ids);
      $filing_ids = array_unique($filing_ids);

      //delete fec filings if there are any
      if (count($filing_ids) > 0)
      {
        $sql = "DELETE FROM fec_filing WHERE id IN (" . implode(",", $filing_ids) . ")";
        $this->db->execute($sql);
      }
      
      //upate relationships
      foreach ($rel_ids as $rel_id)
      {
        if (Donation::updateRelationshipFromFecFilings($rel_id))
        {
          $this->printDebug("Updated relationship after removing fec filings: " . $rel_id);
        } 
        else 
        {
          $this->printDebug("- Deleted relationship after removing fec filings: " . $rel_id);
        }
      }
    }  
  }
}