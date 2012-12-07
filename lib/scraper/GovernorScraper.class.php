<?php

class GovernorScraper extends Scraper
{
  
  protected $_searchUrl = 'http://www.nga.org/portal/site/nga/menuitem.8fd3d12ab65b304f8a278110501010a0?submit=Submit&inOffice=all&Party=Any&Lastname=&Firstname=&startOfficeMonth=&startOfficeYear=1975&endOfficeMonth=&endOfficeYear=2009&numTerms=&Biography=&govsex=both&Religion=&Race=&School=&Higheroffice=&Milservice=&warserved=&Milawards=&Birthplace=Any&keywords=';
  protected $_baseUrl = 'http://www.nga.org';
  protected $_rows = array();

  public function execute()
  {
    if (!$this->browser->get($this->_searchUrl)->responseIsError())
    {
      $text = $this->browser->getResponseText();
      $this->importRows($text);
    }
    
    if ($this->hasMeta('1975_round','last_processed'))
    {
      $start = $this->getMeta('1975_round','last_processed');
    }
    else
    {
      $start = 0;
    }
    for($i = $start; $i < count($this->_rows); $i++)
    {
      $row = $this->_rows[$i];
      try
      {
        $this->db->beginTransaction();
        $this->importGovernor($row);
        $this->saveMeta('1975_round','last_processed',$i);
        $this->db->commit();
      }
      catch (Exception $e)
      {
        $this->db->rollback();
        throw $e;
      }
    }
  }
  
  protected function importRows($text)
  {
    $rows = array();
    //if (preg_match_all('/<p>\s*<strong>([^<]*)<\/strong>\s*<br>\s*<a\s+href\="([^"]+)">([^<]*)</isu',$text,$matches, PREG_SET_ORDER))
    if (preg_match_all('/<tr\s+height\="25" bgcolor="#ffffff">\s*<td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td>\s*<\/tr>/su',$text,$matches,PREG_SET_ORDER))
    {
      foreach($matches as $match)
      {
        array_shift($match);
        $row = array();
        foreach($match as &$m)
        {
          $m = trim(str_replace('&nbsp;',' ',$m));
          //$this->printDebug($m);
        }
        $links = LsHtml::matchLinks($match[0]);
        $row['name'] = $links[0]['text'];
        $row['url'] = $links[0]['url'];
        $row['state'] = $match[1];
        if (preg_match_all('/\d\d\d\d/',$match[2],$years))
        {
          $row['years'] = $years[0];
        }
        $row['party'] = $match[3];        
        $rows[] = $row;
      }
    }
    $this->_rows = $rows;
  }

  protected function importGovernor($row)
  {
    $url = $this->_baseUrl . $row['url'];
    if (!$this->browser->get($url)->responseIsError())
    {
      $text = $this->browser->getResponseText();
      $text = LsHtml::replaceEntities($text);

      //preg_match('/>Family\:<\/b>([^<]*)<br/is',$text,$family_arr);
      
      $name =  trim(str_ireplace('Gov.','',$row['name']));
      $this->printDebug('');
      
      $this->printDebug($name . ':');
      $governor = PersonTable::parseFlatName($name);
      $governor->addExtension('PoliticalCandidate');
      $governor->addExtension('ElectedRepresentative');
      $governor->is_state = 1;
      $similar = $governor->getSimilarEntitiesQuery(true)->execute();
      
      foreach($similar as $s)
      {
        $sim_re = LsString::escapeStringForRegex($s->name_first);
        $search_re = LsString::escapeStringForRegex($governor->name_first);
        if (preg_match('/^' . $sim_re . '/su',$governor->name_first) == 0 && preg_match('/^' . $search_re . '/su', $s->name_first) == 0)
        {

          continue;
        }
        $bio = $s->getExtendedBio();
        if (preg_match('/\bgovernor(ship)?\b/isu',$bio))
        {
          $governor = $s;
          $this->printDebug(' Found existing governor: ' . $s->name . ' ' . $s->id);
          break;
        }
      }
      $governor->save();
      $this->printDebug($governor->id);
      if (!$governor->start_date && preg_match('/>Born\:<\/b>([^<]*)<br/is',$text,$birth_arr))
      {
        $this->printDebug(' Birthdate: ' . $birth_arr[1]);
        $governor->start_date = trim($birth_arr[1]);  
      }

      if (!$governor->birthplace && preg_match('/>Birth State\:<\/b>([^<]*)<br/is',$text,$birth_state_arr))
      {
        $this->printDebug(' Birthplace: ' . trim($birth_state_arr[1]));
        $governor->birthplace = trim($birth_state_arr[1]);
      }      
      
      //PARTY MEMBERSHIP
      if (preg_match('/>Party\:<\/b>([^<]*)<br/is',$text,$party_arr))
      {
        $party_str = $party_arr[1];
        $this->printDebug(' Party: ' . $party_str);
        if (stristr($party_str,'Democrat'))
        {
          $party = EntityTable::getByExtensionQuery('PoliticalParty')->addWhere('name = ?', 'Democratic Party')->fetchOne();
        }
        if (stristr($party_str,'Republican'))
        {
          $party = EntityTable::getByExtensionQuery('PoliticalParty')->addWhere('name = ?', 'Republican Party')->fetchOne();
        }
        if (isset($party) && $party && !$governor->party_id)
        {
          $governor->Party = $party;
          $governor->is_independent = false;
          $this->printDebug(' Added membership in ' . $party);
        }
        else if (stristr($party_str,'Independent'))
        {
          $governor->is_independent = true;
        }
      }      
      
      if (!$governor->summary && preg_match_all('/>([^<]{240,})/isu',$text,$bio_match))
      {
        $str = '';
        foreach ($bio_match[1] as $b)
        {
          if (!stristr($b,'Javascript')) $str .= "\n\n" . $b;
        }
        $str = trim($str);
        if (strlen($str)) $governor->summary = $str;
      }
      $governor->save();
      $governor->addReference($url,null,$governor->getAllModifiedFields(),'Governors Association');
      
      
      //SCHOOLS      
      if (preg_match('/>School\(s\)\:<\/b>([^<]*)<br/is',$text,$school_arr))
      {
        $school_names = explode(';',trim($school_arr[1]));
        if (count($school_names) == 1)
        {
          $school_names = explode(',',$school_names[0]);
        }
        foreach($school_names as $school_name)
        {
          $school_name = trim($school_name);
          if (!$school = EntityTable::getByExtensionQuery('School')->leftJoin('e.Alias a')->addWhere('e.name = ? or a.name = ?', array($school_name, $school_name))->fetchOne())
          {
            $school = new Entity;
            $school->addExtension('Org');
            $school->addExtension('School');
            $school->name = $school_name;
            $school->save();
            $this->printDebug(' Added School: ' . $school_name);
          }      
          
          $q = RelationshipTable::getByCategoryQuery('Education')->addWhere('entity1_id = ? and entity2_id = ?',array($governor->id,$school->id))->fetchOne();
          if (!$q)
          {
            $relationship = new Relationship;
            $relationship->setCategory('Education');
            $relationship->Entity1 = $governor;
            $relationship->Entity2 = $school;
            $relationship->is_current = 0;
            $relationship->save();
            $relationship->addReference($url,null,$relationship->getAllModifiedFields(),'Governors Association');
            $this->printDebug(' Added education: ' . $relationship->name);
          }
        }     
      }      
      //GOVERNOR OFFICE AND POSITION
      $office_name = 'Office of the Governor of ' . $row['state'];
      if (!$office = EntityTable::getByExtensionQuery('GovernmentBody')->addWhere('name = ?', $office_name)->fetchOne())
      {
        $office = new Entity;
        $office->name = $office_name;  
        $office->addExtension('Org');
        $office->addExtension('GovernmentBody');
        $state = Doctrine::getTable('AddressState')->findOneByName($row['state']);
        if ($state)
        {
          $office->state_id = $state->id;
        }
        $office->save();
        $office->addReference($url,null,$office->getAllModifiedFields(),'Governors Association');
        $this->printDebug(' Added office: ' . $office->name);
      }
      
      $q = RelationshipTable::getByCategoryQuery('Position')->addWhere('entity1_id = ? and entity2_id = ? and description1 = ?',array($governor->id,$office->id, 'Governor'))->fetchOne();
      
      if (!$q)
      {
        sort($row['years']);
        $i=0;
        while($i < count($row['years']))
        {
          
          $governorship = new Relationship;
          $governorship->setCategory('Position');
          $governorship->Entity1 = $governor;
          $governorship->Entity2 = $office;
          $governorship->description1 = 'Governor';
          $governorship->start_date = $row['years'][$i];
          $i++;
          if (isset($row['years'][$i]))
          {
            $governorship->end_date = $row['years'][$i];
            $governorship->is_current = 0;
            if (!$governor->blurb && !isset($row['years'][$i+1]))
            {
              $governor->blurb = 'Former Governor of ' . $row['state'];
            }
          }
          else
          {
            $governorship->is_current = 1;
            if (!$governor->blurb)
            {
              $governor->blurb = 'Governor of ' . $row['state'];
            }
          }
          $governor->save();
          $i++;
          $governorship->save();
          $governorship->addReference($url,null,$governorship->getAllModifiedFields(),'Governors Association');
          $this->printDebug(' Added governorship: ' . $governorship->name);
        }
      }      
        
      //SPOUSE
      if (preg_match('/>Spouse\:<\/b>(.*?)<br/is',$text,$spouse_arr))
      {
        $spouse = trim(LsHtml::stripTags($spouse_arr[1]));
        $q = RelationshipTable::getByCategoryQuery('Family')->addWhere('entity1_id = ? or entity2_id = ?', array($governor->id, $governor->id))->fetchOne();
        if (!$q && strlen($spouse))
        {
          $spouse = PersonTable::parseFlatName($spouse);
          $spouse->save();
          $this->printDebug(' Added spouse: ' . $spouse->name); 
          $relationship = new Relationship;
          $relationship->setCategory('Family');
          $relationship->Entity1 = $spouse;
          $relationship->Entity2 = $governor;
          $relationship->description1 = 'Spouse';
          $relationship->description2 = 'Spouse';
          $relationship->save();
          $relationship->addReference($url,null,$relationship->getAllModifiedFields(),'Governors Association');
          $this->printDebug(' Added spouse relationship: ' . $relationship->name); 
        }
      }
      
      //ADDRESS --not working, malformed addresses
      /*
      if (preg_match('/>Address\:\s*<\/b>(.*?)<b>/is',$text,$address_arr))      
      {
        $address = trim(str_replace('<br/>',', ',$address_arr[1]));
        $this->printDebug($address);
        if ($governor->Address->count() == 0 && $a = $governor->addAddress($address))
        {
          $this->printDebug(' Address: ' . $a);
          $governor->save();
        }
      }*/
      
      //PHONE NUMBER
      if (preg_match('/>Phone\(s\)\:<\/b>([^<]*)<br/is',$text,$phone_arr))
      {
        $phone_number = trim($phone_arr[1]);
        if (!$governor->Phone->count())
        {
          $phone = $governor->addPhone($phone_number);
          $this->printDebug(' Phone: ' . $phone);
        }      
      }
      
      if (!$governor->Image->count() && preg_match('/<img .*?class\="display" src\="([^"]*)"/is',$text,$img_arr))
      {
        $url = $img_arr[1];
        try 
        {
          $fileName = ImageTable::createFiles($url, $governor->name_first);
        }
        catch (Exception $e)
        {
          $fileName = null;
        }
        if ($fileName)
        {
          //insert image record
          $image = new Image;
          $image->filename = $fileName;
          $image->entity_id = $governor->id;
          $image->title = $governor->name;
          $image->caption = 'From Governors Association website';
          $image->is_featured = true;
          $image->is_free = false;
          $image->url = $url;
                        
          $image->save();
          $this->printDebug("Imported image: " . $image->filename);
        }
      }
 
      
       
    }
  }  
}


