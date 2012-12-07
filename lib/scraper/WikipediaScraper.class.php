<?php

//old version of Wikipedia scraper
class WikipediaScraper extends Scraper
{
  
  protected $_baseUrl = 'http://en.wikipedia.org/wiki/';
  protected $_url = null;
  protected $_query = '';
  protected $_limit = 5;
  protected $_results = null;
  protected $_numResults = null;
  protected $_page = null;
  protected $_wikiTables = null;
  

  private function setPage($page)
  {
    $this->_page = $page;  
  }
  
  public function getPage()
  {
    return $this->_page;
  }
  
  public function setQuery($query)
  {
    $query = urlencode(trim($query));
    $query = str_replace('+','_',$query);
    $this->_query = $query;
  }
  
  public function setLimit($lim)
  {
    $this->_limit = $lim;
  }  
  
  public function setUrl($url)
  {
    $this->_url = $url;
  }
  
  public function setBaseUrl($base_url)
  {
    $this->_baseUrl = $base_url;
  }
  
  private function setNumResults($num)
  {
    $this->_numResults = $num;
  }
  
  public function getNumResults()
  {
    return $this->_numResults;
  }  
  
  public function getResults()
  {
    return $this->_results;
  }
  public function execute()
  {
    if (!$this->_query && !$this->_url)
    {
      return null;
    }
    if (!$this->_url)
    {
      $this->_url = $this->_baseUrl . $this->_query; 
    }    
    $this->browser->get($this->_url);
    $page = $this->browser->getResponseText();
    //$person = Doctrine::getTable('Entity')->find(1814);
    $this->_page = $page;
  }


}


/*

    $wikiPage = new WikipediaPersonPage($page, $person);
    $wikiPage->setAll();
    //var_dump($wikiPage->getTitle());
    //var_dump($wikiPage->getTableOfContents());
    //var_dump($wikiPage->getImages());
    //var_dump($wikiPage->getWikiTables());
    $paragraphs = $wikiPage->getParagraphs();
    foreach($paragraphs as $p)
    {
      $p = LsString::spacesToSpace(LsHtml::replaceEntities(LsHtml::stripTags($p,'')));
      $this->printDebug($p . "\n");
    }
    if ($birthdate = $wikiPage->getBirthDate())
    {
      $this->printDebug($birthdate);
    }
    
    if ($birthdate = $wikiPage->getSchoolInfo())
    {
      $this->printDebug($birthdate);
    }
    else echo 'oops';

    //var_dump($wikiPage->getContent());
    
    //var_dump($wikiPage->getContent());
    //var_dump($wikiPage->get());
    //var_dump($wikiPage->getTitle());
    
*/

class WikipediaPage
{

  protected $_page = null;
  protected $_content = null;
  protected $_images = null;
  protected $_title = null;
  protected $_sections;
  protected $_tableOfContents;
  protected $_wikiTables;
  protected $_paragraphs;
  protected $_cleanParagraphs;
  protected $_cleanText;
  protected $_logos;
  protected $_responseStatus;
  const DISAMBIGUATION = 2;
  const SEARCH_PAGE = 0;
  const CONTENT_PAGE = 1;
  

  public function __construct($str)
  {
    $this->_page = $str;
    $this->setResponseStatus();
  }

  public function setAll()
  {
    $this->setStatus();
    $this->setTitle();
    $this->setImages();  
    $this->setTableOfContents();
    $this->setWikiTables();
    $this->setParagraphs();
  }  

  public function getContent()
  {
    return $this->_content;
  }
  
  public function setContent()
  {
    $start = strpos($this->_page, '<!-- start content -->');
    $end = strpos($this->_page, '<!-- end content -->');
    if ($start !== false && $end != false && $end > $start)
    {
      $content = substr($this->_page, $start, $end - $start);
      $this->_content = $content;
    }
  }
  
  public function setResponseStatus()
  {
    if (strstr($this->_page, 'Wikipedia:Disambiguation'))
    {
      $this->_responseStatus = self::DISAMBIGUATION;
    }
    else if (strstr($this->_page, 'Wikipedia:Requested_articles'))
    {
      $this->_responseStatus = self::SEARCH_PAGE;
    }
    else
    {
      $this->_responseStatus = self::CONTENT_PAGE;
    }
  }
  
  public function getResponseStatus()
  {
    return $this->_responseStatus;
  }
  
  public function getTitle()
  {
    return $this->_title;
  }
  
  public function setTitle()
  {
    if (preg_match('/<h1[^>]*>([^<]*)</isu',$this->_page, $match))
    {
      $this->_title = $match[1];
    }
  }  
  
  public function getImages()
  {
    return $this->_images;
  }

  public function setImages()
  {
    $images = array();
    if (preg_match_all('/<img\s+alt\="([^"]*)"\s+src\="([^"]+)"[^>]*>/isu',$this->_content,$matches, PREG_SET_ORDER))
    {
      foreach($matches as $match)
      {
        $images[] = array('url' => $match[2], 'name' => $match[1]);
      }
    }
    $this->_images = $images;
  }
  
  public function getTableOfContents()
  {
    return $this->_tableOfContents;  
  }
  
  public function setTableOfContents()
  {
    $re = '/<a\s+href\="#([^"]*)"><span\s+class="tocnumber">([^<]*)<\/span>\s*<span\s+class="toctext">([^<]*)</';
    $table = array();
    if (preg_match_all($re,$this->_content,$matches,PREG_SET_ORDER))
    {
      foreach($matches as $match)
      {
        $table[] = array('anchor' => $match[1], 'number' => $match[2], 'text' => $match[3]);
      }
    }
    $this->_tableOfContents = $table;
  }
  
  public function getWikiTables()
  {
    return $this->_wikiTables;
  }
  
  public function setWikiTables()
  {
    $wikiTables = array();
    if (preg_match_all('/<table\s+class\="wikitable"[^>]*>(.*?)<\/table/isu',$this->_content,$matches))
    {
      $table_count =1;
      foreach($matches[1] as $table_match)
      {
        $table = array();
        if (preg_match_all('/<tr[^>]*>(.*?)<\/tr/isu',$table_match,$row_matches))
        {
          $row_count = 1;
          foreach($row_matches[1] as $row_match)
          {
            $row = array();
            if (preg_match_all('/<td[^>]*>(.*?)<\/td/isu',$row_match,$cell_matches))
            {
              $cell_count = 1;
              foreach($cell_matches[1] as $cell_match)
              {
                $cell = array();
                $cell['str'] = LsString::spacesToSpace(LsHtml::replaceEntities(LsHtml::stripTags($cell_match)));
                $cell['links'] = LsHtml::matchLinks($cell_match);
                $row['cell' . $cell_count] = $cell;
                $cell_count++;
              }
            }
            $table['row' . $row_count] = $row;  
            $row_count++;
          }
        }
        $wikiTables['table' . $table_count] = $table;
        $table_count++;
      }
    }
    $this->_wikiTables = $wikiTables; 
  }
  
  public function setParagraphs()
  {
    if (preg_match_all('/<p[^>]*>(.*?)<\/p/isu',$this->_content,$matches))
    {
      echo 'ok';
      $this->_paragraphs = $matches[1];
      $this->_cleanParagraphs = array();
      foreach ($matches[1] as $m)
      {
        $this->_cleanParagraphs[] =LsString::spacesToSpace(LsHtml::replaceEntities(LsHtml::stripTags($m, '')));
      }    
      $this->_cleanText = implode("\n\n",$this->cleanParagraphs);
    }
  }
  
  public function getParagraphs()
  {
    return $this->_paragraphs;  
  }
  
  public function getCleanFirstParagraph() //'/^http.*(Wikinews|Wikiversity|Wikisource|Wikiquote|Wikibooks|Wiktionary|Commons-logo)/i'
  {
    if ($this->_paragraphs)
    {
      return null;
    }
    $first = $this->_paragraphs[0];
    $first = LsString::spacesToSpace(LsHtml::replaceEntities(LsHtml::stripTags($first)));
    return $first;
  }
  
  public function getCleanParagraphs()
  {
    return $this->_cleanParagraphs;
  }
  
}

class WikipediaListPage extends WikipediaPage
{


}

class WikipediaPersonPage extends WikipediaPage
{

  protected $_person = null;

  public function __construct($str, Entity $e)
  {
    $this->_page = $str;
    $this->_person = $e;
  }
  
  public function getBirthDate()
  {
    if (preg_match('/born\s+(\w+\s+(\d\d?)\,\s+((20|19|18)\d\d))/isu',$this->_cleanParagraphs[0], $match))
    {
      return $match[1];
    } 
  }
  
  public function getSchoolInfo()
  {
    $res = array();
    $res[] = '/graduated.*?from.*?<a.*?>.*?<\/a/isu';
    $res[] = '/received.*?degree.*?<a.*?>.*?<\/a/isu';
    foreach($res as $re)
    {
      if (preg_match_all($re,$this->_content,$matches))
      {
        var_dump($matches[0]);
      }
    }
  
  }

}


class WikipediaOrgPage extends WikipediaPage
{

  protected $_org = null;

  public function __construct($str, Entity $e)
  {
    $this->_page = $str;
    $this->_org = $e;
  }
  
  public function setLogos()
  {
    if (!$this->_images)
    {
      $this->setImages();
    }
    $logos = array();
    foreach($this->_images as $image)
    {
      if (preg_match('/^http.*(Wikinews|Wikiversity|Wikisource|Wikiquote|Wikibooks|Wiktionary|Commons-logo)/i',$image['url']))
      {
        continue;
      }
      if (strstr($image['url'],'logo') || strstr($image['name'],'logo'))
      {
        $logos[] = $image;
      }
    }
    $this->_logos = $logos;
  }
  
  public function getLogos()
  {
    return $this->_logos;
  }
  
  public function getInfoTable()
  {
  
  
  }
  
}


