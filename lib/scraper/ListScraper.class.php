<?php 

class ListScraper extends Scraper
{
  protected $urls = array();
  protected $re = null,
    $list_id = null,
    $org_id = null,
    $org = null,
    $list = null,
    $url = null,
    $url_name = null,
    $description1 = null,
    $is_board = null,
    $relationship_category = 'Position',
    $is_current = null,
    $org_org = false,
    $org_extensions = false,
    $last_first = false;
  
  public function setUrls($urls)
  {
    $urls = explode(',', $urls);
    foreach($urls as &$url)
    {
      $url = trim($url);
    }
    $this->urls = $urls;
  }
  
  public function setLastFirst($last_first)
  {
    $this->last_first = $last_first;
  }
  
  public function setOrgExtensions($org_extensions)
  {
    if ($org_extensions)
    {
      $this->org_extensions = preg_split('/\,\s*/isu',$org_extensions,-1,PREG_SPLIT_NO_EMPTY);
    }
    else
    {
      $this->org_extensions = array();
    }
  }
  
  public function setRegex($re)
  {
    $this->re = $re;
  }
  
  public function setListId($id)
  {
    $this->list_id = $id;
  }
  
  public function setOrgId($id)
  {
    $this->org_id = $id;
  }
  
  public function setDescription1($description1)
  {
    $this->description1 = $description1;
  }
  
  public function setIsCurrent($is_current)
  {
    $this->is_current = $is_current;
  }
  
  public function setBoard($is_board)
  {
    if ($is_board == 1 || $is_board == 0)
    {
      $this->is_board = $is_board;
    }
  }
  
  public function setRelationshipCategory($category)
  {
    $this->relationship_category = $category;
  }
  
  public function setOrgOrg($org_org)
  {
    $this->org_org = $org_org;
  }

  public function execute()
  {
    if ($this->list_id)
    {
      $this->list = Doctrine::getTable('LsList')->find($this->list_id);
      $accept = strtolower($this->readline(" $this->list ok? (y or n)"));
      if ($accept == 'n')
      {
        die;
      }
    }
    if ($this->org_id)
    {
      $this->org = Doctrine::getTable('Entity')->find($this->org_id);
      $accept = strtolower($this->readline(" $this->org ok? (y or n)"));
      if ($accept == 'n')
      {
        die;
      }
    }
    if (!in_array($this->relationship_category,array('Position','Membership','Donation')))
    {
      $this->printDebug('category not set properly');
      die;
    }
    if ((!$this->org && !$this->list) || !$this->re) 
    {
      $this->printDebug('either no regex set or no list or org set');
      die;
    }
    foreach($this->urls as $url)
    {
      $this->url = $url;

      if (preg_match('/http\:\/\/([^\/]+)\//isu',$url,$match))
      {
        $this->url_name = $match[1];
      }
      else $this->url_name = null;
      if (!$this->browser->get($this->url)->responseIsError())
      {
        $text = $this->browser->getResponseText();    
        preg_match_all($this->re, $text, $matches, PREG_SET_ORDER);
        foreach($matches as $match)
        {
          try
          {
            $this->db->beginTransaction();
            $this->parseResults($match);
            $this->db->commit();
          }
          catch (Exception $e)
          {
            $this->db->rollback();
            throw $e;
          }
        }
      }
      else 
      {
        $this->printDebug($url);
      }
    }
  }

  public function parseResults($match)
  {
    if (isset($match['bio']))
    {
      $bio_dirty = LsHtml::replaceEntities(LsString::spacesToSpace(LsHtml::stripTags($match['bio'],"; ")));
      $bio_dirty = preg_replace('/(\;\s)+/is','; ',$bio_dirty);
    }
    foreach($match as $k => &$m)
    {
      $m = LsHtml::replaceEntities(LsString::spacesToSpace(LsHtml::stripTags($m," ")));
    }
    
    if (isset($match['name']))
    {
      $name = $match['name'];
      $bio = '';
      if (isset($match['bio']))
      {
        $bio = $match['bio'];
      }
    }
    else return;
 
    $this->printDebug("_________________________\n\nname: " . $name. "\n");
    $this->printDebug("bio: " . $bio . "\n");
    $accept = strtolower($this->readline('Process this entity? (n to skip) '));
    if ($accept == 'n' || $accept == 'no') 
    {   
      return false;
    }
    
    if (!$this->org_org)
    {
      if ($this->last_first)
      {
        $entity = PersonTable::parseCommaName($name);
      }
      else
      {
      	$entity = PersonTable::parseFlatName($name);
      }
      $similar_entities = PersonTable::getSimilarQuery2($entity)->execute();
    }
    else
    {
      $entity = new Entity;
      $entity->addExtension('Org');
      foreach($this->org_extensions as $ext)
      {
        $entity->addExtension($ext);
      }      
      $entity->setEntityField('name', $name);  
      $name = trim($name);
      $name = str_replace('.','',$name);
      $similar_entities = OrgTable::getSimilarQuery($entity)->execute();  
    }
    
    $matched = false;
    foreach ($similar_entities as $similar_entity)
    {
      if ($similar_entity['primary_ext'] == 'Person')
      {
        $this->printDebug('  POSSIBLE MATCH: ' . $similar_entity->name . ' (Orgs :: ' . $similar_entity->getRelatedOrgsSummary() . "  Bio :: $similar_entity->summary)");
      }
      else
      {
        $this->printDebug('  POSSIBLE MATCH: ' . $similar_entity->name . ' (Summary :: ' . $similar_entity->summary . ')');
      }      
      $accept = $this->readline('  Is this the same entity? (y or n)');
      $attempts = 1;
      while ($accept != 'y' && $accept != 'n' && $attempts < 5)
      {
         $accept = $this->readline('  Is this the same entity? (y or n) ');
         $attempts++;
      }
      if ($accept == 'y')
      {
        $entity = $similar_entity;
        $matched = true;
        $this->printDebug('             [accepted]');
        //sleep(1);
        break;
      } 
      else if ($accept == 'break')
      {
        break;
      }     
    }
    $created = false;
    if (!$matched)
    {
      if ($entity->getPrimaryExtension() == 'Person')
      {
        $this->printDebug('  New person: ' . $entity->name_first . ' ' . $entity->name_last);
      }
      else
      {
        $this->printDebug('  New org: ' . $entity->name);
      }
      $accept = $this->readline('    create this new entity? (y or n) ');
      $attempts = 1;
      while ($accept != 'y' && $accept != 'n' && $attempts < 5)
      {
         $accept = $this->readline('    create this new entity? (y or n) ');
         $attempts++;
      }
      if ($accept == 'y')
      {
        if ($entity->getPrimaryExtension() == 'Person')
        {
          $this->printDebug("\n  Bio: $bio \n");
          $accept = $this->readline('    Add this bio? (y or n) ');
          $attempts = 1;
          while ($accept != 'y' && $accept != 'n' && $attempts < 5)
          {
             $accept = $this->readline('    add this bio? (y or n) ');
             $attempts++;
          }
          if ($accept == 'y')
          {
            $entity->summary = $bio;
          }
        }
        $entity->save();
        $entity->addReference($this->url,null,null,$this->url_name);
        $created = true;
        $this->printDebug(' ' . $entity->name . ' saved');
        //sleep(1);
      }
    }
    
    if (($matched || $created) && $entity->getPrimaryExtension() == 'Person')
    {
      $accept = $this->readline("Parse above bio for possible relationships? (y or n) ");
      $attempts = 1;

      while ($accept != 'y' && $accept != 'n' && $attempts < 5)
      {
        $accept = $this->readline("Parse above bio for possible relationships? (y or n) ");
        $attempts++;
      }
      
      if ($accept == 'y')
      {
        $names = $entity->parseBio($bio_dirty);
        $this->printDebug(" Orgs that $entity has a position at?"); 
        foreach($names as $name)
        {
          $exists = false;
          $name = trim($name);
          $accept = $this->readline(" > $name ::  an org? (y or n or b to break) ");
          $attempts = 1;
          $accept = strtolower($accept);
          while ($accept != 'y' && $accept != 'n' && $accept != 'b' && $attempts < 5)
          {
            $accept = $this->readline("  $name ::  an org? (y or n or b to break) ");
            $accept = strtolower($accept);
            $attempts++;
          }
          if ($accept == 'b')
          {
            break;
          }
          else if ($accept == 'y')
          {
            $this->printDebug(' .....looking for names.....');
            $orgs = EntityTable::getByExtensionAndNameQuery('Org',$name)->limit(10)->execute();
            $related_org = null;
            foreach($orgs as $org)
            {
              $q = LsDoctrineQuery::create()
                    ->from('Relationship r')
                    ->where('entity1_id = ? and entity2_id = ?', array($entity->id, $org->id))
                    ->fetchOne();
              if ($q) 
              {
                $this->printDebug('  Position already exists, skipping...');
                $exists = true;
                break;
              }
              
              $accept = $this->readline("    Create a position relationship between $entity->name and $org->name? (y or n) ");
              $attempts = 1;
              while ($accept != 'y' && $accept != 'n' && $attempts < 5)
              {
                 $accept = $this->readline("    Create a position relationship between $entity->name and $org->name? (y or n) ");
                 $attempts++;
              }
              if ($accept == 'y')
              {
                $related_org = $org;
                break;
              }
            }
            if (!$related_org && !$exists)
            {
              $accept = $this->readline(" couldn't find org, should this one be created: $name (y or n) ");
              while ($accept != 'y' && $accept != 'n' && $attempts < 5)
              {
                 $accept = $this->readline(" couldn't find org, should this one be created: $name (y or n) ");
                 $attempts++;
              }
              if ($accept == 'y')
              {
                $related_org = new Entity;
                $related_org->addExtension('Org');
                $related_org->name = preg_replace('/\.(?!com)/i', '', $name);
                $extensions = $this->readline("  what extensions should this org get? (eg 'Business, LobbyingFirm, LawFirm') ");
                $extensions = preg_split('/\,\s*/isu',$extensions,-1,PREG_SPLIT_NO_EMPTY);
                try
                {
                  foreach($extensions as $extension)
                  {
                    $related_org->addExtension($extension);
                  }
                  $related_org->save();   
                  $related_org->addReference($this->url,null,null,$this->url_name);
                }
                catch (Exception $e)
                {
                  $this->printDebug('   !!! problems with org creation, skipping');
                  $related_org = null;
                }
              }
            }
            if ($related_org)
            {
            
              $q = LsDoctrineQuery::create()
                ->from('Relationship r')
                ->where('r.entity1_id = ? and r.entity2_id = ? and r.category_id = ?', array($entity->id, $related_org->id, 1))
                ->fetchOne();
                
              if ($q)
              {
                $this->printDebug('   (relationship already found, skipping...)');
                continue;
              }
            
              $relationship = new Relationship;
              $relationship->Entity1 = $entity;
              $relationship->Entity2 = $related_org;
              $relationship->setCategory('Position');
              $title = $this->readline("     Title for this position relationship? (<enter> to skip) ");
              if (strlen($title) > 2)
              {
                $relationship->description1 = $title;
              }
              $current = strtolower($this->readline("      Is the relationship current? (y or n or <enter> to skip) "));
              if (in_array($current, array('y','yes')))
              {
                $relationship->is_current = 1;
              }
              else if (in_array($current, array('n','no')))
              {
                $relationship->is_current = 0;               
              }
              $board = strtolower($this->readline("      Is the relationship a board position? (y or n or <enter> to skip) "));
              if (in_array($board, array('y','yes')))
              {
                $relationship->is_board = 1;
              }
              else if (in_array($board, array('n','no')))
              {
                $relationship->is_board = 0;               
              }
              $relationship->save();
              $relationship->addReference($this->url,null,null,$this->url_name);
              $this->printDebug("     Relationship saved: $relationship");
            }            
          }
        }
      } 
    }
    
    if ($matched || $created)
    {
      if ($this->list)
      {

        $q = LsDoctrineQuery::create()
            ->from('LsListEntity l')
            ->where('l.entity_id = ? and l.list_id = ?', array($entity->id, $this->list->id))
            ->fetchOne();
          
        if (!$q)
        {
          $le = new LsListEntity;
          $le->Entity = $entity;
          $le->LsList = $this->list;
          if (isset($match['rank']))
          {
            if (preg_match('/(\d+)/isu',$match['rank'],$m))
            {
              $le->rank = $m[1];
            }
          }
          $le->save();
          $this->printDebug('List membership saved');
        }
      }
      
      if ($this->org)
      {

        $q = LsDoctrineQuery::create()
            ->from('Relationship r')
            ->where('r.entity1_id = ? and r.entity2_id = ? and r.category_id = ?', array($entity->id, $this->org->id, 1))
            ->fetchOne();
                
        if ($q)
        {
          $this->printDebug('   (relationship already found, skipping...)');
          return;
        }
        
        $relationship = new Relationship;
        $relationship->Entity1 = $entity;
        $relationship->Entity2 = $this->org;
        $relationship->setCategory($this->relationship_category);
        if ($this->description1)
        {
          $relationship->description1 = $this->description1;
        }
        else
        {
          $description = $this->readline("       what description to give this relationship ($relationship) ? (less than 3 chars will skip)");
          if (strlen($description) > 2)
          {
            $relationship->description1 = $description;
          }
        }
        if ($this->relationship_category == 'Position')
        {
          $relationship->is_board = $this->is_board;
        }
        else if ($this->relationship_category == 'Donation')
        {
          if ($this->amount)
          {
            $relationship->amount = $this->amount;
          }
          else
          {
            $amount = $this->readline("  what amount ($relationship) ? (less than 3 chars will skip)");
            if (strlen($amount) > 1)
            {
              $relationship->amount = $amount;
            }
          }
        }
        $relationship->save();          
        $relationship->addReference($this->url,null,null,$this->url_name);    
        $this->printDebug(" Relationship saved: $relationship");
      } 
    }
    //dump history
    
    if (isset($match['affiliation1']))
    {
      $affiliation = $match['affiliation'];
      //$this->printDebug($affiliation);      
    }    
  }

  
  public function readline($prompt="")
  {
     print $prompt;
     $out = "";
     $key = "";
     $key = fgetc(STDIN);        //read from standard input (keyboard)
     while ($key!="\n")        //if the newline character has not yet arrived read another
     {
         $out.= $key;
         $key = fread(STDIN, 1);
     }
     return $out;
  }

}