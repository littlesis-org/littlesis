<?php

//grabs top execs and directors for a company

class PublicCompanyRosterScraper extends PublicCompanyScraper
{

  private $corp_ids = null;
  private $search_depth = null;
  private $proxyText = null;
  protected $continuous = false;
  protected $override = false;
  
  //sets the number of form 4 search result pages (each with 10 results) to hit
  
  public function setSearchDepth($depth = 10)
  {
    $this->search_depth = $depth;
  }
  
  public function setContinuous($continuous)
  {
    $this->continuous = $continuous;
  }
  
  public function setOverride($override)
  {
    $this->override = $override;
  }

  public function setCorpIds($limit = 100, $start_id = 1, $ticker = null)
  {

    $q = LsDoctrineQuery::create()
      ->select('p.entity_id')
      ->from('PublicCompany p')
      ->where('p.ticker IS NOT NULL')
      ->limit($limit);  
      
    if ($this->continuous)
    {
      $q->addWhere('NOT EXISTS (SELECT * FROM ScraperMeta s WHERE s.scraper = ? and s.namespace = p.entity_id)','PublicCompanyRosterScraper');
    }
    else if ($ticker)
    {
      $q->addWhere('p.ticker = ?', $ticker);
    }
    if ($start_id)
    {
      $q->addWhere('p.entity_id >= ?', $start_id);
    }

    $corp_ids = $q->execute(array(), Doctrine::HYDRATE_NONE);
    
    foreach($corp_ids as $corp_id) 
    {
      $this->corp_ids[] = $corp_id[0];
    }
    
  }
  
  public function execute()
  {

    if (!$this->safeToRun('sec'))
    {
      $this->printDebug('script already running');
      die;
    }

    if (!isset($this->corp_ids))
    {
      return null;
    }

    foreach($this->corp_ids as $corp_id)
    {
      if (!$this->override && $this->hasMeta($corp_id, 'is_complete') && $this->getMeta($corp_id, 'is_complete'))
      {
        $this->printDebug("Already fetched roster for Entity " . $corp_id . "; skipping...");
        continue;
      }
      else if  (!$this->override && $this->hasMeta($corp_id, 'lacks_cik') && $this->getMeta($corp_id, 'lacks_cik'))
      {
        $this->printDebug("No SEC cik found for Entity " . $corp_id . "; skipping...");
        continue;
      }

      try 
      {
        echo number_format(memory_get_usage()) . "\n";
        $this->browser->restart($this->defaultHeaders);
        $this->db->beginTransaction();
        
        $corp = Doctrine::getTable('Entity')->find($corp_id);
        
        echo "\n*****************\n\nfetching roster for " . $corp->name . " (" . $corp->ticker . ")" . "\n\n";
        
        //grab the corporation's cik if it doesn't have one already
        if (!$corp->sec_cik)
        {
          if ($result = $this->getCik($corp->ticker))
          {
            $corp->sec_cik = $result['cik'];
            if ($corp->Industry->count() == 0)
            {
              if ($result['sic']['name'] && $result['sic']['name'] != '')
              {
                if (!$industry = LsDoctrineQuery::create()->from('Industry i')->where('i.name = ? and i.code = ?', array($result['sic']['name'],$result['sic']['code']))->fetchOne())
                {
                  $industry = new Industry;
                  $industry->name = LsLanguage::nameize(LsHtml::replaceEntities($result['sic']['name']));
                  $industry->context = 'SIC';
                  $industry->code = $result['sic']['code'];
                  $industry->save();
                  $this->printDebug('Industry: ' . $industry->name . ' (' . $industry->code . ')');
                }
                $q = LsQuery::getByModelAndFieldsQuery('BusinessIndustry',array('industry_id' =>$industry->id,'business_id' => $corp->id));
                if (!$q->fetchOne())
                {
                  $corp->Industry[] = $industry;
                }
              }
              $corp->save();
              $corp->addReference($result['url'],null,$corp->getAllModifiedFields(),'SEC EDGAR Page');
            }           
            
          }
          else 
          {
            $this->saveMeta($corp->id, 'lacks_cik', true);
            $this->db->commit();
            continue;
          }
        }
        
        if ($corp->sec_cik)
        {
        
          $form4_urls = $this->getForm4Urls($corp->sec_cik);
          
          $roster = array();
          foreach($form4_urls as $url_arr)
          {
            $result = $this->getForm4Data($url_arr, $corp->sec_cik);
            if ($result)
            {
              $roster[] = $result;
            }

          }
          
          $proxy_urls = $this->getProxyUrls($corp->sec_cik, array('2007','2008'));
          if (count($proxy_urls))
          {
            $proxy_url = $proxy_urls[0]['url'];
            $proxy_year = $proxy_urls[0]['year'];
            //search proxy for names appearing on form 4s
            $roster = $this->getProxyData($roster, $proxy_url, $proxy_year);
            
          }
          else 
          {
            $this->saveMeta($corp->id, 'lacks_cik', true);
            $this->db->commit();
            continue;
          }

          $corp->addReference($proxy_url,null,null,$proxy_year . ' Proxy');

          //loop through names found on form 4s and search proxy
          foreach ($roster as $r)
          {
            echo "\n" . $r['personName'] . " is director? " . $r['isDirector'] .  " at " . $r['form4Url'] . " \n";

            if (isset($r['proxyName']))
            {
              echo "in proxy as " . $r['proxyName'] . " \n";
            }
            else
            {
              echo "not in proxy \n\n";
            } 

            //make sure this appears in the proxy and has either an officer title or is a director
            if (isset($r['proxyName']) && ($r['isDirector'] == '1' || $r['officerTitle'] != ''))
            {
              $p = EntityTable::getByExtensionQuery('BusinessPerson')->addWhere('businessperson.sec_cik = ?', $r['personCik'])->fetchOne();   
        
              if (!$p)
              {
                $p = $this->importPerson($r, $corp->name);
              }
              
              if ($p)
              {
                $this->importAddress($r['address'], $p, $r, $corp->name);
                if($r['isDirector'] == 1)
                {
                  $this->importRelationship($p, $corp, 'Director', $r);
                }
                if($r['officerTitle'] != '')
                {
                  $descriptions = $this->parseDescriptionStr($r['officerTitle'], $corp);
                  foreach($descriptions as $d)
                  {
                    if ($d['note'])
                    {
                      $position = $d['description'] . ' (' . implode(', ',$d['note']) . ')'; 
                    }
                    else
                    {
                      $position = $d['description'];
                    }
                    $this->importRelationship($p, $corp, $position, $r);
                  }
                }
              }
            }           
          }
                
        }
        
        if (!$this->testMode)
        {
          $this->db->commit();
        }
        if (isset($proxy_url))
        {
          $proxy_scraper = new ProxyScraper($this->testMode,$this->debugMode, $this->appConfiguration);
          $proxy_scraper->setCorpIds(1, $corp->id);
          $proxy_scraper->setProxy($this->proxyText,$proxy_url,$proxy_year);
          $proxy_scraper->disableBeep();
          $proxy_scraper->run();
        }
      }
      catch (Exception $e)
      {
				//something bad happened, rollback
				$this->db->rollback();		
        throw $e;
      }    
      
      $this->saveMeta($corp_id, 'is_complete', true);
    }
  }
  
  /*
    full text EDGAR search for form 4s for $corp_cik
    hits up to $lim search index pages (10 results on each page)
    only adds urls of form 4s to results array for people whose cik identifiers have not already been found
  `returns array of form 4 urls
  */
  
  private function getForm4Urls($corp_cik)
  {

    //to check out the page, an example url: http://searchwww.sec.gov/EDGARFSClient/jsp/EDGAR_Query_Result.jsp?queryString=&queryForm=Form4&isAdv=1&queryCik=876437&numResults=100#topAnchor
    
    $url = 'http://searchwww.sec.gov/EDGARFSClient/jsp/EDGAR_Query_Result.jsp?queryString=&queryForm=Form4&isAdv=1&queryCik=' . $corp_cik . '&numResults=10';
    $form4_urls = array();
    $search_pages = 0;
    $unique_ciks = array();
    $next_page = true;
    
    //loop through search results
    while ($next_page == true && $search_pages < $this->search_depth)
    {
      if (!$this->browser->get($url)->responseIsError())
      {
        $selector = $this->browser->getResponseDomCssSelector();
        $text = $this->browser->getResponseText();
        $rows = $selector->matchAll('tr')->getNodes();
        for($i=0; $i < count($rows); $i++)
        {

          $row = new sfDomCssSelector(array($rows[$i]));
          $links = $row->matchAll('a')->getNodes();
          $person_cik = null;
          foreach ($links as $link)
          {
            if ($link->getAttribute('name') == 'cikSearch')
            {
              $person_cik = trim($link->textContent);
              //break if it's not same as corp's cik
              if ($person_cik != $corp_cik) break;
            }
          }
          
          if (!$person_cik) continue;
         
          if(!in_array($person_cik, $unique_ciks))
          {
            $prev_row = new sfDomCssSelector($rows[$i-1]);
            $href = $prev_row->matchSingle('a')->getNode();
            if (!is_object($href)) continue;
            $href = $href->getAttribute('href');
            
            //if not an xml doc, grab the parent filing underneath the listing
            if(!stripos($href,'.xml'))
            {
              $next_row = new sfDomCssSelector($rows[$i+2]);
              $href = $next_row->matchSingle('a')->getNode();
              if (!is_object($href)) continue;
              if (trim($href->getAttribute('title')) == 'Parent Filing')
              {
                $href = $href->getAttribute('href');
              }
              else continue;
            }
            
            if (preg_match("/\'([^\']+)\'/",$href,$matches))
    		    {
    		      $unique_ciks[] = $person_cik;
    		      $u = str_replace('xslF345/','',$matches[1]);
    		      if ($u != $matches[1])
    		      {
    		        $form4_urls[] = array('xmlUrl' => $u, 'htmlUrl' => $matches[1]);
    		      }
    		    }
          }
         
        }
        $next_page = false;  
        
        $links = $selector->matchAll('.clsBlueBg')->getNodes();
        foreach ($links as $link)
        {
          if ($link->getAttribute('title') == "Next Page")
          {
            $url = "http://searchwww.sec.gov" . $link->getAttribute('href');
            $next_page = true;
          }
        }  
      }
      $search_pages++;
    }
    return $form4_urls;
  }

  /*
    passes form4 data retrieval off to Form4Scraper
    returns array of data from filing including personName, officerTitle, personCik, form4_url,address
  */
  
  private function getForm4Data($url_arr, $corp_cik)
  {
    $url = $url_arr['xmlUrl'];
    $form4_scraper = new Form4Scraper($this->testMode, $this->debugMode, $this->appConfiguration);
    $form4_scraper->disableBeep();
    $form4_scraper->setUrl($url);
    $form4_scraper->setShowTime(false);
    $form4_scraper->run();
    $results = $form4_scraper->getResults();
    $corp_cik = (string) $corp_cik;
    if (strpos($results['personCik'],$corp_cik) !== false || stristr($results['corpCik'],$corp_cik) === false)
    {
      return null;
    }
    else
    {
      $results['form4Url'] = $url;
      $results['htmlUrl'] = $url_arr['xmlUrl'];
      return $results;
    }
  }
  
  /*
    searches for proxies for corporation with given corp cik identifier for years in array $years (defaulting to 2008 and 2007)
    returns array of proxy urls
  */

  private function getProxyUrls($corp_cik, $years = array(2007, 2008))
  {
    $years = implode('|', $years);
    $url = "http://searchwww.sec.gov/EDGARFSClient/jsp/EDGAR_Query_Result.jsp?queryString=&queryForm=DEF+14A&isAdv=1&queryCik=" . $corp_cik . "&numResults=10";
    $proxy_urls = array();
    if (!$this->browser->get($url)->responseIsError())
    {
      $re = '/(' . $years . ')<\/i>(<[^>]*>){2}<a[^\']+\'([^\']+)(?<=\.htm)\'[^>]*>([^<]*)</isu';
  	  $text = $this->browser->getResponseText();  		    
      //echo $text;  
  		$matched = preg_match_all($re,$text,$matches, PREG_SET_ORDER);
  		if ($matched > 0)
  		{
  		  echo 'matched';
  		  foreach($matches as $match)
  		  {
  		    if (stristr($match[3],'def14') !== false || stristr($match[4],'def 14') !== false)
  		    {
  		      $proxy_urls[] = array('url' => $match[3], 'year' => $match[1]);
  		    }
  		  }
      } 
    }
    else
		{
			//Error response (eg. 404, 500, etc)
  	  //$log = fopen($this->logFile, 'a');
			//fwrite($log, "Couldn't get " . $url . "\n");
			//fclose($log);
		}
    return $proxy_urls;
  }
  
  /*
    searches proxy at given url for names in roster
    given the lack of non-uniformity of names and issues with the format, name search is fairly complicated 
    returns roster with proxyName field added for names that do appear 
  */
  
  private function getProxyData($roster, $url, $proxy_year)
  {
    echo "fetching data from proxy at $url \n\n";
    $people_count = 0;
    if (!$this->browser->get($url)->responseIsError())
    {
      $this->proxyText = $this->browser->getResponseText();
      $this->proxyText = LsHtml::replaceEntities($this->proxyText, ENT_QUOTES, 'UTF-8');   
      $this->proxyText = LsString::utf8TransUnaccent($this->proxyText);
      foreach($roster as &$r)
      {
        //make sure this is not form 4 data for a corporation, continue to the next if it is
        if ($r['officerTitle'] == '' && $r['isDirector'] != 1 && strtoupper($r['isDirector']) != strtoupper('true')) {      
          continue;
        }
        //echo $re;
        $parts = preg_split("/[\s|\.]+/", $r['personName'], -1, PREG_SPLIT_NO_EMPTY);
        
        //first word, but has to be part of last name because form4 names are in format RUBIN ROBERT E
        $last = trim($parts[0]);
        
        //sometimes O'LEARY can appear as O LEARY in the form 4
        if (strlen($last) == 1)
        {
          $r['personName'] = $last . substr($r['personName'],2);
          $parts = preg_split("/[\s|\.]+/", $r['personName'], -1, PREG_SPLIT_NO_EMPTY);
          $last = trim($parts[0]);
        }
        
        //prepare regex to match occurrences of full name
        //case insensitive to accommodate for various irregularities in names
        $re = LsLanguage::buildLooseNameRegex($r['personName']);        
        
        $offset = 0;
        $found = true;
        
        //use stripos (much faster than regex) to find occurrences of the first word in the form 4 name (assumed to be part of the last name)
        //needs to be case insensitive        
        //continue searching for last name in proxy until a matching full name (proxyName) is found
        while (!isset($r['proxyName'])  && $found !== false)
        {
          $found = stripos($this->proxyText, $last, $offset);
          //$this->printDebug('found at pos:' . $found);
          $offset = $found + 1;
          if ($found !== false)
          {
            $str = substr($this->proxyText,$found - 70, 120);
            //$this->printDebug('found string: ' . $str);
            //$this->printDebug($re);
            preg_match_all($re,$str,$matches,PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
            //$this->printDebug('matchcount is ' . count($matches));
            foreach ($matches as $match)
            {
              if (stristr($match[1][0],'=')) continue;
              //since we may or may not be working with the full last name, use getLastName to return full last name
              $new_last = $this->getLastName($r['personName'],$match[1][0]);
              
              if ($new_last)
              {
                //if last name produced by case insensitive search has no capital letters, not a match
                if (preg_match('/\p{Lu}/su',$new_last) == 0)
                {
                  continue;
                }
                
                //now that we have a last name, pull the full name from the string
                $name = LsLanguage::getNameWithLast($match[0][0],$new_last);
                
                if ($name)
                {
                  $parts = preg_split('/\s+/isu',$name['nameStart'],-1, PREG_SPLIT_NO_EMPTY);
                  $non_prefixes = array_diff($parts,PersonTable::$nameParsePrefixes);
                  
                  //if all we've found are matching prefixes, not a match
                  if (count($non_prefixes) == 0)
                  {
                    continue;
                  }
                 
                  else
                  {
                    
                    $name1_parts = preg_split('/\s+/',$r['personName'],-1,PREG_SPLIT_NO_EMPTY);
                    $ct = 0;
                    
                    //compatibility check to correct for vagueness of regex
                    foreach ($non_prefixes as $n)
                    {
                      foreach ($name1_parts as $p)
                      {
                        if (stripos($n,$p) === 0 || stripos($p,$n) === 0)
                        {
                          $ct++;
                        }
                      }
                    }
                    
                    //phew -- if name is (somewhat) compatible, assume we've found it
                    if ($ct > 0)
                    {
                      $r['proxyUrl'] = $url;
                      $r['proxyYear'] = $proxy_year;
                      $r['nameLast'] = trim(LsString::spacesToSpace($name['nameLast']));
                      $r['proxyName'] = trim(LsString::spacesToSpace($name['nameFull']));
                    }             
                  }
                }
              }
            }
          }
        }
      }
      unset($r);
    }
    else
	  {
			//Error response (eg. 404, 500, etc)
  	  $log = fopen($this->logFile, 'a');
			fwrite($log, "Couldn't get " . $url . "\n");
			fclose($log);
		}
    return $roster;
  }
  
  /*
    given two strings, one with the last name first and one with the first name last, 
    returns the full last name
  */
  
  private function getLastName($lastfirst, $firstlast)
  {
    $parts = preg_split("/[\s|\.|-]+/", $lastfirst, -1, PREG_SPLIT_NO_EMPTY);
    $matched = true;
    $last = null;
    foreach($parts as &$part)
    {
      $part = LsString::escapeStringForRegex($part);
    }
    $arr = array();
    foreach ($parts as $part)
    {
      if (count($arr) > 0 && LsArray::inArrayNoCase($part,PersonTable::$nameParseSuffixes) != false)
      {
        break;
      }
      $part = $part[0] . "'?" . substr($part,1);
      $arr[] = $part;
      $re = implode("\b[\s-]+\b",$arr);
      $re = "/\b" . $re . "\b/is";
      if (preg_match($re,$firstlast,$matches))
      {
        $last = $matches[0];
      }
      else
      {
        break;
      }
    }
    return $last;    
  }
  
  
  /*
    imports person data
  */
  
  private function importPerson($person_arr, $corp_name)
  {
    $last = $person_arr['nameLast'];
    $p1 = PersonTable::parseFlatName($person_arr['proxyName'], $last);
    //$p1->save();
    
    //prep form 4 name for parseFlatName
    $rest = substr($person_arr['personName'],strlen($last));
    $parts = preg_split('/\s+/s',$rest,-1, PREG_SPLIT_NO_EMPTY);
    $suffixes = array();
    $prefixes = array();
    $fm = array();
    
    //transfer suffixes to end of name passed to parseFlatName, prefixes to beginning of name
    foreach($parts as $p)
    {
      if (strlen($p) > 1 && $s = LsArray::inArrayNoCase($p,PersonTable::$nameParseSuffixes))
      {
        $suffixes[] = $s;
      }
      else if (strlen($p) > 1 && $s = LsArray::inArrayNoCase($p,PersonTable::$nameParsePrefixes))
      {
        $prefixes[] = $s;
      }
      else
      {
        $fm[] = $p;
      }
    }

    $suffixes = implode(' ', $suffixes);
    $prefixes = implode(' ', $prefixes);
    $fm = implode(' ', $fm);
    $flatname = $prefixes . ' ' . $fm . ' ' . $last . ' ' . $suffixes;
    $p2 = PersonTable::parseFlatName($flatname, $last);
    //$p2->save();
    $p = $this->mergePeople($p1, $p2);
    $case = LsString::checkCase($last);
    if ($case == 'upper')
    {
      $last = LsLanguage::nameize($last);
    }
    $p->name_last = $last;
    $p->name_first;
    $p->addExtension('BusinessPerson');
    $p->sec_cik = $person_arr['personCik'];    
    $p->save();
    echo $p->name . " saved \n";
    
    //save source info
    $p->addReference($person_arr['form4Url'], null, $fields = array('name_first', 'name_last', 'name_middle', 'name_suffix', 'name_prefix', 'name_nick'),$corp_name . ' Form 4',  null, $person_arr['date']);
    //$p->addReference($person_arr['proxyUrl'], null, $fields = array('name_first', 'name_last', 'name_middle', 'name_suffix', 'name_prefix', 'name_nick'), $corp_name . ' proxy, ' . $person_arr['proxyYear'], );

    return $p;
  }
  
  private function importAddress ($address_arr, $person, $person_arr, $corp_name)
  {
    $a = new Address;
    $a->street1 = LsLanguage::nameize($address_arr['street1']);
    $a->street2 = LsLanguage::nameize($address_arr['street2']);
    $a->city = $address_arr['city'];
    $a->Category = Doctrine::getTable('AddressCategory')->findOneByName('Mailing');
    if ($state = AddressStateTable::retrieveByText($address_arr['state']))
    {
      $a->State = $state;
    }
    else
    {
      return;
    }

    $a->postal = $address_arr['postal'];
    $modifiedFields = $a->getAllModifiedFields();
    
    if ($person->addAddress($a))
    {
      $person->save();
      $a->addReference($person_arr['form4Url'], null, null,$corp_name . ' Form 4',  null, $person_arr['date']);
    }

  }

  /*
    given person id, corp id, position (as in 'Director'), and url, creates a relationship
    assumes relationship in 'Position' category
  */
  
  private function importRelationship($person, $corp, $position, $person_arr)
  {
    $r = new Relationship;
    $r->entity1_id = $person->id;
    $r->entity2_id = $corp->id;
    $r->setCategory('Position');
    $r->is_current = 1;
    $r->description1 = $position;
    $q = LsDoctrineQuery::create()
          ->from('Relationship r')
          ->where('r.entity1_id = ?', $person->id)
          ->addWhere('r.entity2_id = ?', $corp->id)
          ->addWhere('r.category_id = ?', RelationshipTable::POSITION_CATEGORY)
          ->addWhere('r.description1 = ?', $r->description1);

    if ($q->count() == 0)
    {
      //$r->relationship_id = $r->id;
      if ($person_arr['isDirector'] == '1')
      {  
        $r->is_board = 1;
        $r->is_executive = ($position != 'Chairman' && $position != 'Director') ? 1 : 0;
      }
      else
      {
        $r->is_executive = 1;
        $r->is_board = 0;        
      }
      
      $r->save();
      $r->addReference($person_arr['form4Url'], null, $fields= array('entity1_id', 'entity2_id', 'is_current', 'description1','description2', 'category_id', 'relationship_id', 'is_board', 'is_executive'), $corp->name . ' Form 4', null, $person_arr['date']);

      $r->addReference($person_arr['proxyUrl'], null, $fields= array('entity1_id', 'entity2_id', 'is_current','description1','description2', 'category_id', 'relationship_id', 'is_board', 'is_executive'), $corp->name . ' proxy, ' . $person_arr['proxyYear']);
    }    
    else
    {
      $this->printDebug('relationship exists');    
    }
  }
  

   
  /*
     returns a person with a new name based upon the names in the two person objects passed to the function
     formPerson is given precedence; for the new Person to take values from proxyPerson, the values
     must be compatible with those in formPerson due to proxy name matching's messiness
  */
  
  private function mergePeople($proxyPerson,$formPerson)
  {
    $formPerson->name_first;
    $formPerson->name_last;
    $person = new entity;
    $person->addExtension('Person');
    $person->name_first = $formPerson->name_first;
    $person->name_middle = $formPerson->name_middle;
    $person->name_last = $formPerson->name_last;
    $person->name_nick = $formPerson->name_nick;
    $person->name_prefix = $formPerson->name_prefix;
    $person->name_suffix = $formPerson->name_suffix;
    
    $compatible = false;
  
    if (!$proxyPerson->name_first or $proxyPerson->name_first == '')
    {
      return $person;
    }
            
    //check first name compatibility before doing anything
    
    if (stripos($proxyPerson->name_first,$formPerson->name_first) === 0 ||
                    stripos($formPerson->name_first,$proxyPerson->name_first) === 0)
    {
      
      if ($formPerson->name_middle == null || $formPerson->name_middle == '')
      {
        $compatible = true;
        $person->name_middle = $proxyPerson->name_middle;
        if (strlen($proxyPerson->name_first) > strlen($formPerson->name_first))
        {
          $person->name_first = $proxyPerson->name_first;
        }
      }
      
      //unclear whether this is a good idea
      else if (!$proxyPerson->name_middle || $proxyPerson->name_middle == '')
      {
        $compatible = true;
        if (strlen($proxyPerson->name_first) > strlen($formPerson->name_first))
        {
          $person->name_first = $proxyPerson->name_first;
        }
      }
      
      //check middle name compatibility if both exist
      else if (stripos($proxyPerson->name_middle,$formPerson->name_middle) === 0 ||
                      stripos($formPerson->name_middle,$proxyPerson->name_middle) === 0)
      {
        $compatible = true;
        if (strlen($proxyPerson->name_middle) > strlen($formPerson->name_middle))
        {
          $person->name_middle = $proxyPerson->name_middle;
        }
        if (strlen($proxyPerson->name_first) > strlen($formPerson->name_first))
        {
          $person->name_first = $proxyPerson->name_first;
        }
      }
      
    }
    
    //if names have proven compatible, then check generational suffixes (Jr, Sr etc)
    if ($compatible == true)
    {
      $form_suffixes = explode(' ', $formPerson->name_suffix);
      $form_generationals = array_intersect($form_suffixes,LsLanguage::$generationalSuffixes);
      $proxy_suffixes = explode(' ', $proxyPerson->name_suffix);
      $common_generationals = array_intersect($form_generationals,$proxy_suffixes); 
      //if there are no generationals in the form 4 name, go ahead and grab prefixes, suffixes, etc from proxy name
      if (count($form_generationals) == 0 || $form_generationals = $common_generationals)
      {        
        if (strlen($proxyPerson->name_nick) > strlen($formPerson->name_nick))
        {
          $person->name_nick = $proxyPerson->name_nick;
        }
        if (strlen($proxyPerson->name_prefix) > strlen($formPerson->name_prefix))
        {
          $person->name_prefix = $proxyPerson->name_prefix;
        }
        if (strlen($proxyPerson->name_suffix) > strlen($formPerson->name_suffix))
        {
          $person->name_suffix = $proxyPerson->name_suffix;
        }
      }            
    }   
    return $person; 
  }
  
  public function parseDescriptionStr($str, $corp)
  {
  	$descriptions = array();
  	$remains = array();
  	
  	//cleanup text to be parsed
  	$str = trim($str);
  
  	$str = str_replace('.', ' ', $str);
  	$str = preg_replace('/\s{2,}/', ' ', $str);
  	
  	$name_re = LsString::escapeStringForRegex($corp->name);
  	$str = preg_replace('/\b' . $name_re . '\b/isu', '', $str);
  	
  	if ($corp->name_nick)
  	{
  	  $nick_re = LsString::escapeStringForRegex($corp->name_nick);
  	  $str = preg_replace('/\b' . $nick_re . '\b/isu', '', $str);
  	}
  	
  	if ($corp->ticker)
  	{
  	  $tick_re = LsString::escapeStringForRegex($corp->ticker);
  	  $str = preg_replace('/\b' . $tick_re . '\b/isu', '', $str);
  	}
  	
  	//split by commas
  	$parts = preg_split('/,|;|\band\b|(?<!C[Oo])\-|\bAND\b|\s&\s|\//', $str, -1, PREG_SPLIT_NO_EMPTY);
  	foreach ($parts as $part)
  	{
  		$part = trim($part);
  		$part = preg_replace('/\s{2,}/', ' ', $part);
  
  		//abbreviation replacements
  		$part = preg_replace('/( |^)(\w) (\w) (\w)( |$)/', '\2\3\4', $part);
  		$part = preg_replace('/(Interim|Acting|Incoming) /i', '', $part);
  		$part = preg_replace('/Sr /i', 'Senior ', $part);
  		$part = preg_replace('/Chf /i', 'Chief ', $part);
  		$part = preg_replace('/( |^)V( |$)/i', ' Vice ', $part);
  		$part = preg_replace('/( |^)VP( |$)/i', ' Vice President ', $part);
  		$part = preg_replace('/( |^)VC( |$)/i', ' Vice Chairman ', $part);
      $part = preg_replace('/( |^)Chr( |$)/i', ' Chairman ', $part);
      $part = preg_replace('/( |^)Ofcr( |$)/i', ' Officer ', $part);
  		$part = preg_replace('/( |^)Vice P( |$)/i', ' Vice President ', $part);
  		$part = preg_replace('/( |^)(Ex|Exec)( |$)/i', ' Executive ', $part);
  		$part = preg_replace('/( |^)EVP( |$)/i', ' Executive Vice President ', $part);
  		$part = preg_replace('/( |^)(Off|Offic|Offcr)( |$)/i', ' Officer ', $part);
  		$part = str_replace('Gen ', 'General ', $part);
  		$part = preg_replace('/( |^)(Op|Oper) /', ' Operating ', $part);
  		$part = preg_replace('/( |^)(Bd|Brd)( |$)/i', ' Board ', $part);
  		$part = preg_replace('/of Board/i', ' of the Board', $part);
  		$part = preg_replace('/( |^)COB( |$)/i', ' Chairman of the Board ', $part);
  		$part = preg_replace('/( |^)(Pres|Prs|Presid|Prsdt|Prsdnt)( |$)/i', ' President ', $part);
  		$part = preg_replace('/( |^)Admin( |$)/i', ' Administrative ', $part);
      $part = preg_replace('/( |^)Info( |$)/i', ' Information ', $part);
  		$part = preg_replace('/\bComm\b/i', 'Committee', $part);
  		$part = preg_replace('/\bInc\b/i', '', $part);
  		$part = preg_replace('/( |-|^)(Ch|Chm|Chmn|Chrm|Chrmn|Chair|Chairmain|Chariman)( |$)/i', '\1Chairman ', $part);
  		$part = preg_replace('/(Sec|Secr|Secy|Secretar|Secreta)( |$)/i', 'Secretary ', $part);
  		$part = str_replace('Vice-', 'Vice ', $part);
  		$part = preg_replace('/( |^)Non /i', ' Non-', $part);
  		$part = preg_replace('/\bCompl\b/i','Compliance',$part);
  		$part = str_ireplace('of Advisory', 'of the Advisory', $part);
  		$part = preg_replace('/Advisory (Panel|Council)/i', 'Advisory Board', $part);
  		$part = str_ireplace('Independent ', '', $part);
  		$part = str_ireplace('Lead ', '', $part);
  		$part = str_ireplace('Corporate ', '', $part);
  		$part = str_ireplace('Outside ', '', $part);
  		$part = str_ireplace('Non-interested', '', $part);
  		$part = str_ireplace('Interested', '', $part);
  		$part = str_replace('Main ', '', $part);
  		$part = str_ireplace('Presiding ', '', $part);
  		$part = str_ireplace('Founding ', '', $part);
      $part = str_ireplace('Acctg', 'Accounting', $part);
  		$part = str_ireplace('Chairperson', 'Chairman', $part);
  		$part = str_ireplace('Chairwoman', 'Chairman', $part);
  		$part = str_ireplace("Gen'l",'General',$part);
  		$part = trim($part);
  		$part = preg_replace('/\s{2,}/', ' ', $part);
      $position = array('description' => null, 'note' => array());
      
      if ($part != '')
      {
  		  
    		//look for matching title
    		$p = LsArray::inArrayNoCase($part,PositionTable::$businessPositions);
    		if ($p)
    		{
    		  $position['description'] = $p;    		  
    		}
    		else if ($q = Doctrine::getTable('Relationship')->findOneByDescription1($position))
    		{
    		  $position['description'] = $q->description1;
    	  }
    		else
    		{
    		  if (count($descriptions) == 0)
    		  {
    		    $part_splat = LsString::split($part);
            $note = array();
            //$this->printDebug($part);
            //var_dump($part_splat);
            $lim = count($part_splat) -1;
    		    for ($i = 0; $i < $lim; $i++)
    		    {
    		      $note[] = array_pop($part_splat);
    		      $part_new = implode(' ',$part_splat);
    		      if (strtoupper($part_new) == 'DIRECTOR') break;
    		      $p = LsArray::inArrayNoCase($part_new,PositionTable::$businessPositions);
          		if ($p)
          		{
          		  $position['description'] = $p;    		  
          		}
          		else if ($q = Doctrine::getTable('Relationship')->findOneByDescription1($position))
          		{
          		  $position['description'] = $q->description1;
          	  }
    		    }
    		    if (!$position['description'])
    		    {
    		      $position['description'] = $part;
    		    }
    		  }
    		  else
    		  {
    			  $descriptions[count($descriptions)-1]['note'][] = $part;
    			}
    		}
    		if (isset($position['description'])) $descriptions[] = $position;
      }
  	}
  	return $descriptions;
  
  }
  
}