<?php

class EmployeeInfoScraper extends Scraper
{
  
  protected $_orgs = null;
  protected $_orgExtensions = array('LobbyingFirm');
  protected $_metaName = 'lobbyist_bios';//'lobbying_websites';
  protected $_metaPredicate = 'last_processed';
  
  public function setMetaName($metaName)
  {
    $this->_metaName = $metaName;
  }
  
  public function setMetaPredicate($metaPred)
  {
    $this->_metaPredicate = $metaPred;
  }
  
  public function setOrgExtensions($extensions)
  {
    $this->_orgExtensions = (array) $extensions;
  }

  public function setOrgs($orgs = null)
  {
    if ($orgs)
    {
      $this->_orgs = $orgs;
    }
    else
    {
      array_unshift($this->_orgExtensions,'Org');
      $q = EntityTable::getByExtensionQuery($this->_orgExtensions)->addWhere('website is not NULL')->limit(100);
      if ($this->hasMeta($this->_metaName,$this->_metaPredicate))
      {
        $start_id = $this->getMeta($this->_metaName,$this->_metaPredicate); 
        $q->addWhere('e.id > ?', $start_id);
      }
      $this->_orgs = $q->execute();      
    }
      
  }

  public function execute()
  {
    $this->browser = new sfWebBrowser($this->defaultHeaders, 'sfCurlAdapter', array('followlocation' => '1'));
    if (!$this->safeToRun('employeeinfo'))
    {
      $this->printDebug('script already running');
      die;
    }
    if (!$this->_orgs)
    {
      $this->setOrgs();
    }
    if (!count($this->_orgs))
    {
      echo 'no more orgs';
      die;  
    }
    foreach ($this->_orgs as $org)
    {
      try
      {
        $this->db->beginTransaction();
        $this->printDebug('__________________________________ ' . $org->name);
        $this->getLobbyistInfo($org);

        $this->saveMeta($this->_metaName, $this->_metaPredicate, $org->id);
        $this->saveMeta($org->id, 'scraped','1');  

        if (!$this->testMode)
        {
          $this->db->commit();
        }
        else
        {
          $this->db->rollback();
        }
      }
      catch (Exception $e)
      {
        $this->db->rollback();

        try
        {
          $this->db->beginTransaction();
          $this->saveMeta($org->id,'error',1);
          $this->saveMeta($this->_metaName, $this->_metaPredicate, $org->id);

          if (!$this->testMode)
          {
            $this->db->commit();
          }
          else
          {
            $this->db->rollback();
          }
          
          return null;
        }
        catch (Exception $e)
        {
          $this->db->rollback();
          throw $e;
        }
      }
    }  
  }
  
  private function getLobbyistInfo($org)
  {
    $people = $org->getRelatedEntitiesQuery('Person',RelationshipTable::POSITION_CATEGORY,null,null,null,false)->addWhere('summary is NULL or summary = ?', '')->execute();  
    $google_scraper = new LsGoogle();
    $ct = 0;
    foreach ($people as $person)
    {
      if ($ct > 30)
      {
        return null;
      }
      $this->printDebug("\n******************\n");
      $bio = null;
      $image = null;
      $query = 'site:' . $org->website . ' ' . $person->name;
      $this->printDebug('Query: ' . $query);
      $google_scraper->setQuery(trim($query));
      $google_scraper->execute();
      if ($google_scraper->getNumResults())
      {
        $results = $google_scraper->getResults();
        $match_sets = array();
        $this->stopTimer();
        $last = $this->timer->getElapsedTime();
        $this->beginTimer();
        foreach ($results as $result)
        {
          $this->stopTimer();
          $now = $this->timer->getElapsedTime();
          $diff = $now - $last;
          $this->printDebug($diff);
          $last = $now;
          $this->beginTimer();
          if ($diff > 30)
          {
            try
            {
              $this->db->beginTransaction();
              $this->saveMeta($org->id,'timeout',1);
              $this->printDebug('TIMEOUT=======================================');

              if (!$this->testMode)
              {
                $this->db->commit();
              }
              else
              {
                $this->db->rollback();
              }
            }
            catch (Exception $e)
            {
              $this->db->rollback();
            }
            return null;
          }
          if (0) //empty($result->cacheUrl) == false)
          {
            $url = $result->cacheUrl;
          }
          else
          {
            $url = $result->unescapedUrl;
          }
          $this->printDebug($url);
          if (preg_match('/\.pdf$/is',$url))
          {
            $this->printDebug("PDF, skipping ($url)\n----------------");
            continue;
          }
          try
          {
            $error = $this->browser->get($url)->responseIsError();
          }
          catch (Exception $e)
          {
            continue;
          }
          if (!$error)
          {
            $this->printDebug('checking: ' . $url);
            $page = $this->browser->getResponseText();
            $page = LsHtml::replaceEntities($page);
            if (!$bio)
            {
              if ($bio = $this->findPersonBio($page,$person, $org))
              {
                try
                {
                  $this->db->beginTransaction();
                  $person->summary = $bio;
                  $person->save();
                  $person->addReference($url,null,array('summary'), $org->name . ' website');
                  $this->printDebug("\nBIO FOUND & SAVED: " . $bio . "\n");
                  $ct = 0;

                  if (!$this->testMode)
                  {
                    $this->db->commit();
                  }
                  else
                  {
                    $this->db->rollback();
                  }
                }
                catch (Exception $e)
                {
                  $this->db->rollback();
                  throw $e;
                }
              }
              else
              {
                $this->printDebug('no bio');
              }
            }
            if (!$image)
            {            
              if ($image = $this->findPersonImage($page,$person,$org))
              {
                $this->printDebug('IMAGE FOUND: ' . $image['url']);
                preg_match('/(http\:\/\/[^\/]+)\//is',$url,$match);
                $root_url = $match[1];
                $image_url = null;
                if (preg_match('/^http/is',$image['url']))
                {
                  $image_url = $image['url'];
                }
                else
                {
                  $pos = strrpos($url,'/');
                  if ($pos > 8)
                  {
                    $trimmed_url = substr($url,0,$pos);
                  }  
                  else
                  {
                    $trimmed_url = $url;
                  }
                  if (preg_match('/^\//is',$image['url']))
                  {
                    $image_url = $root_url . $image['url'];
                  }
                  else if (preg_match('/^((\.\.\/)+)(.+)/is',$image['url'],$match))
                  {
                    $num_steps = strlen($match[1]) / 3;
                    for($i=0; $i < $num_steps; $i++)
                    {
                      $trimmed_url = substr($trimmed_url,0,strrpos($trimmed_url,'/'));
                    }
                    $image_url = $trimmed_url . '/' . $match[3];
                  }
                  else
                  {
                    $image_url = $trimmed_url . '/' . $image['url'];
                  }
                }
                if ($image_url)
                {
                  $this->printDebug($image_url);
                  if ($fileName = ImageTable::createFiles($image_url, $person->name))
                  {
                      //insert image record
                    try
                    {
                      $this->db->beginTransaction();
                      $image = new Image;
                      $image->filename = $fileName;
                      $image->entity_id = $person->id;
                      $image->title = $person->name;
                      $image->caption = 'From ' . $org->name . '\'s website.';
                      $image->is_featured = true;
                      $image->is_free = false;
                      $image->url = $image_url;
                      
                      $q = LsDoctrineQuery::create()
                          ->from('Image i')
                          ->where('i.entity_id = ?', $person->id)
                          ->addWhere('i.title =?', $person->name)
                          ->addWhere('i.caption =?', $image->caption);
                       
                      if (count($q->execute()) == 0)
                      {                       
                        $image->save();
                        $image->addReference($image_url, null, array('filename'), $org->name . ' website');
                        if (!$bio)
                        {
                          $person->addReference($url,null,null, $org->name . ' website');
                        }
                        $this->printDebug("Imported image: " . $image->filename);
                        $ct = 0;
                      }

                      if (!$this->testMode)
                      {
                        $this->db->commit();
                      }
                      else
                      {
                        $this->db->rollback();
                      }
                    }
                    catch (Exception $e)
                    {
                      $this->db->rollback();
                      throw $e;
                    }  
                  }
                }
              }
              else
              {
                $this->printDebug('no image');
              }
            }
            if ($bio && $image)
            {
              break;
            }

          }
          else
          {
            $this->printDebug('response is error: ' . $url);
          }
          $this->printDebug('-------------');  
        }
        if (count($match_sets))
        {
          //var_dump($match_sets);
        }
      } 
      else
      {
        $this->printDebug("No results found \n");
      }
      if (!$image && !$bio)
      {
        $ct++;
      }
    }
    
  }
  
  private function findBestSummary($match_sets, $person)
  {
  
  
  }
  
  private function findPersonImage($page, $person, $org)
  {
    $image_matches = array();
    if (!stristr($org->name,$person->name_last))
    {
      if ($images = LsHtml::matchImages($page))
      {
        $ct = 0;
        foreach($images as $image)
        {
          if (!stristr($image['url'],'spacer') && !stristr($image['url'],'icon') && (preg_match('/' . $person->name_last . '[^\/]*$/is',$image['url']) || stristr($image['alt'],$person->name_last)))
          {
            $image_matches[] = $image;
            //$this->printDebug('IMAGE MATCH: ' . $image['url'] . ' ' . $image['alt']);
          }
          else
          {
            //$this->printDebug($image['url']);
            $ct++;
          }
        }
        //$this->printDebug($ct . ' images did not match');
      }
    }  
    if (count($image_matches) == 1)
    {
      return $image_matches[0];
    }
    else
    {
      return false;
    }
  }
  
  
  private function findPersonBio($page, $person, $org)
  {
    //$this->printDebug('');
    $name_re = LsString::escapeStringForRegex($person->name_last);
    if (preg_match('/<title>([^<]*)<\/title>/is',$page,$match))
    {
      if (stristr($match[1],$person->name_last) && stristr ($match[1],$person->name_first) && strlen($person->name_first) > 2)
      {
        $name_re .= '|' . LsString::escapeStringForRegex($person->name_first);
      }
    }
    $layout_tags = implode('|',LsHtml::$layoutTags);
    $re2 = '/>([^<]*?(' . $name_re . ')(\s|,|<)(.*?))<\/?(' . $layout_tags . ')/is';
    $re = $re2 . 'u';
    //$this->printDebug($re);
    $bio_match = null;
    if (preg_match_all($re,$page,$matches) || preg_match_all($re2,$page,$matches))
    {
      //$this->printDebug('matches found');
      $arr = array();
      $most_reqs = 0;
      $qual = false;
      $news = false;
      foreach($matches[1] as $match)
      {
        if (stristr($match,'}') || stristr($match,'{') || preg_match('/\svar\s/is',$match))
        {
          //$this->printDebug('FAILED - curly brackets');
          continue;
        }
        $str = LsHtml::replaceEntities($match);
        $str = LsHtml::stripTags($str,'');                
        $str = trim(LsString::spacesToSpace($str));
        
        $this->printDebug(strlen($str));
        if (strlen($str) > 3000)
        {
          $this->printDebug('FAILED - str too long');
          continue;
        }
        if (preg_match('/(^|\b)(' . $name_re . ')\b/is',$str) == 0)
        {
          $this->printDebug($match . 'FAILED - no name match');
          continue;
        }
        $word_count = count(explode(' ',$str));
        if ($word_count < 12)
        {
          $this->printDebug('FAILED - str not long enough');
          continue;
        }
        else 
        {
          if (stristr($str,'announce') || stristr($str,'today') || stristr($str,'—') || stristr($str,'–') || preg_match('/^[^\-]{0,100}\-(\-|\s)/is',$str))
          {
            $news = true;
            $this->printDebug('FAILED: dash / announced / today');
          }
          else if (preg_match('/(^|\s)([\'"”])([^\1]+)\1/is',$str, $qm) && count(explode(' ',$qm[0])) > 6)
          {
            $news = true;
            $this->printDebug('FAILED: quote');
          }  
          else if (preg_match_all('/\s(\p{Ll})+\b/su',$str,$lcm) < 5)
          {
            $this->printDebug('FAILED: not enough lowercase');
          }
          else
          {
            $bio_words = PersonTable::$commonBioWords;
            if (in_array('Lobbyist',$person->getExtensions()))
            {
              $bio_words = array_merge($bio_words,LobbyistTable::$commonBioWords);
            }
            $bio_words = implode('|',$bio_words);
            $bio_word_ct = preg_match_all('/\s(' . $bio_words . ')\s/is',$str,$matches);
            $str = trim($str);
            if (preg_match('/\.$/is',$str) == 0)
            {
              $this->printDebug('no period at end of string');
            }
            else if ($bio_word_ct > 1)
            {
              $news = false;
              $qual = true;
              $arr[] = $str;
            }
            else
            {
              $this->printDebug('less than 2 bio words');
              if ($news == false)
              {
                $str = preg_replace('/^[\,\.\:\;]\s*/su','',$str);
                $arr[] = $str; //array('str' => $str, 'bio_words' => $bio_word_ct);
              }
            }
          }
          //$this->printDebug('');
        }     
      }
      if ($qual)
      {
        $arr = array_unique($arr);
        $ret = false;
        $bio = implode("\n\n", $arr);
        //$this->printDebug($name_re);
        if (strlen($bio) < 3000 && LsString::withinN($bio,'(' . $name_re . ')','(is|was|holds|led|has|had|provides|practices|served|leads)',2))
        {
          if (preg_match('/^.*?\b(' . $name_re . ')\b/is',$bio,$m) && count(explode(' ',$m[0])) < 20)
          {
            $ret = true;
            $this->printDebug('SUCCESS');
          }
        }
        else
        {
          $this->printDebug('within N failed !!!!');
        }
        $org_test = true;
        if ($ret && stristr($org->name,$person->name_last))
        {
          $org_test = false;
          if (strlen($person->name_first) > 1)
          {
            if (preg_match('/([^\s]+\s+){0,14}/is',$arr[0],$beg_match))
            {
              $nf_re = LsString::escapeStringForRegex($person->name_first);
              if (preg_match('/\b' . $nf_re . '\b/is',$beg_match[0]) || preg_match('/\b(Mr|Mrs|Ms)\b/su',$arr[0]))
              {
                $org_test = true;
                //$this->printDebug('PASSED FIRST NAME TEST');
              }
            }            
          }
          else
          {
            if (preg_match('/\b(he|she|him|her|his|mr|ms|mrs)\b/is',$arr[0]))
            {
              $org_test = true;
              //$this->printDebug('PASSED POSSESSIVE TEST');
            }
          }
        }
        if ($ret && $org_test)
        {
          return $bio;
        }
      }   
    }
    else
    {
      $this->printDebug('no matches found');
    }
    return false;
  }

  private function getWikipediaPage($org)
  {
    $query = $org->name;
    $wikipedia_scraper = new WikipediaScraper($this->testMode, $this->debugMode, $this->appConfiguration, $this->user);
	  $wikipedia_scraper->disableBeep();
    $wikipedia_scraper->setShowTime(false);
    //$wikipedia_scraper->setQuery(trim($query));
    $wikipedia_scraper->setUrl('http://en.wikipedia.org/wiki/Wal_Mart_Stores');
    $wikipedia_scraper->run();
    return $wikipedia_scraper->getPage();
  }
 


}