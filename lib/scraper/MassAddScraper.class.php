<?php

class MassAddScraper extends Scraper
{
  protected $source_url = null,
    $source_name = null,
    $list_id = null,
    $org_id = null,
    $category_id = null,
    $relationship_description = null,
    $filename = null,
    $edits = array(),
    $errors = array(),
    $start = 0,
    $limit = 50;
  
  public function setOptions($filename, $source_url, $source_name = null, $list_id = null, $org_id = null, $category_id = null, $relationship_description = null)
  {
    $this->source_url = $source_url;
    $this->filename = $filename;
    $this->list_id = $list_id;
    $this->org_id = $org_id;
    $this->category_id = $category_id;
    $this->relationship_description = $relationship_description;
    $this->source_name = $source_name;
  }
  

  protected function execute()
  {
    
    $this->filename = $this->filename;
    
    $str = trim(file_get_contents($this->filename));
    $str = str_replace('"','',$str);
    $arr = explode("\n",$str);
    $headers = explode("\t",array_shift($arr));
    //cleanup array  
    $rows = array();
    foreach($arr as $a)
    {
      if (trim($a) == '') continue;
      $row = explode("\t",$a);
      if (count($row) != count($headers)) 
      {
        $row = array_pad($row,count($headers),' ');
        $this->setError('unmatched columns / headers : ' . count($row) . ' : ' . count($headers));
      }
      $row = array_change_key_case(array_combine($headers,$row));
      $rows[] = $row;
    }

    $lim = count($rows) > $this->limit ? $this->limit : count($rows);
    
    for($i = $this->start; $i < $lim; $i ++)
    {
      $row = $rows[$i];
      if (!isset($row['name']) || !isset($row['affiliation1']))
      {
        $this->setError('Spreadsheet not properly configured, exiting');
        return null;
      }
      else if (trim($row['name']) == '' || trim($row['affiliation1']) == '')
      {
        $this->setError('Name or affiliation not set for some rows');
        continue;
      }

      $this->printDebug('searching ' . $row['name'] . ' (' . $row['affiliation1'] . ')');
      $this->processRow($row);
    }  
    
    if ($this->list_id)
    {
      $list = Doctrine::getTable('LsList')->find($this->list_id);
      $this->addToList($list);
    }    
    
    if ($this->org_id)
    {
      $org = Doctrine::getTable('Entity')->find($this->org_id);
      if ($org)
      {
        $this->addRelationship($org,$this->category_id,$this->relationship_description);
      }
    }
  }
    
  protected function processRow($row)
  {
    foreach($row as &$r)
    {
      $r = trim($r);
    }
    
    $edit = array('Search Name' => $row['name'], 'Affiliation Name' => $row['affiliation1'], 'Similar Names' => array(), 'New Person' => null, 'Existing Person' => null, 'New Org' => null, 'Existing Org' => null, 'New Relationship' => null);

    try 
    {
      $this->db->beginTransaction();
      $person = null;
      $search_person = PersonTable::parseFlatName($row['name']);
      $similar = $search_person->getSimilarEntitiesQuery(true)->execute();
      $matched_bio = false;
      $similar_ids = array();
      foreach($similar as $s)
      {
        $similar_ids[] = $s->id;
        $sim_re = LsString::escapeStringForRegex($s->name_first);
        $search_re = LsString::escapeStringForRegex($search_person->name_first);

        if (preg_match('/^' . $sim_re . '/su',$search_person->name_first) == 0 && preg_match('/^' . $search_re . '/su', $s->name_first) == 0)
        {
          continue;
        }
        $matched = false;
        $affils = array();
        $ct = 1;
        $matched_affils = array();
        $unmatched_affils = array();
        while (isset($row['affiliation' . $ct]) && trim($row['affiliation' . $ct]) != '')
        {
          $affil = trim($row['affiliation' . $ct]);
          $org = $s->checkAffiliations(array($affil));
          if ($org)
          {
            $matched_affils[] = array($org,$affil);
            $edit['Existing Org'] = $org->id;
            break;
          }
          else
          {
            $unmatched_affils[] = $affil;
          }
          $ct++;
        }
        if (count($matched_affils))
        {
          $person = $s;
          break;
          //$ret[] = array('person' => $s, $matched_affils, $unmatched_affils);
        }   
        else
        {
          /*$str = implode(' ', $unmatched_affils);
          if (isset($row['bio']))
          {
            $str .= ' ' . $row['bio'];
          }*/
          $bio = $s->getExtendedBio();
          foreach ($unmatched_affils as $affil)
          {
            $affil = OrgTable::removeSuffixes($affil);
            $this->printDebug($affil);
            $this->printDebug($bio);
            if (preg_match('/' . OrgTable::getNameRegex($affil) . '/su',$bio))
            {
              $matched_bio = true;
              break;
            }
          }  
          if ($matched_bio)
          {
            $person = $s;
            break;
          }
          else
          {
            $this->printDebug('  ' . $s->name . ' failed');
          }
        }
      }
      $edit['Similar Names'] = array_slice($similar_ids,0,5);
      $no_match = false;
      if (!$person)
      {
        if (isset($row['bio']) && trim($row['bio']) != '') 
        {
          $search_person->summary = $row['bio'];
        }
        $search_person->save();
        $this->printDebug('  not found, new person saved: ' . $search_person->name);        
        $search_person->addReference($this->source_url, null, null, $this->source_name);
        $no_match = true;
        $edit['New Person'] = $search_person->id;
        $person = $search_person;
      }
      else
      {
        if (isset($row['bio']) && trim($row['bio']) != '' && !$person->summary) 
        {
          $person->summary = $row['bio'];
          $person->save();
        }
        $this->printDebug('  **person found: ' . $person->name);
        $edit['Existing Person'] = $person->id;
      }
        
      if ($matched_bio || $no_match)
      {
  
        $orgs = OrgTable::getOrgsWithSimilarNames($row['affiliation1'], true); 
        $max = -1;
        $affiliated_org = null;
        
        foreach ($orgs as $org)
        {
          $this->printDebug('    found match: ' . $org->name);
          $ct = $org->getRelatedEntitiesQuery('Person',RelationshipTable::POSITION_CATEGORY,null,null,null,false,2)->count();
          if ($ct > $max)
          {
            $affiliated_org = $org;
            $edit['Existing Org'] = $affiliated_org->id;
            $max = $ct;
          }
        } 
        
        if (!$affiliated_org)
        {
          $affiliated_org = new Entity;
          $affiliated_org->addExtension('Org');
          if (isset($row['affiliation1_extensions']) && $row['affiliation1_extensions'] != '')
          {
            $extensions = explode(',',$row['affiliation1_extensions']);
            foreach($extensions as $ext)
            {
              $ext = trim($ext);
              if (in_array($ext,ExtensionDefinitionTable::$extensionNames))
              {
                $affiliated_org->addExtension($ext);
              }
            }
          }
          else
          {
            //$affiliated_org->addExtension('Business');
          }
          $affiliated_org->name = $row['affiliation1'];
          $affiliated_org->save();
          $affiliated_org->addReference($this->source_url, null, null, $this->source_name);
          $edit['New Org'] = $affiliated_org->id;
        }
      
        $rel = new Relationship;
        $rel->Entity1 = $person;
        $rel->Entity2 = $affiliated_org;
        $rel->setCategory('Position');
        if (isset($row['affiliation1_title']) && $row['affiliation1_title'] != '')
        {
          $description = trim($row['affiliation1_title']);
          $rel->description1 = $description; 
          if ($description == 'Director' || $description == 'Trustee' || preg_match('/^Chair/su',$description))
          {
            $rel->is_board = 1;
            $rel->is_employee = 0;
          } 
        }
        $rel->save();
        $rel->addReference($this->source_url, null, null, $this->source_name);
                
        $edit['New Relationship'] = $rel->id;
      }
      
      if (isset($row['start_date']) && trim($row['start_date']) != '')
      {
        $edit['Relationship']['start_date'] = trim($row['start_date']);
      }
      
      if (isset($row['end_date']) && trim($row['end_date']) != '')
      {
        $edit['Relationship']['end_date'] = trim($row['end_date']);
      }
      
      if (isset($row['title']) && trim($row['title']) != '')
      {
        $edit['Relationship']['title'] = trim($row['title']);
      }

      if (isset($row['notes']) && trim($row['notes']) != '')
      {
        $edit['Relationship']['notes'] = trim($row['notes']);
      }    
      
      if (isset($row['rank']) && $row['rank'] != '')
      {
        $edit['rank'] = $row['rank'];
      }  
      
      $this->db->commit();
    }
    
    catch (Exception $e)
    {
      $this->db->rollback();
      throw $e;
    }
    
    $this->edits[] = $edit;

  }
  
  protected function addToList($list)
  {
    try
    {
      $this->db->beginTransaction();
      foreach ($this->edits as &$edit)
      {
        $entity_id = $edit['New Person'] ? $edit['New Person'] : $edit['Existing Person'];
        $entity = Doctrine::getTable('Entity')->find($entity_id);
        $q = LsDoctrineQuery::create()
            ->from('LsListEntity l')
            ->where('l.entity_id = ? and l.list_id = ?', array($entity->id, $list->id))
            ->fetchOne();
        if ($q)
        {
          $this->printDebug('List Entity already saved');
        }
        else
        {
          $le = new LsListEntity;
          $le->LsList = $list;
          $le->Entity = $entity;
          if (isset($edit['rank']))
          {
            $le->rank = $edit['rank'];
            unset($edit['rank']);
          }
          $le->save();
          //$edit['lists'] = $le;
        }      
      }
      $this->db->commit();
    }
    catch (Exception $e)
    {
      $this->db->rollback();
      throw $e;
    }
  }
  
  protected function addRelationship($org, $category_id, $description)
  {
    try
    {
      $category = Doctrine::getTable('RelationshipCategory')->find($category_id);
      if (!$category )
      {
        $this->setError('No relationships added -- you must choose a category');
        return false;
      }
      $this->db->beginTransaction();
      
      foreach ($this->edits as &$edit)
      {
        $person_id = $edit['New Person'] ? $edit['New Person'] : $edit['Existing Person'];
        $person = Doctrine::getTable('Entity')->find($person_id);
 
        $q = LsDoctrineQuery::create()  
            ->from('Relationship r')
            ->where('r.entity1_id = ? and r.entity2_id = ?', array($person->id,$org->id))
            ->fetchOne();
            
        if ($q) 
        {                
          unset($edit['Relationship']); 
          continue;
        }
            
        $rel = new Relationship;
        $rel->Entity1 = $person;
        $rel->Entity2 = $org;
        $rel->setCategory($category);
        $rel->is_current = 1;
                
        if (isset($edit['Relationship']['start_date']) && preg_match('/\d\d\d\d/',$edit['Relationship']['start_date']))
        {
          $rel->start_date = $edit['Relationship']['start_date'];
        }
        if (isset($edit['Relationship']['end_date']) && preg_match('/\d\d\d\d/',$edit['Relationship']['end_date']))
        {
          $rel->end_date = $edit['Relationship']['end_date'];
        }

        if ($category->name == 'Position' && isset($edit['Relationship']['title']))
        {
          $rel->description1 = $edit['Relationship']['title'];
        }
        else if ($description)
        {
          $description == trim($description);
          $rel->description1 = $description;
          if ($description == 'Director' || $description == 'Trustee')
          {
            $rel->is_board = 1;
            $rel->is_employee = 0;
          }  
        } 

        if (isset($edit['Relationship']['notes']))
        {
          $rel->notes = $edit['Relationship']['notes'];
        }
        $rel->save();
        $rel->addReference($this->source_url, null, null, $this->source_name);
        $this->printDebug($rel . ' saved');
        unset($edit['Relationship']); 
      }

      $this->db->commit();

    }
    catch (Exception $e)
    {
      $this->db->rollback();
      throw $e;
    }
  }
  
  protected function setError($error)
  {
    if (in_array($error, $this->errors))
    {
      $this->errors[] = $error;
    }
  }
  
  public function getErrors()
  {
    if (count($this->errors))
    {
      return $this->errors;
    }
    else return null;
  }
  
  public function getEdits()
  {
    if (count($this->edits))
    {
      return $this->edits;
    }
    else return null;
  }
  
  public function setStart($start)
  {
    $this->start = $start;
  }
  
}