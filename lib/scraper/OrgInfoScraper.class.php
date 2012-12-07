<?php

class OrgInfoScraper extends Scraper
{
  
  protected $_orgs = null;
  protected $_orgExtensions = array('LobbyingFirm');
  protected $_metaName = 'lobbying_websites';//'lobbying_websites';
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
      $q = EntityTable::getByExtensionQuery($this->_orgExtensions)->addWhere('e.website is NULL')->limit(200);
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
    if (!$this->safeToRun('orginfo'))
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
        $this->getWebsite($org);
        $this->saveMeta($this->_metaName, $this->_metaPredicate, $org->id);
        $this->saveMeta($org->id,'scraped',$this->_metaPredicate);
        $this->db->commit();
      }      
      catch (Exception $e)
      {
				$this->db->rollback();		
        throw $e;
      }
    }  
  }
  

  
  private function getWebsite($org)
  {    
    $this->printDebug($org->name);
    $query = $org->name;
	  $google_scraper = new LsGoogle();
    $google_scraper->setQuery(trim($query));
    $google_scraper->execute();
    $results = $google_scraper->getResults();
    foreach($results as $result)
    {
      preg_match('/http\:\/\/[^\/]+\//isu',$result->unescapedUrl,$match);
      if (!$match) continue;      
      $trimmed_url = $match[0];
      if ($this->checkUrl($trimmed_url,$org->name))
      {
        $this->printDebug('passed: ' . $result->url);//titleNoFormatting);  
        //$this->printDebug($result->url);          
        //$this->printDebug($result->content);
        $people = $org->getRelatedEntitiesQuery('Person')->execute();
        $num_arr = array();
        $people_ct = 0;
        $multi = false;
        foreach ($people as $p)
        {
          $q = 'site:' . $trimmed_url . ' "' . $p->name_first . ' ' . $p->name_last . '"';
          $this->printDebug($q);
          $google_scraper->setQuery($q);
          $google_scraper->execute();
          $num = $google_scraper->getNumResults();
          if ($num > 0)
          {
            $people_ct++;
            if ($num > 1) $multi++;
          }  
          if ($people_ct > 1)
          {
            break;
          }
        }
        if ($people_ct == 0)
        {
          $this->printDebug('no people found');
        }
        else
        {
          if (!$org->website)
          {
            $org->website = $trimmed_url;
            $this->printDebug('website saved for ' . $org->name . ': ' . $trimmed_url);
          }
          $org->save();
          break;
        }
        $this->printDebug('');
      }
      else
      {
        $this->printDebug('failed: ' . $result->url); 
      }
    }
    $this->printDebug('***');
  }
    
  public function checkUrl($url,$org_name)
  {
    $ret = false;
    if (preg_match('/\/\/[^\/]+\//isu',$url,$match))
    {
      $url = $match[0];
    }
    $parts = LsString::split($org_name);

    $all = '';
    $no_common ='';
    $no_corp = '';
    $stripped = '';
    
    $common = array('and','the','of','in','at','&');
    $abbrevs = array('Corporation','Inc','Group','LLC','LLP','Corp','Co','Cos','LP','PA','Dept','Department','International','Administration');
    $both = array_merge($common, $abbrevs);    
    
    foreach($parts as $part)
    {
      if (!LsArray::inArrayNoCase($part,$common)) $no_common .= $part[0];
      if (!LsArray::inArrayNoCase($part,$abbrevs)) $no_corp .= $part[0];
      if (!LsArray::inArrayNoCase($part,$both)) $stripped .= $part[0];
      $all .= $part[0];

      if (stristr($url,$part) && strlen($part) > 1 && !LsArray::inArrayNoCase($part,$both))
      {
        $ret = true;
      }
    }
    
    if ($ret == false)
    {
      if (strlen($all) > 2 && stristr($url,$all)) $ret = true;
      if (strlen($no_common) > 2 && stristr($url,$no_common)) $ret = true;
      if (strlen($no_corp) > 2  && stristr($url,$no_corp)) $ret = true;
    }
      
    return $ret;
  }
  
  private function checkSite($url,$description,$org)
  {
    $ret = false;
    $this->browser->get($url);
    $text = $this->browser->getResponseText();
    $ext = $org->getExtensions();
    if (in_array('LobbyingFirm',$ext))
    {
      $score = 0;
      $arr = array('government','relations','lobbying','affairs','political','represent','legislat','congress','federal','regulation','regulatory','law firm','strategies');
      foreach ($arr as $a)
      {
        if (stristr($text,$a))
        {
          $score++;
        }
      }
      if ($score > 1) $ret = $text;
    }
    else if (in_array('GovernmentBody',$ext))
    {
      if (stristr($url,'.gov'))
      {
        $ret = $text;
      }
    }
    return $ret;
  }
  
  public function getSummary($str,Entity $e)
  {
    $str = LsHtml::replaceEntities($str);
    $name_re = array();
    $name_re[] = $e->getNameRegex();
    if ($e->name_nick && $e->name_nick != '')
    {
      $name_re[] = LsString::escapeStringForRegex($e->name_nick);
    }
    $name_re = implode('|', $name_re);
    $style_tags = implode('|',LsHtml::$fontStyleTags);
    $layout_tags = implode('|',LsHtml::$layoutTags);
    $re = '/((' . $name_re . ')(.*?))<\/?(' . $layout_tags . ')/isu';
    $this->printDebug($re);
    $results = null;
    if (preg_match_all($re,$str,$matches))
    {
      $results = $matches[1];
      foreach($results as $result)
      {
        $result = LsString::spacesToSpace(LsHtml::stripTags($result));
        $this->printDebug($result);
      }
    }
    return $results;
  }

}