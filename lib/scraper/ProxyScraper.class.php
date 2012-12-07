<?php

class ProxyScraper extends PublicCompanyScraper
{
  private $url = null;
  private $text = null;
  private $corp_ids = array();
  private $proxy = null;
  private $corp = null;
  private $people = null;
  private $years = array(2007,2008);
  private $pages = null;
  private $sets = null;
  private $year = null;
  private $need_proxy = true;
  
  public function setCorpIds($limit = 10, $start_id = 1, $ticker = null)
  {
  
    $q = Doctrine_Query::create()
      ->select('p.entity_id')
      ->from('PublicCompany p')
      ->where('p.entity_id >= ? and p.ticker IS NOT NULL', $start_id)
      ->limit($limit);
      
    if ($ticker)
    {
      $q->addWhere('p.ticker = ?', $ticker);
    }
    
    $corp_ids = $q->execute(array(), Doctrine::HYDRATE_NONE);
    
    foreach($corp_ids as $corp_id) 
    {
      $this->corp_ids[] = $corp_id[0];
    }
    
  }
  
    
  public function setProxy($text, $url, $year)
  {
    $this->url = $url;
    $this->text = $text;
    $this->year = $year;
    $this->need_proxy = false;
  }

  public function execute()
  {
  
    foreach($this->corp_ids as $corp_id)
    {
      try
      {
        $this->db->beginTransaction();
        $this->corp = Doctrine::getTable('Entity')->find($corp_id);
        if (!$this->corp->sec_cik)
        {
          if ($result = $this->getCik($this->corp->ticker))
          {
            $this->corp->sec_cik = $result['cik'];
            if (!$this->corp->Industry->count())
            {
              if ($result['sic']['name'] && $result['sic']['name'] != '')
              {
                $q = LsDoctrineQuery::create()->from('Industry i')->where('i.name = ? and i.code = ?', array($result['sic']['name'],$result['sic']['code']))->fetchOne();
                if (!$industry = $q->fetchOne())
                {
                  $industry = new Industry;
                  $industry->name = LsLanguage::nameize(LsHtml::replaceEntities($result['sic']['name']));
                  $industry->context = 'SIC';
                  $industry->code = $result['sic']['code'];
                  $industry->save();
                }
                $q = LsQuery::getByModelAndFieldsQuery('BusinessIndustry',array('industry_id' =>$industry->id,'business_id' => $this->corp->id));
                if (!$q->fetchOne())
                {
                  $this->corp->Industry[] = $industry;
                }
              }
              $this->corp->save();
              $this->corp->addReference($result['url'],null,$corp->getAllModifiedFields(),'SEC EDGAR Page');
            }           
          }  
          $this->corp->save();
        }  
        if ($this->corp->sec_cik)
        {
          $category = Doctrine::getTable('RelationshipCategory')->findOneByName('Position');
          $this->people = $this->corp->getRelatedEntitiesQuery('Person',$category->id,'Director',null,null,false)->execute();
          if (count($this->people) > 1)
          {
            if($this->need_proxy)
            {
              $this->getProxy();
              $this->need_proxy = true;
            }
            if ($this->url)
            {
              $this->paginate();
              if ($this->pages)
              {
                $this->printDebug('paginated');
                $this->findNamePages();
                $this->findBasicInfo();
              }
              else 
              {
                $this->saveMeta($this->corp->id,'error','not_paginated');
                $this->printDebug('not paginated');
              }
            }
            else 
            {
              $this->saveMeta($this->corp->id,'error','no_proxy_retrieved');
              $this->printDebug('could not get proxy');
            }
          }
        }
        $this->saveMeta($this->corp->id,'scraped','1');
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
  			//something bad happened, rollback
  			$this->db->rollback();		
        throw $e;
      }    
    }
  }

  //gets and sets proxy text
  private function getProxy()
  {
    $text = null;
    $this->printDebug($this->corp->name);
    $url = "http://searchwww.sec.gov/EDGARFSClient/jsp/EDGAR_Query_Result.jsp?startDoc=1&queryString=&queryForm=DEF+14A&isAdv=1&queryCik=" . $this->corp->sec_cik . "&numResults=10#topAnchor";
    $years = implode('|', $this->years);

	  if ($this->browser->get($url)->responseIsError())
	  {
	    echo "Couldn't get " . $url . "\n";
	    return;
	  }
	  
    $re = '/(' . $years . ')<\/i>(<[^>]*>){2}<a[^\']+\'([^\']+)(?<=\.htm)\'[^>]*>([^<]*)</isu';
    $text = $this->browser->getResponseText();  		    
    //echo $text;  
    $matched = preg_match_all($re,$text,$matches, PREG_SET_ORDER);
    if ($matched > 0)
    {
      foreach($matches as $match)
      {
        if (stristr($match[3],'def14') !== false || stristr($match[4],'def 14') !== false)
        {
          $this->year = $match[1];
          //$this->printDebug($this->year);
          $this->url = $match[3];
          break;
        }
      }
      
      if ($this->browser->get($this->url)->responseIsError())
      {
        echo "Couldn't get " . $this->url . "\n";
        return;
      }
      
      $this->printDebug($this->url);
      $text = $this->browser->getResponseText();
      $text = LsHtml::replaceEntities($text);
      $text = LsString::utf8TransUnaccent($text);
      $this->text = $text;  		    
    }
  }
  
  //find sets of proxy pages with high name frequency
  private function findNamePages($gap = 2)
  {
    $name_matches = array();
    foreach($this->people as $person)
    {
      $re = '/<[^>]*>([^<]*?)(' . $person->getNameRegex() . ')[^<]*<[^>]*>/su';
      if (preg_match_all($re,$this->text,$matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) > 0)
      {
        $this->printDebug('count matches is ' . count($matches));
        foreach ($matches as $match)
        {
          $pos = $match[2][1];
          $page = $this->getPageNumber($pos);
          if ($page && strlen($match[1][0]) < 30)
          {
            $arr = array('page_number' => $page[1], 'page_index' => $page[0], 'pos' => $pos, 'match' => $match, 'id' => $person->id, 'name' => $person->name, 'person' => $person);
            $name_matches[] = $arr;
          }
        }
      }
    }
    if (!count($name_matches)) 
    {
      $this->printDebug('no name matches');
      return null;
    }
    $name_matches = LsArray::multiSort($name_matches,'pos');
    $sets = array();
    $unique = array($name_matches[0]['id']);
    $temp = array($name_matches[0]);
    $min = .7 * count($this->people);
    for ($i = 1; $i < count($name_matches); $i++)
    {
      if ($name_matches[$i]['page_index'] && $name_matches[$i]['page_index'] - $gap <= $name_matches[$i-1]['page_index'])
      {
        $temp[] = $name_matches[$i];
        if (!in_array($name_matches[$i]['id'],$unique))
        {
          $unique[] = $name_matches[$i]['id'];
        }
        //$this->printDebug($name_matches[$i]['name']);
      }
      else
      {
        if (count($unique) > $min)
        {
          $sets[] = $temp;
        }
        $temp = array($name_matches[$i]);
        $unique = array($name_matches[$i]['id']);
      }
      if ($i < count($name_matches) -1)
      {
        if ($name_matches[$i+1]['pos'] < $name_matches[$i]['pos'] + strlen($name_matches[$i]['match'][2][0])) 
        {
          $i++;
        }    
      }
    }
    if (count($unique) > $min)
    {
      $sets[] = $temp;
    }
    $this->sets = $sets;
    foreach($sets as $set)
    {
      //$this->printDebug($set[0]['page_index'] . '->' . $set[count($set)-1]['page_index']);
    }
  }
  
  //pulls directors' ages, bios, images, and year they started as director
  //long and convoluted
  private function findBasicInfo()
  {
    if(!$this->sets) return null;
    $re = '/^([^<]*?<[^>]*>)*?[^<]*?(?<!([\.,$\/]))(\b[2-9]\d\b)(?!((,\s+200\d|199\d)|%|[,\.]\d|[-\s]+([Yy]ears?\s+(with|career)|[Dd]ays?|[Mm]onths?)\b))/su';
    $age_match_sets = array();
    
    //go through the sets of name matches and find age matches for each
    foreach($this->sets as $set)
    {
      $age_matches = array();
      for($i = 0; $i < count($set); $i++)
      {
        $len = ($i == count($set)-1) ? 2000 : $set[$i+1]['pos'] - $set[$i]['pos'];
        if ($len > 100000) 
        {
          continue;
        }
        $str = substr($this->text,$set[$i]['pos'], $len);
        if (preg_match($re,$str,$match))
        {
          $n = preg_match_all('/<(\p{L}+)[^>]*>/s',$match[0], $m, PREG_SET_ORDER);
          $tag = 'empty';
          if ($n > 0) 
          {
             $tag = $m[count($m) -1][1];
          }
          $stripped = LsHtml::stripTags($match[0]);
          if (strlen($stripped) < 2000)
          {
            $age_matches[] = array('ind' => $i, 'age_match' => $match, 'age' => $match[3], 'name_match' => $set[$i], 'num_tags' => $n, 'tag' => $tag, 'len' => strlen($match[0]));
          }
          //$this->printDebug($i . '. ' . $set[$i]['name'] . ' : ' . $match[3] . ' : ' . strlen($match[0]) . ' : ' . $n . ' : ' . $tag);
          //$this->printDebug($set[$i]['match'][1][0]);
        }

        //else $this->printDebug('--');
        //$this->printDebug($set[$i]['match'][1][0]);
      }
      $this->printDebug('count age matches is ' . count($age_matches));
      $age_match_sets[] = $age_matches;
    }
    
    //find the best set (most unique names and ages)
    $max = 0;
    $best = array(array('unique' => array(), 'set' => array()));
    foreach($age_match_sets as $age_matches)
    {
      if (count($age_matches) < 2) continue;
      $unique = array($age_matches[0]['name_match']['id']);
      $temp = array($age_matches[0]);
      for ($i = 1; $i < count($age_matches); $i++)
      {
        if ($age_matches[$i]['ind'] - 4 <= $age_matches[$i-1]['ind'])
        {
          $temp[] = $age_matches[$i];
          if (!in_array($age_matches[$i]['name_match']['id'], $unique))
          {
            $unique[] = $age_matches[$i]['name_match']['id'];
          }
        }
        else
        {
          if (count($unique) > $max)
          {
            $max = count($unique);
            if (count(array_intersect($best[0]['unique'],$unique)) == 0 && count($best[0]['unique']) > 2)
            {
              array_unshift($best,array('unique' => $unique, 'set' => $temp));
            }
            else
            {
              $best = array(array('unique' => $unique, 'set' => $temp));
            }         
          }
          else
          {
            if (count(array_intersect($best[0]['unique'],$unique)) == 0 && count($unique) > 2)
            {
              $best[] = array('unique' => $unique, 'set' => $temp);
            }
          }
          $unique = array($age_matches[$i]['name_match']['id']);
          $temp = array($age_matches[$i]);
        }
      }
      if (count($unique) > $max)
      {
        $max = count($unique);
        if (count(array_intersect($best[0]['unique'],$unique)) == 0)
        {
          array_unshift($best,array('unique' => $unique, 'set' => $temp));
        }
        else
        {
          $best = array(array('unique' => $unique, 'set' => $temp));
        } 
      }
    }
    $best = $best[0]['set'];
    //$this->printDebug('count best is ' . count($best));
    //find the tag all names have in common (if there is one)
    $tag_counts = array();
    foreach ($best as $b)
    {
      if (isset($tag_counts[$b['tag']]))
      {
        $tag_counts[$b['tag']]++;
      }
      else
      {
        $tag_counts[$b['tag']] = 1;
      }
       $this->printDebug($b['ind'] . '. ' . $b['name_match']['name'] . ' : ' . $b['age'] . ' : ' . strlen($b['age_match'][0]) . ' : ' . $b['num_tags'] . ' : ' . $b['tag']);   
    }
    $tag = null;
    foreach($tag_counts as $k => $v)
    {
      if ($v > .8 * count($best))
      { 
        $tag = $k;      
        break;
      }
    }
    $age_set = array();
    if ($tag)
    {
      foreach($best as $b)
      {
        if ($b['tag'] == $tag)
        {
          $age_set[] = $b;
        }
      }
    }
    else
    {
      $age_set = $best;
    }
    $age_set = LsArray::multiSort($age_set,array('name_match','id'));

    //find duplicates and determine the best match out of the pair/set
    $singles = array();
    $doubles = array();
    $num_tags = 0;
    $len = 0;
    for ($i = 0; $i < count($age_set); $i++)
    {
      $double = array($age_set[$i]);
      while ($i < count($age_set) -1 && $double[0]['name_match']['id'] == $age_set[$i+1]['name_match']['id'])
      {
        $double[] = $age_set[$i+1];
        $i ++;
      }
      if (count($double) == 1)
      {
        $singles[] = $age_set[$i];
        $num_tags += $age_set[$i]['num_tags'];
        $len += $age_set[$i]['len'];
      }
      else
      {
        $doubles[] = $double;
      }
    }


    if (count($singles) < 3)
    {
      $unique = array();
      $sets = array(array());
      $age_set = LsArray::multiSort($age_set,array('name_match','pos'));
      foreach($age_set as $a)
      {
        //$this->printDebug($a['name_match']['name'] . ": ");
        if (!in_array($a['name_match']['id'],$unique))
        {
          
          $unique[] = $a['name_match']['id'];
          $sets[count($sets)-1][] = $a;
        }
        else
        {
         
          $unique = array($a['name_match']['id']);
          $sets[] = array($a);
        }
      }
      $age_set = $sets[0];
    }
    else
    {
      $avg_len = $len / count($singles);
      $avg_tags = $num_tags / count($singles);
      //$this->printDebug('len is ' . $avg_len . ' and tags is ' . $avg_tags);
      foreach($doubles as $double)
      {
        $best = null;
        foreach($double as $d)
        {
          $lf = $d['len']/$avg_len;
          $tf = $d['num_tags']/$avg_tags;
          $f = abs(2 - ($lf + $tf));
          if (!$best) 
          {
            $best = $d;
          }
          else 
          {  
            if (abs($avg_tags - $best['num_tags']) > abs($avg_tags - $d['num_tags']))
            {
              $best = $d;          
            }
            else if (abs($avg_tags - $best['num_tags']) == abs($avg_tags - $d['num_tags']) &&
                   abs($avg_len - $best['len']) == abs($avg_len - $d['len']) )
            {
              $best = $d;
            }
          }
        }
        $singles[] = $best;
      }
      $age_set = LsArray::multiSort($singles,array('name_match','pos'));
    }    
    //determine which directors were found, which weren't
    $ids = array();
    foreach($age_set as $a)
    {
      $ids[] = $a['name_match']['id'];
      //$this->printDebug($a['ind'] . '. ' . $a['name_match']['name'] . ' : ' . $a['age'] . ' : ' . strlen($a['age_match'][0]) . ' : ' . $a['num_tags'] . ' : ' . $a['tag']); 
    }
    foreach($this->people as $p)
    {
      if (!in_array($p->id, $ids)) 
      {
        $category = Doctrine::getTable('RelationshipCategory')->findOneByName('Position');
        $relationship = LsDoctrineQuery::create()
          ->from('Relationship r')
          ->where('r.entity1_id = ?', $p->id)
          ->addWhere('r.entity2_id = ?', $this->corp->id)
          ->addWhere('r.category_id = ?', $category->id)
          ->addWhere('r.description1 = ?', 'Director')
          ->fetchOne();
        if ($relationship)
        {
          $relationship->is_current = 0;
          $relationship->save();
        }  
      }
    }
    if (count($age_set) < .5 * count($this->people)) 
    {
      $this->printDebug('not enough names in age set:' . count($age_set) . ' vs. ' . count($this->people));
      return null;
    }
    //figure out which tags surround name/age pairs
    $tag_arr = array('<table' => array(), '<tr' => array(), '<td' => array(), '<div' => array(), '<br' => array(), '<p' => array());
    $tag_arr = array('table' => array(), 'tr' => array(), 'td' => array(), 'div' => array(), 'br' => array(), 'p' => array());
    for ($i = 1; $i < count($age_set)-1; $i++)
    {
      $str = substr($this->text,$age_set[$i-1]['name_match']['pos'],$age_set[$i+1]['name_match']['pos'] - $age_set[$i-1]['name_match']['pos']);
      //$this->printDebug($str);
      foreach ($tag_arr as $tag => &$arr)
      {
        $tag_str = LsHtml::getStringInTag($str,$tag, $age_set[$i]['name_match']['pos'] -$age_set[$i-1]['name_match']['pos']);
        
        if (strlen($tag_str) > 0)
        {
          $arr[] = strlen($tag_str);
          //$this->printDebug($tag_str);
          //echo "\n*****\n";
        }
      }
    }
    arsort($tag_arr);
    //var_dump($tag_arr);
    //$this->printDebug(count($this->people));
    if (count(reset($tag_arr)) == 0) 
    {
      $this->printDebug('problems with enclosing tag detection');
      return null;
    }
    foreach($tag_arr as $tag => $arr)
    {
      $avg = array_sum($arr) / count($arr);
      $splitter = $tag;
      break;
    }
    $tag_counts = array();
    for ($i = 0; $i < count($age_set)-1; $i++)
    {
      $str = substr($this->text,$age_set[$i]['name_match']['pos'],$age_set[$i+1]['name_match']['pos'] - $age_set[$i]['name_match']['pos']);
      str_ireplace('<' . $splitter, ' ', $str, $count);
      $tag_counts[] = $count;
    }
    sort($tag_counts);
    $ct = $tag_counts[0];
    if (!$ct) return null;
    $post_strlen = 0;
    $info_arr = array();
    for ($i = 0; $i < count($age_set); $i++)
    {
      $a = $age_set[$i];
      $matches = LsString::striposMulti($this->text,'</' .$splitter,$ct,$a['name_match']['pos']);
      $end = $matches[count($matches) -1];
      $start = strripos(substr($this->text,0,$a['name_match']['pos']),'<' . $splitter);
      $str = substr($this->text, $start,$end - $start);
      if ($i == count($age_set)-1 && count($matches) > 1)
      {
        $end = $matches[count($matches) -2];
        $str2 = substr($this->text,$start,$end-$start);
        $avg = strlen(implode(' ', $segments))/count($segments);
        if (abs(strlen($str2) - $avg) < abs(strlen($str) - $avg))
        {
          $str = $str2;
        }
      }

      $segments[] = $str;
      //$this->printDebug($str);
      $info = $this->parseSegment($str,$a['name_match']['pos'] - $start, $a['name_match']['pos'] - $start  + strlen($a['name_match']['match'][2][0]));      
      $info = $this->parseBlurb($info, $a);
      
      //looks to see if bio appears aftr the parsed segment
      if ($i < count($age_set) -1)
      {
        $next_start = strripos(substr($this->text,0,$age_set[$i+1]['name_match']['pos']),'<' . $splitter);
        $post_str = substr($this->text, $end, $next_start - $end);
      }
      else
      {
        $avg = $post_strlen / (count($age_set) - 1);
        $post_str = substr($this->text, $end, $avg);
      }
      $post_strlen += strlen($post_str);
      $post_str = LsHtml::replaceFontStyleTags($post_str);
      $person = $a['name_match']['person'];
      $last = LsString::escapeStringForRegex($person->name_last);
      $info['post_blurb'] = '';
      if (preg_match_all('/>([^<]*' . $last . '[^<]*)</isu',$post_str,$matches))
      {
        $post_blurb = implode(' ', $matches[1]);
        $post_blurb = trim(preg_replace('/\s+/s',' ',$post_blurb));
        if (strlen($post_blurb) > 40)
        {
          $info['post_blurb'] = $post_blurb;
        } 
      }

      $info_arr[] = $info;
      //echo "\n\n***\n\n";
    }
    
    $ct = 0;
    $unv_ct = 0;
    foreach ($info_arr as $info)
    {
      if (strlen($info['post_blurb']) > strlen($info['blurb']))
      {
        $ct++;
      }
      if ($info['img'] == null && $info['unverified_img'] != null)
      {
        $unv_ct++;
      }
    }
    
    //if most of the profile segments have images at the end, check to see if they belong to the next profile segment
    if ($unv_ct > count($age_set) - 3)
    {
      for ($i = 0; $i < count($age_set); $i++)
      {
        $len = strripos(substr($this->text,0,$age_set[$i]['name_match']['pos']),'<' . $splitter);   
        $tag_start = strripos(substr($this->text,0,$len),'<img');
        $str = substr($this->text,$tag_start,200); 
        if (preg_match('/^<img[^>]+src=[\'"]([^\'"]+)[\'"]/is',$str,$match) == 1)
        {
          $info['img'] = $match[1];
        }
        else if ($i == 0) break;
      }
    }
    
    for ($i = 0; $i < count($info_arr); $i++)
    {
      if ($ct > .8 * count($age_set)) 
      {
        $info_arr[$i]['blurb'] = $info_arr[$i]['post_blurb'];
        if (!$info_arr[$i]['since'])
        {
          $info_arr[$i]['since'] = $this->getStartDate($info_arr[$i]['blurb']);
        }
      }
      $this->importDirectorInfo($info_arr[$i], $age_set[$i]);
      $this->printDebug("\n***");
    }
    
    //$this->printDebug($splitter);
    //var_dump($tag_counts);
  }
  
  private function parseSegment($segment, $name_start, $name_end)
  {
    $info = array('since' => null, 'blurb' => null, 'img' => null,  'unverified_img' => null);
    $part1 = substr($segment, 0, $name_start);
    $part2 = substr($segment, $name_end);
    //$this->printDebug('segment is :' . $segment);
    //$this->printDebug('part1 is :' . $part1);
    //$this->printDebug('part2 is :' . $part2);
    if (preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"]( alt="([^"]*)")?/is',$part1,$match))
    {
      $info['img'] = $match[1];
    }  
    if (preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"]( alt="([^"]*)")?/is',$part2,$match, PREG_OFFSET_CAPTURE))
    {
      if ($match[1][1] < strlen($segment)/2)
      {
        $info['img'] = $match[1][0];
      }
      else
      {
        $info['unverified_img'] = $match[1][0];
      }
    }
 
    $info['since'] = $this->getStartDate($part2);
    $info['blurb_arr'] = array();
    $info['blurb'] = '';
    $segment = preg_replace('/<\/?(b|i|font)\b[^>]*>/is',' ',$segment);
    $segment = preg_replace('/\s+/s',' ',$segment);
    $avg = 0;
    if (preg_match_all('/>([^<]{5,})/isu', $segment, $matches))
    {
      $arr = $matches[1];
      $new = array();
      foreach($arr as $str)
      {
        $str = preg_replace('/\s+/s',' ',$str);
        $str = trim($str);
        if (preg_match('/[,\.\;\:]$/',$str) == 0)
        {
          $str .= '.';
        }
        $words = explode(' ', $str);

        $new[] = $str;
      }
      if (count($new) > 0)
      {
        $info['blurb_arr'] = $new;
      }
    }
    return $info;
  }
  
  /*
    A director of our company since May 2006. 
    Robert L. Rewey was appointed as a director of Sonic in December 2001. 
    Mr. Urban was appointed to our Board on October 23, 2007.
  */
  private function getStartDate($str)
  {
    $since = null;
    $num_matches = preg_match_all('/\b((19|20)\d\d)\b/',$str, $matches, PREG_SET_ORDER);
    if ($num_matches == 1) 
    {
      $since = $matches[0][1];
    }
    else
    {
      $suffixes = 'Inc|Corp|Company|Incorporated|Corporation';
      $corp_names = array(LsString::escapeStringForRegex(trim(str_replace(array('Inc','Incorporated','Corp','Corporation','Company'),'',$this->corp->name))));

      if ($this->corp->ticker)
      {
        $corp_names[] = LsString::escapeStringForRegex($this->corp->ticker);
      }
      else if ($this->corp->name_nick)
      {      
        $corp_names[] = LsString::escapeStringForRegex($this->corp->name_nick);
      }
      $corp_names = implode('|',$corp_names);
      $corp_names = '(' . $corp_names . ')(\s+(' . $suffixes . ')\.?)?';
            
      $months = 'January|February|March|April|May|June|July|August|September|October|November|December';
      
      $date = '((' . $months . ')\s+(\d\d?\,\s+)?)?((19|20)\d\d)\b';    
      
      $res = array('elected to' => array('/(elected\s+to|appointed\s+to|joined)\s+((the|our)\s+)?((company|corporation|' . $corp_names . ')(\'s)?\s+)?board\s+(of\s+(trustees|directors)\s+)?(in\s+)?' . $date . '/isu', 16));
      
      $res['director of'] = array('/director\s+of\s+(the\s+(company|corporation)|' . $corp_names . ')\s+(in|since)\s+' . $date . '/isu', 10);
      
      $res['director since'] = array('/(director|trustee)\s+since\s+(\w+\s+(of\s+)?)?' . $date . '/isu', 7);
      
      $res['elected a'] = array('/(elected|appointed)\s+(as\s+)?a\s+((member|director)\s+)(of\s+((the|our)\s+)?(company|corporation' . $corp_names . ')\s+)?(in\s+)?' . $date . '/isu',16);
      
      $res['member'] = array('/member\s+of\s+((the|our)\s+)?((company|corporation|' . $corp_names . ')(\'s)?\s+)?board\s+(of\s+(trustees|directors)\s+)?since\s+' . $date . '/isu',14);

      if (preg_match_all('/>(since)?\s*' . $date . '\s*</is',$str, $matches))
      {
        $years = $matches[5];
        //make sure it's start date, not end date
        sort($years);
        $since = $years[0];
      }
      else
      {
        foreach($res as $re)
        {
          if(preg_match($re[0],$str,$match))
          {
            $since = $match[$re[1]];
            if ($since < 2009)
            {
              break;
            }
          }        
        }   
      }
    } 
    return $since;  
  }
  
  private function importDirectorInfo($info, $age_match)
  {
    $id = $age_match['name_match']['id'];
    $person = $age_match['name_match']['person'];
    $this->printDebug($person->name);
    $category = Doctrine::getTable('RelationshipCategory')->findOneByName('Position');
    $relationship = LsDoctrineQuery::create()
      ->from('Relationship r')
      ->where('r.entity1_id = ?', $id)
      ->addWhere('r.entity2_id = ?', $this->corp->id)
      ->addWhere('r.category_id = ?', $category->id)
      ->addWhere('r.description1 = ?', 'Director')
      ->fetchOne();
    if ($info['since'])
    {
      $relationship->start_date = $info['since'] . '-00-00';
      $relationship->save();
      $this->printDebug('Director since: ' . $relationship->start_date);
      $relationship->addReference($this->url, null,array('start_date'), $this->corp->name . ' ' . $this->year . ' Proxy', 'pg ' . $age_match['name_match']['page_number']);
    }

    if ($info['blurb'] != '')
    {
      $info['blurb'] = $this->cleanSummary($info['blurb']);
      if (strlen($info['blurb']) > 3000)
      {
        $info['blurb'] = substr($info['blurb'],0,3000);
      }
      if ($person->summary == null)
      {
        $person->summary = $info['blurb'];
        $person->save();
        $person->addReference($this->url, $info['blurb'],array('summary'), $this->corp->name . ' ' . $this->year . ' Proxy', 'pg ' . $age_match['name_match']['page_number']);
        $this->printDebug('Summary: ' . $person->summary);
      }
      else
      {
        $person->addReference($this->url, $info['blurb'],array('summary'), $this->corp->name . ' ' . $this->year . ' Proxy', 'pg ' . $age_match['name_match']['page_number']);
      }
    }
    else $this->printDebug('no blurb');

    if ($person->start_date == null)
    {
      $person->start_date = $this->year - $age_match['age'] . '-00-00';
      $person->save();
      $person->addReference($this->url, $info['blurb'],array('start_date'), $this->corp->name . ' ' . $this->year . ' Proxy', 'pg ' . $age_match['name_match']['page_number']);
      $this->printDebug('Birthdate: ' . $person->start_date);
    }
    if (isset($info['img']))
    {
      $url = substr($this->url,0,strrpos($this->url, '/') + 1) . $info['img'];
      if ($fileName = ImageTable::createFiles($url, $info['img']))
      {
          //insert image record
          $image = new Image;
          $image->filename = $fileName;
          $image->entity_id = $person->id;
          $image->title = $person->name;
          $image->caption = 'From ' . $this->corp->name . '\'s proxy filing.';
          $image->is_featured = true;
          $image->is_free = false;
          $image->url = $url;
          
          $q = LsDoctrineQuery::create()
                ->from('Image i')
                ->where('i.entity_id = ?', $person->id)
                ->addWhere('i.title =?', $person->name)
                ->addWhere('i.caption =?', $image->caption);
           
          if (count($q->execute()) == 0)
          {                       
            $image->save();
            $image->addReference($this->url, null, array('filename'), $this->corp->name . ' ' . $this->year . ' Proxy', 'pg ' . $age_match['name_match']['page_number']);
            $this->printDebug("Imported image: " . $image->filename);
          }
       }
    }
  }
  
  private function parseBlurb($info, $age_match)
  {
    if (count($info['blurb_arr']) == 0) return $info;
    
    $id = $age_match['name_match']['id'];
    $person = Doctrine::getTable('Entity')->find($id);
    $name_words = explode(' ',$person->name);
    $skip = array('director','directors','since','board',$info['since'],$age_match['age'],'age');
    $skip = array_merge($skip,$name_words);
    $new = array();
    foreach($info['blurb_arr'] as $b)
    {
      $n = $b;
      foreach ($skip as $s)
      {  
        $s = LsString::escapeStringForRegex($s);
        $n = preg_replace('/\b' . $s . '\b/isu','',$n);
      }
      $n = preg_replace('/\b\d\d\d\d\b/','',$n);
      $n = LsString::stripNonAlpha($n,' ');
      $words = preg_split('/\s+/s',$n);
      if (count($words) > 3)
      {
        $new[] = $b;
      }
    } 
    if (count($new) > 0)
    {
      $blurb = implode(' ', $new);
      $blurb_parts = preg_split('/\s+/s',$blurb);
      $skip = array_merge($skip, array('executive','vice','president','chief','chairman','of','the'));
      $n = $blurb;
      foreach($skip as $s)
      {
        $s = LsString::escapeStringForRegex($s);
        $n = preg_replace('/\b' . $s . '\b/isu','',$n);
      }
      $n = preg_replace('/\b\d\d\d\d\b/','',$n);
      $n = LsString::stripNonAlpha($n,' ');
      $words = preg_split('/\s+/s',$n);
      if (count($words) > 4)
      {
        $info['blurb'] = $blurb;
      }
    }
    return $info;
  }
  
  private function cleanSummary($str)
  {
    $str = preg_replace('/\s+(\.|\,|\;|\:)/is',"\\1",$str);
    if (preg_match('/^([^\d]*)\b\d\d\)?\.\s+\p{Lu}/su',$str,$match))
    {
      //$this->printDebug('matched');
      $splat = preg_split('/\s+/',trim($match[1]),-1,PREG_SPLIT_NO_EMPTY);
      if (count ($splat) < 6)
      {
        $str = substr($str,strlen($match[0])-1);
      }
    }
    $str = preg_replace('/\,\s+(age)?\s*\d\d((\.)|\,)/is',"\\3", $str);
    $str = preg_replace('/\s+/',' ',$str);
    $str = trim($str);
    return $str;
  }
 
 
  public function getFamilyInfo()
  {
    
  }  
  
  //finds positions of all page numbers.  $this->pages is actually array of page numbers and positions
  private function paginate()
	{
	  $res = array();
	  $res[] = array('/<[^>]*>\s*(((\p{L}){0,2}\-)?(\d{1,2}|[ivx]{1,3}))\s*(<[^>]*>[^<]*){0,8}<[^>]*(?<=page-break-before)/isu',1);
	  $res[] = array('/(((\p{L}){0,2}\-)?(\d{1,2}|[ivx]{1,3}))\s*(<[^>]*>[^<]*){0,8}<PAGE>/isu',1);

    $res[] = array('/page\-break\-before\:[^>]*>\s*(<[^>]*>\s*){0,10}(table\s+of\s+contents\s*)?(<[^>]*>\s*){0,10}(((\p{L}){0,2}\-)?(\d{1,2}|[ivx]{1,3}))/isu',5);
    $res[] = array('/page\-break\-after\:[^>]*>\s*(<[^>]*>\s*){0,10}(table\s+of\s+contents\s*)?(<[^>]*>\s*){0,10}(\-\s*)?(((\p{L}){0,2}\-)?(\d{1,2}|[ivx]{1,3}))/isu',6);
	  foreach ($res as $re)
	  {
	    $matched = preg_match_all($re[0],$this->text,$matches,PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
	    $pages = array();
	    if ($matched > 5) 
	    {
	      foreach ($matches as $match)
	      {
	        $pages[] = array($match[$re[1]][0],$match[$re[1]][1]);
	      }
	      array_unshift($pages,array('0',0));
	      $this->pages = $pages;
        break;
	    }
    }
	}
	
	//takes position in string of character, returns page index and number
	private function getPageNumber($x)
	{
	  $ret = null;
	  for($i = 1; $i < count($this->pages); $i++)
	  {
	    if ($x < $this->pages[$i][1] && $x > $this->pages[$i-1][1])
	    {
	      $ret = array($i, $this->pages[$i][0]);
        break;
	    }	  
	  }
	  return $ret;
	}


}

//develop match class
//Mr. McCain, 72 ...
