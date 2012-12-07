<?php

class ArticleIndexer
{
  protected $article;
  protected $calais;
  protected $personNames = array();
  protected $orgNames = array();
  protected $allNames = array();
  protected $entities = array();


  public function __construct(Article $article=null)
  {
    $this->article = $article;
    $this->calais = new LsCalaisRequest;

    $defaultHeaders = array(
      'User-Agent' => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20061205 Iceweasel/2.0.0.1 (Debian-2.0.0.1+dfsg-2)'
    );
    $this->browser = new sfWebBrowser($defaultHeaders);
  }


  public function setArticle(Article $article)
  {
    $this->article = $article;
    $this->clearMatches();
  }
  

  public function clearMatches()
  {
    $this->personNames = array();
    $this->orgNames = array();
    $this->allNames = array();
    $this->entities = array();
  }

  
  public function getEntities()
  {
    return $this->entities;
  }
  
  
  public function getEntityNames()
  {
    return array_keys($this->entities);
  }
  
  
  public function getEntityIdsByName($name)
  {
    return isset($this->entities[$name]) ? $this->entities[$name] : array();
  }
  
  
  public function getEntityIds()
  {
    $ids = array();

    foreach ($this->entities as $name => $entityIds)
    {
      $ids = array_merge($ids, $entityIds);
    }
    
    return array_unique($ids);
  }


	public function indexArticle()
	{
    if (!$this->article)
    {
      throw new Exception("Can't index article; no article set");
    }    

    $this->extractCalaisNames($this->article);    

    //match persons    
    foreach ($this->personNames as $name)
    {
      if (count($ids = $this->getEntityIdsByPersonName($name)))
      {
        $this->entities[$name] = $ids;
      }
    }
    
    //match orgs    
    foreach ($this->orgNames as $name)
    {
      if (count($ids = $this->getEntityIdsByOrgName($name)))
      {
        $this->entities[$name] = $ids;
      }
    }    
	}


  public function extractCalaisNames()
  {
    $this->calais->setContent($this->article->body);
    $this->calais->request();

    //extract entities
    $names = array(
      'Company' => array(),
      'Organization' => array(),
      'Person' => array()      
    );
    
    foreach (array_keys($names) as $key)
    {
      $names[$key] = $this->extractCalaisEntities($key);
    }

    $this->personNames = array_unique($names['Person']);
    $this->orgNames = array_unique(array_merge($names['Company'], $names['Organization']));
    $this->allNames = array_merge($this->personNames, $this->orgNames);
	}


	protected function extractCalaisEntities($key)
	{
    if (!$this->calais->getResponse())
    {
      throw new Exception("Can't extract entities; no Calais response set");
    }

    $names = array();
    $pattern = '#(\r|\n)' . $key . ': (.*?)(\r|\n)#im';

	  if (preg_match($pattern, $this->calais->getResponse(), $match))
	  {
	    $names = explode(',', substr($match[2], 0, -1));
	  }
	  
	  return $names;
	}


  protected function getEntityIdsByPersonName($name)
  {  
    //clean up name
    $name = self::cleanName($name, 'Person');
    
    //parse name
    $person = PersonTable::parseFlatName($name, null, true);

    if (!$person['name_first'] || !$person['name_last'])
    {
      return array();
    }    

    //look for matching entities
    return PersonTable::getEntityMatchingQuery($person)
      ->select('e.id')
      ->fetchAll(PDO::FETCH_COLUMN);
  }
	
	
	protected function getEntityIdsByOrgName($name)
	{
    //clean up name
    $name = self::cleanName($name, 'Org');
    
    //search sphinx for name
    $result = EntityTable::getSphinxHits('@(name,aliases) ' . $name . '@primary_ext Org', $page=1, $num=10);

    if ($result['total_found'] == 0 || !isset($result['matches']))
    {
      return array();
    }

    $ids = array_keys($result['matches']);
    $matches = array();

    $q = LsDoctrineQuery::create()
      ->select('a.entity_id, a.name')
      ->from('Alias a')
      ->whereIn('a.entity_id', $ids);
    
    foreach ($q->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP) as $id => $aliases)
    {
      foreach ($aliases as $alias)
      {
        if (self::areSameName($name, $alias, 'Org'))
        {
          $matches[] = $id;          
          break;
        }
      }
    }
    
    return $matches;
	}
	
	
	static function areSameName($name1, $name2, $ext)
	{	  
	  $name1 = self::cleanName($name1, $ext);
	  $name2 = self::cleanName($name2, $ext);

    return strtolower(trim($name1)) == strtolower(trim($name2));
  }

	  
	static function cleanName($name, $ext, $words=array())
  {
	  $name = str_replace(array('.', ','), '', $name);

    if ($ext == 'Person')
    {
      $junkWords = $words ? $words : array(
        'Sir',
        'Hon',
        'Honorable',
        'Gen',
        'Dr',
        'Lt'
      );      
    }
    elseif ($ext = 'Org')
    {
      $junkWords = $words ? $words : LsLanguage::$businessAbbreviations;
    }

    foreach ($junkWords as $word)
    {
      $name = preg_replace('/(^| )' . $word . '($| )/i', '', $name);
    }
    
    return $name;
	}
}