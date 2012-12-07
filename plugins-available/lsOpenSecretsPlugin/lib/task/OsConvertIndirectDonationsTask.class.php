<?php

class OsConvertIndirectDonationsTask extends sfTask
{
  protected
    $db = null,
    $rawDb = null,
    $debugMode = null,
    $startTime = null,
    $databaseManager = null,
    $fecImageBaseUrl = 'http://images.nictusa.com/cgi-bin/fecimg/?',
    $fecCommitteeBaseUrl = 'http://query.nictusa.com/cgi-bin/com_detail/';


  protected function configure()
  {
    $this->namespace        = 'opensecrets';
    $this->name             = 'convert-indirect-donations';
    $this->briefDescription = 'Creates direct donation relationships between entities and candidates based on old indirect donations';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Run task without saving data', false);
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of relationships to convert', 100);
    $this->addOption('merge', null, sfCommandOption::PARAMETER_REQUIRED, 'Merge relationships after converting', false);
    $this->addOption('merge_limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of relationships to merge', 100);
  }


  protected function execute($arguments = array(), $options = array())
  {
    $this->init($arguments, $options);

    $triples = $this->getRelationshipConversionTriples($options['limit']);

    foreach ($triples as $triple)
    {
      $this->convertRelationship($triple['id'], $triple['old_value'], $triple['new_value']);
    }


    print("\n\nConverted " . count($triples) . " relationships in " . (microtime(true) - $this->startTime) . " s\n\n");


    if ($options['merge'])
    {
      $this->startTime = microtime(true);
    
      $rows = $this->getRelationshipMergingData($options['merge_limit']);

      foreach ($rows as $row)
      {
        call_user_func_array(array($this, 'mergeRelationships'), $row);
      }

      print("\n\nMerged " . count($rows) . " relationships in " . (microtime(true) - $this->startTime) . " s\n\n");
    }
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
    $this->testMode = $options['test_mode'];
  }
  

  protected function getRelationshipConversionTriples($limit)
  {
    $sql =  'SELECT DISTINCT r1.id, r1.entity2_id old_value, r2.entity2_id new_value ' . 
            'FROM relationship r1 ' .
            'LEFT JOIN extension_record er1 ON (er1.entity_id = r1.entity2_id) ' .
            'LEFT JOIN relationship r2 ON (r2.entity1_id = r1.entity2_id) ' . 
            'LEFT JOIN extension_record er2 ON (er2.entity_id = r2.entity2_id) ' .
            'WHERE r1.category_id = 5 AND r1.description1 = \'Campaign Contribution\' AND r1.is_deleted = 0 ' .
            'AND er1.definition_id = 11 ' .
            'AND r2.category_id = 5 AND r2.description1 IN (\'Principal Campaign Committee\', \'Authorized Campaign Committee\', \'Other Campaign Committee\') AND r2.is_deleted = 0 ' . 
            'AND er2.definition_id = 3 ' .
            'LIMIT ' . $limit;
    $stmt = $this->db->execute($sql);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  
  protected function convertRelationship($id, $oldEntity2Id, $newEntity2Id)
  {
    $now = LsDate::getCurrentDateTime();


    //update relationship
    $sql = 'UPDATE relationship r SET entity2_id = ?, last_user_id = ?, updated_at = ? WHERE id = ?';

    if (!$this->testMode)
    {
      $stmt = $this->db->execute($sql, array($newEntity2Id, 1, $now, $id));

      if (!$stmt->rowCount())
      {
        throw new Exception("Couldn't update relationship " . $id);
      }
    }
    

    //create modification record
    $sql = 'INSERT INTO modification (object_model, object_id, object_name, created_at, updated_at) ' .
           'VALUES (?, ?, ?, ?, ?)';
    
    if (!$this->testMode)
    {
      $stmt = $this->db->execute($sql, array('Relationship', $id, 'Relationship ' . $id, $now, $now));
      
      if (!$stmt->rowCount())
      {
        throw new Exception("Couldn't insert modification record for relationship " . $id);
      }
    }
    

    //create modification_field record
    $modId = $this->db->lastInsertId('modification');
    $sql = 'INSERT INTO modification_field (modification_id, field_name, old_value, new_value) VALUES (?, ?, ?, ?)';

    if (!$this->testMode)
    {
      $stmt = $this->db->execute($sql, array($modId, 'entity2_id', $oldEntity2Id, $newEntity2Id));
      
      if (!$stmt->rowCount())
      {
        throw new Exception("Couldn't insert modification_field record for relationship " . $id);
      }
    }
    
    
    if ($this->debugMode)
    {
      print("Converted relationship " . $id . "\n");
    }
  }
  
  
  protected function getRelationshipMergingData($limit=null)
  {
    $sql = 'SELECT MIN(r.id) id, GROUP_CONCAT(DISTINCT r.id) merge_ids, ' . 
           'SUM(r.amount) amount, SUM(r.filings) filings, MIN(r.start_date) start_date, MAX(r.end_date) end_date ' .
           'FROM relationship r ' . 
           'WHERE r.category_id = 5 AND r.description1 = \'Campaign Contribution\' AND r.is_deleted = 0 ' .
           'GROUP BY r.entity1_id, r.entity2_id HAVING COUNT(r.id) > 1';           
    
    if ($limit)
    {
      $sql .= ' LIMIT ' . $limit;
    }
    
    $stmt = $this->db->execute($sql);
    
    return $stmt->fetchAll(PDO::FETCH_NUM);
  }
  
  
  protected function mergeRelationships($id, $mergeIds, $amount, $filings, $startDate, $endDate)
  {
    //remove surviving relationship id from list of relationship ids to merge
    $mergeIds = explode(',', $mergeIds);
    $mergeIds = array_diff($mergeIds, array($id));
    
    
    //update fec_filing records
    $sql = 'UPDATE fec_filing SET relationship_id = ? WHERE relationship_id IN (' . implode(',', $mergeIds) . ')';

    if (!$this->testMode)
    {
      $stmt = $this->db->execute($sql, array($id));
      
      if (!$stmt->rowCount())
      {
        //throw new Exception("No fec_filings updated for relationship " . $id . " (merging relationships: " . implode(', ', $mergeIds) . ")");
      }
    }
    
            
    //update surviving relationship
    $rel = Doctrine::getTable('Relationship')->find($id);
    $rel->amount = $amount;
    $rel->filings = $filings;
    $rel->start_date = $startDate;
    $rel->end_date = $endDate;

    if (!$this->testMode)
    {
      $rel->save();
    }


    //delete merged relationships
    foreach ($mergeIds as $mergeId)
    {
      $mergedRel = Doctrine::getTable('Relationship')->find($mergeId);

      if (!$this->testMode)
      {
        $mergedRel->delete();
      }
    }


    if ($this->debugMode)
    {
      print("Merging relationships (" . implode(', ', $mergeIds) . ") into " . $id . "\n");
    }
  }
}