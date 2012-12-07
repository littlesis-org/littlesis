<?php

abstract class ArticleScraper extends Scraper
{
  protected $articles = array();
  protected $sourceName = null;
  protected $requiresLogin = true;
  protected $loginUrl = null;
  protected $loginParams = array();
  protected $loginSuccessRegexp = null;
  protected $saveToDatabase = false;
  protected $googleSearchUrl = 'http://news.google.com/news/search';
  protected $googleSearchParams = array(
    'pz' => '1',
    'cf' => 'all',
    'ned' => 'us',
    'hl' => 'en',
    'as_q' => '',
    'as_epq' => '',
    'as_oq' => '',
    'as_eq' => '',
    'as_scoring' => 'n',
    'btnG' => 'Search',
    'as_drrb' => 'q',
    'as_qdr' => 'd',
    'as_minm' => '10',
    'as_mind' => '6',
    'as_maxm' => '11',
    'as_maxd' => '5',
    'as_nsrc' => '',
    'as_nloc' => '',
    'geo' => '',
    'as_author' => '',
    'as_occt' => 'any',
    'start' => ''
  );
  protected $googleRequestHeaders = array(
    'User-Agent' => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20061205 Iceweasel/2.0.0.1 (Debian-2.0.0.1+dfsg-2)'
  );
  protected $articleRegexp = '#<h2 class="title">[^<]*?<a .*?href="([^"]+)"[^>]*>([^<]+)</a>.*?<div class="sub-title">(.*?)</div>#i';
  protected $articleList = array();


  abstract protected function init();


  public function setUsername($username)
  {
    $this->username = $username;
  }


  public function setPassword($password)
  {
    $this->password = $password;
  }


  public function setSaveToDatabase($bool)
  {
    $this->saveToDatabase = $bool;
  }


  public function getArticles()
  {
    return $this->articles;
  }


  protected function execute()
  {
    $this->init();

    if ($this->requiresLogin)
    {
      $this->login();
    }
    
    $this->scrapeArticleList();
    $this->filterArticles();

    $this->scrapeArticles();

    if ($this->saveToDatabase)
    {
      $this->saveToDatabase();
    }    
  }


  protected function login()
  {
    if (!$this->username || !$this->password)
    {
      throw new Exception("Can't login to " . $this->sourceName . ": username and password must be set");
    }

    $this->printDebug("Logging in to " . $this->sourceName . "...");

		if ($this->cookieBrowser->post($this->loginUrl, $this->loginParams)->responseIsError())
		{
			throw new Exception("Couldnt post to " . $this->sourceName . " login: " . $this->loginUrl);
    }

    $text = $this->cookieBrowser->getResponseText();
  
    if (!preg_match($this->loginSuccessRegexp, $text))
    {
      print_r($this->cookieBrowser->getResponseText());
      throw new Exception($this->sourceName . " login failed");
    }
	}  


	protected function scrapeArticleList($page=1, $allPages=true)
	{
    $params = $this->googleSearchParams;
    $params['start'] = ($page - 1) * 10;
    $params['as_nsrc'] = strtolower($this->sourceName);
    $url = $this->googleSearchUrl . '?' . http_build_query($params);

    $this->printDebug("Getting news articles from Google: " . $url);
    
		if ($this->browser->get($url, null, $this->googleRequestHeaders)->responseIsError())
		{
			print_r($this->browser->getResponseCode());
			print_r($this->browser->getResponseHeaders());
			print_r($this->browser->getResponseText());
			throw new Exception("Couldn't get " . $this->sourceName . " articles: " . $url);
    }

    $text = $this->browser->getResponseText();		

    if (preg_match_all($this->articleRegexp, $text, $matches, PREG_SET_ORDER))
    {
      foreach ($matches as $match)
      {
        //index by url in order to avoid duplicates
        $this->articleList[$match[1]] = array(
          'title' => $match[2],
          'subtitle' => $match[3]
        );
      }
    }

    if ($allPages && preg_match('#<span>Next</span>#i', $text) && count($this->articleList) <= $this->limit)
    {
      $this->printDebug(count($matches) . " found on page " . $page . "; continuing to next page...");

      $this->scrapeArticleList($page + 1);
    }

		array_reverse($this->articles);
  }


  protected function getArticleSourceId()
  {
    return LsDoctrineQuery::create()
      ->select('s.id')
      ->from('ArticleSource s')
      ->where('s.name = ?', $this->sourceName)
      ->fetch(PDO::FETCH_COLUMN);
  }
  
  
  protected function cleanArticleUrl($url)
  {
    return $url;  
  }

  
  protected function cleanArticleTitle($title)
  {
    return $title;  
  }  
  
  
  protected function saveToDatabase()
  {
    $this->printDebug("Saving articles to database...");

    foreach ($this->articles as $article)
    {
      //make sure it isn't a duplicate
      $q = LsDoctrineQuery::create()
        ->from('Article a')
        ->where('a.url = ?', $article->url);

      if ($q->fetch())
      {
        $this->printDebug("Skipping duplicate article: " . $article->url);
      }
      else
      {
        $article->save();
      }
    }
  }
  
  
  protected function scrapeArticleBody($url)
  {
	  if ($this->cookieBrowser->get($url)->responseIsError())
	  {
	    throw new Exception("Couldn't get NYT article: " . $url);
	  }

	  return trim($this->cookieBrowser->getResponseText());
  }
  
  
  abstract protected function getArticleDateFromBody($body);
  
  
  abstract protected function filterArticles();
  
  
  protected function scrapeArticles()
  {
    foreach ($this->articleList as $url => $ary)
    {
      $article = new Article;
      $article->url = $this->cleanArticleUrl($url);
      $article->title = $this->cleanArticleTitle($ary['title']);
      $article->source_id = $this->getArticleSourceId();

      //get authors if listed
      if (preg_match_all('#q=author%3A%22([^%]+)%22#i', $ary['subtitle'], $authorMatches, PREG_PATTERN_ORDER))
      {
        $article->authors = urldecode(implode(',', $authorMatches[1]));
      }
              
      try
      {
        $article->body = $this->scrapeArticleBody($article->url);
      }
      catch (Exception $e)
      {    
        $this->printDebug("*** Error scraping article: " . $e->getMessage() . " (" . $url . ")");
        continue;
      }      

      $article->published_at = $this->getArticleDateFromBody($article->body);
      $article->reviewed_at = NULL;
      $article->found_at = LsDate::getCurrentDateTime();
   
      $this->articles[] = $article;

      $this->printDebug("+ Scraped article '" . $article->title . "'");
    }  
  }
}