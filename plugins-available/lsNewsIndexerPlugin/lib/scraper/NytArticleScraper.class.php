<?php

class NytArticleScraper extends ArticleScraper
{
  protected $sourceName = 'New York Times';
  protected $loginUrl = 'http://www.nytimes.com/auth/login';


  protected function init()
  {
    $this->username = sfConfig::get('app_nytimes_username');
    $this->password = sfConfig::get('app_nytimes_password');
    
    $this->loginParams = array(
			'is_continue' => 'true',
			'URL' => '',
			'OQ' => '',
			'OP' => '',
			'USERID' => $this->username,
			'PASSWORD' => $this->password,
			'Submit2' => 'Log In'
		);
		
		$this->loginSuccessRegexp = '#<a href="http://www.nytimes.com/mem/profile.html">' . $this->username . '</a>#i';
  }

  
  protected function cleanArticleUrl($url)
  {
    //change article url to print-friendly
    $urlParts = explode('?', $url);

    return $urlParts[0] . '?pagewanted=print';
  }
  
  
  protected function getArticleDateFromBody($body)
  {
    //look for date in meta
    if (preg_match('#name="DISPLAYDATE" content="([^"]+)"#i', $body, $match))
    {
      return date('Y-m-d', strtotime($match[1]));
    }

    //look for date in blog markup
    if (preg_match('#<span class="date">([^<]+)<em#i', $body, $match))
    {
      return date('Y-m-d', strtotime($match[1]));
    }

    return null;
  }
  
  
  protected function filterArticles()
  {
    $this->printDebug("Filtering articles...");

    $keywordsToKeep = array(
      'business', 'politics', 'economy', 'technology', '/us/', 'dealbook', 'bits', 'thecaucus',
      'executivesuite', 'mediadecoder', 'norris', 'prescriptions'
    );
    $keepPattern = '#(' . implode('|', $keywordsToKeep) . ')#i';

    $keywordsToSkip = array(
      'aponline', 'reuters'
    );
    $skipPattern = '#(' . implode('|', $keywordsToSkip) . ')#i';

    foreach (array_keys($this->articleList) as $url)
    {
      if (preg_match($keepPattern, $url) && !preg_match($skipPattern, $url))
      {
        $this->printDebug("+ Keeping article: " . $url);
      }
      else
      {
        unset($this->articleList[$url]);
      }
    }
  }
}