<?php

abstract class Scraper
{
  protected 
    $browser = null,
    $cookieBrowser = null,
    $defaultHeaders = null,
    $logDir = null,
    $logFile = null,
    $testMode = null,
    $debugMode = null,
    $timer = null,
    $db = null,
    $conn = array(),    
    $beepEnabled = true,
    $offset = 0,
    $limit = null,
    $show_time = true,
    $testMeta = array(),
    $appConfiguration = null,
    $user;
    
    
  public function __construct($testMode=false, $debugMode=false, $configuration=null, $user=null, $browserTimeout=30)
  {  
    $this->appConfiguration = $configuration;

    //initialize database manager with configuration
    $doctrineManager = Doctrine_Manager::getInstance();
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);


    //set db array
    foreach ($databaseManager->getNames() as $name)
    {
      $this->conn[$name] = $doctrineManager->openConnection($databaseManager->getDatabase($name)->getParameter('dsn'), $name);
    }
    
    
    //set db
    $doctrineManager->setCurrentConnection('main');
    $this->db = $doctrineManager->getCurrentConnection();
    
    
    //logging (DISABLED UNTIL NOTICES CAN BE REMOVED)
    /*
    require_once 'Log.php';
    $this->logDir = sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.'log'.DIRECTORY_SEPARATOR.'scraper'.DIRECTORY_SEPARATOR;
    $this->logFile = get_class($this);
    $this->logger = &Log::singleton('file', $this->logDir . $this->logFile, 'SCRAPER');
    */


    //browser
    $this->defaultHeaders = array(
      'User-Agent' => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20061205 Iceweasel/2.0.0.1 (Debian-2.0.0.1+dfsg-2)'
    );
    $this->browser = new sfWebBrowser($this->defaultHeaders, 'sfCurlAdapter', array('Timeout' => $browserTimeout));
    $this->cookieBrowser = new sfWebBrowser($this->defaultHeaders, 'sfCurlAdapter', array('cookies' => true, 'Timeout' => $browserTimeout));


    //modes
    $this->testMode = $testMode;
    $this->debugMode = $debugMode;
    
    
    //meta
    if ($this->testMode)
    {
      $this->testMode = false;
      $this->testMeta = $this->getAllMeta();
      $this->testMode = true;
    }
    
    
    //login as scraper user
    if (!$user)
    {
      $context = sfContext::createInstance($configuration);
      $user = $context->getUser();
      $user->setAttribute('user_id', sfGuardUserTable::SCRAPER_USER_ID, 'sfGuardSecurityUser');
    }
    
    if (!$user->isAuthenticated())
    {
      $user->setAuthenticated(true);
      $user->clearCredentials();
    }

    $this->user = $user->getGuardUser();
    
  }

  public function setDefaultHeaders($ary)
  {
    $this->defaultHeaders = $ary;
  }
  
  public function setDefaultHeader($header, $value)
  {
    $this->defaultHeaders[$header] = $value;
  }
  
  public function beginTimer()
  {
    $this->timer = sfTimerManager::getTimer('Scraper');
  }
  
  public function stopTimer()
  {
    $this->timer->addTime();  
  }
  
  public function printTime()
  {
    echo "\n**********************************\n";
    echo "DURATION: " . $this->timer->getElapsedTime() . "\n\n";
  }
  
  abstract protected function execute();
  
  final public function run()
  {
    try
    {
        
      $this->beginTimer();
  
      $this->execute();
  
      $this->stopTimer();
      if ($this->show_time == true)
      {
        $this->printTime();
      }
    }
    catch (Exception $e)
    {
      $this->logException($e);
      throw $e;
    }
    
    if ($this->beepEnabled)
    {
      echo "\x07";
    }
  }
  
  final protected function logException(Exception $e)
  {
    //$this->logger->log('EXCEPTION: Line ' . $e->getLine() . ' of ' . $e->getFile() . ': ' . $e->getMessage());
  }
  
  final protected function printException($e)
  {
    echo "****************************\n";
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "****************************\n";
  }
  
  final protected function printBrowserResponse(sfWebBrowser $b, $url, $printText=false)
  {
    echo "****************************\n";
    echo "COULDN'T GET REMOTE LOCATION: " . $url . "\n";
    echo "Response code: " . $b->getResponseCode() . "\n";
    echo "Response headers: ";
    print_r($b->getResponseHeaders());

    if ($printText)
    {
      echo "Response text: \n";
      echo $b->getResponseText();
    }

    echo "****************************\n";
    echo "\n";
  }
  
  final public function enableBeep()
  {
    $this->beepEnabled = true;
  }
  
  final public function disableBeep()
  {
    $this->beepEnabled = false;
  }

  protected function printDebug($str, $override=false)
  {
    if ($this->debugMode || $override)
    {
      echo $str . "\n";
    }
    
    if ($this->user->id == 1)
    {
      flush();
    }
  }

  public function setOffset($offset)
  {
    $this->offset = $offset;
  }
  
  public function setLimit($limit)
  {
    $this->limit = $limit;
  }
  
  public function setShowTime($bool)
  {
    $this->show_time = $bool;
  }
    
  public function getName()
  {
    return get_class($this);
  }
    
    
  public function saveMeta($namespace, $predicate, $value)
  {
    //use class variable if in testMode
    if ($this->testMode)
    {
      if (isset($this->testMeta[$namespace]))
      {
        $this->testMeta[$namespace][$predicate] = $value;
      }
      else
      {
        $this->testMeta[$namespace] = array($predicate => $value);
      }
      
      return;
    }


    if (!$meta = $this->_getMeta($namespace, $predicate))
    {
      $meta = new ScraperMeta;
      $meta->scraper = $this->getName();
      $meta->namespace = $namespace;
      $meta->predicate = $predicate;
    }
    
    $meta->value = $value;
    $meta->save();
  }
  
  
  public function getMeta($namespace, $predicate)
  {
    //use class variable if in testMode
    if ($this->testMode)
    {
      if (isset($this->testMeta[$namespace]))
      {
        if (isset($this->testMeta[$namespace][$predicate]))
        {
          return $this->testMeta[$namespace][$predicate];
        }
      }
      
      throw new Exception($this->getName() . " doesn't have meta value for " . $namespace . ":" . $predicate);
    }


    if (!$meta = $this->_getMeta($namespace, $predicate))
    {
      throw new Exception($this->getName() . " doesn't have meta value for " . $namespace . ":" . $predicate);
    }

    return $meta->value;
  }


  public function getAllMeta($namespace=null)
  {
    //use class variable if in testMode
    if ($this->testMode)
    {
      if ($namespace && isset($this->testMeta[$namespace]))
      {
        return array($namespace => $this->testMeta[$namespace]);
      }
      elseif ($namespace)
      {
        return array();
      }
      else
      {
        return $this->testMeta;
      }
    }


    $ret = array();
    
    $q = LsQuery::getByModelAndFieldsQuery('ScraperMeta', array(
      'scraper' => $this->getName()
    ));
    
    if ($namespace)
    {
      $q->addWhere('scrapermeta.namespace = ?', $namespace);
    }
    
    foreach ($q->execute() as $meta)
    {
      if (isset($ret[$meta->namespace]))
      {
        $ret[$meta->namespace][$meta->predicate] = $meta->value;
      }
      else
      {
        $ret[$meta->namespace] = array($meta->predicate => $meta->value);
      }
    }  
    
    return $ret;
  }
  
  
  public function removeMeta($namespace, $predicate)
  {
    //use class variable if in testMode
    if ($this->testMode)
    {
      if (!isset($this->testMeta[$namespace]) || !isset($this->testMeta[$namespace][$predicate]))
      {
        throw new Exception($this->getName() . " doesn't have meta value for " . $namespace . ":" . $predicate);      
      }
      else
      {
        unset($this->testMeta[$namespace][$predicate]);
      }    
    }


    if (!$meta = $this->_getMeta($namespace, $predicate))
    {
      throw new Exception($this->getName() . " doesn't have meta value for " . $namespace . ":" . $predicate);
    }
    
    $meta->delete();
  }


  public function removeAllMeta($namespace=null)
  {
    //use class variable if in testMode
    if ($this->testMode)
    {
      $this->testMeta = array();
    }
    

    $q = LsQuery::getByModelAndFieldsQuery('ScraperMeta', array(
      'scraper' => $this->getName()
    ));
    
    if ($namespace)
    {
      $q->addWhere('scrapermeta.namespace = ?', $namespace);
    }
    
    $q->delete();
  }
  
  
  public function hasMeta($namespace, $predicate)
  {
    //use class variable if in testMode
    if ($this->testMode)
    {
      return isset($this->testMeta[$namespace]) && isset($this->testMeta[$namespace][$predicate]);    
    }


    return $this->_getMeta($namespace, $predicate) ? true : false;
  }
  
  
  protected function _getMeta($namespace, $predicate)
  {
    return LsQuery::getByModelAndFieldsQuery('ScraperMeta', array(
      'scraper' => $this->getName(),
      'namespace' => $namespace,
      'predicate' => $predicate
    ))->fetchOne();    
  }  

  
  public function safeToRun($task)
  {
    $proc_id = getmypid();
    exec('ps aux -ww | grep symfony | grep :' . $task . ' | grep -v ' . $proc_id . ' | grep -v grep', $status_arr);

    foreach($status_arr as $status)
    {
      //sometimes the shell startup command also appears, which is fine (script is still safe to run)
      if(preg_match('/sh\s+\-c/isu',$status) == 0)
      {
        return false;
      }
    }

    return true;
  }

}