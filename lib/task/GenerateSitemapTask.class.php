<?php

class GenerateSitemapTask extends sfBaseTask
{
  protected $urlBase = 'http://littlesis.org';
  protected $fileNames = array();
  

  protected function configure()
  {
    $this->namespace        = 'sitemap';
    $this->name             = 'generate';
    $this->briefDescription = 'generates a Sitemap based on the Sitemap protocol';
    $this->detailedDescription = '';
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'How many entities to perform this operation on', 1000);
    $this->addOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'all, static, entity, relationship, list, user, group', 'all');
  }


  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      


    $startTime = microtime(true);
    $db = Doctrine_Manager::connection();

    $urls = array();

    switch ($options['type'])
    {
      case 'all':
        $types = array('static', 'entity', 'relationship', 'list', 'user', 'group');
        $tmpArguments = $arguments;
        $tmpOptions = $options;
        
        foreach ($types as $type)
        {
          $this->run(array($tmpArguments['application']), array(
            'env=' . $tmpOptions['env'],
            'limit=' . $tmpOptions['limit'],
            'type=' . $type
          ));
        }
        
        if (count($this->fileNames))
        {
          $this->writeSitemapIndex();
        }

        return;

        
      case 'static':
        //static urls
        $urls = array(
          '/',
          '/lists',
          '/modification/latest',
          '/notes',
          '/groups',
          '/guide',
          '/faq',
          '/howto',
          '/videos',
          '/contact',
          '/join',
          '/about',
          '/features',
          '/team',
          '/press',
          '/disclaimer'
        );

        break;

        
      case 'entity':
        //get entity urls
        $q = LsDoctrineQuery::create()
          ->select("CONCAT('/', LOWER(e.primary_ext), '/', e.id, '/', REPLACE(REPLACE(e.name, ' ', '_'), '/', '~'))")
          ->from('Entity e')
          ->where('e.is_deleted = 0');    
        $stmt = $db->execute($q->getSqlQuery());
        $entityUrls = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
        $patterns = array(
          '',
          '/references',
          '/notes',
          '/images',
          '/interlocks',
          '/giving'
        );
        
        foreach ($entityUrls as $u)
        {
          foreach ($patterns as $p)
          {
            $urls[] = $u . $p;
          }
        }        

        break;


      case 'relationship':
        //get relationship urls
        $q = LsDoctrineQuery::create()
          ->select('r.id')
          ->from('Relationship r')
          ->where('r.is_deleted = 0');
        $stmt = $db->execute($q->getSqlQuery());
        $relIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $patterns = array(
          'view',
          'references',
          'notes'
        );
        
        foreach ($relIds as $id)
        {
          foreach ($patterns as $p)
          {
            $urls[] = '/relationship/' . $p . '/id/' . $id;
          }
        }
        
        break;

        
      case 'list':
        //get list urls
        $q = LsDoctrineQuery::create()
          ->select("CONCAT('/', l.id, '/', REPLACE(REPLACE(l.name, ' ', '_'), '/', '~'))")
          ->from('LsList l')
          ->where('l.is_deleted = 0');
        $stmt = $db->execute($q->getSqlQuery());
        $listUrls = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $patterns = array(
          '',
          '/references',
          '/notes',
          '/members',
          '/interlocks',
          '/giving',
          '/funding'
        );
        
        foreach ($listUrls as $u)
        {
          foreach ($patterns as $p)
          {
            $urls[] = '/list' . $u . $p;
          }
        }
        
        break;

        
      case 'user':
        //get user urls
        $q = LsDoctrineQuery::create()
          ->select('p.public_name')
          ->from('sfGuardUserProfile p')
          ->where('p.is_visible = 1 AND p.is_confirmed = 1')
          ->setHydrationMode(Doctrine::HYDRATE_NONE);
        $stmt = $db->execute($q->getSqlQuery());
        $names = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $patterns = array(
          '',
          '/groups'
        );
        
        foreach ($names as $name)
        {
          foreach ($patterns as $p)
          {
            $urls[] = '/user/' . $name . $p;
          }
        }
        
        break;

        
      case 'group':
        //get group urls
        $q = LsDoctrineQuery::create()
          ->select('g.name')
          ->from('sfGuardGroup g')
          ->where('g.is_working = 1 AND g.is_private = 0')
          ->setHydrationMode(Doctrine::HYDRATE_NONE);
        $stmt = $db->execute($q->getSqlQuery());
        $names = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($names as $name)
        {
          $urls[] = '/group/' . $name;
        }
        
        break;
    }

    
    //break urls into sets of 50,000
    $num = ceil(count($urls) / 50000);
    $urlSets = array();
    
    for ($n = 0; $n < $num; $n++)
    {
      $urlSets[] = array_slice($urls, $n * 50000, 50000);
    }


    print("Found " . count($urls) . " " . $options['type'] . " URLs; writing to " . count($urlSets) . " Sitemap files...\n");

        
    //generate sitemap XML file for each set
    foreach ($urlSets as $key => $set)
    {
      $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
      $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
      
      foreach ($set as $url)
      {
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($this->urlBase . $url) . "</loc>\n";
        $xml .= "  </url>\n";
      }
  
      $xml .= "</urlset>";
    
      //write to file in /web
      $fileName = $options['type'] . '-sitemap.' . ($key + 1) . '.xml';
      $f = fopen(sfConfig::get('sf_web_dir') . '/' . $fileName, 'w');
      fwrite($f, $xml);
      fclose($f);
      
      $this->fileNames[] = $fileName;
      
      print("+ Wrote " . count($set) . " URLs to sitemap file: " . $fileName . "\n");
    }


    //DONE
    LsCli::beep();
  }


  protected function writeSitemapIndex()
  {
    print("Writing " . count($this->fileNames) . " Sitemap files to Sitemap index...\n");

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    foreach ($this->fileNames as $fileName)
    {
      $xml .= "  <sitemap>\n";
      $xml .= "    <loc>" . htmlspecialchars($this->urlBase . '/' . $fileName) . "</loc>\n";
      $xml .= "  </sitemap>\n";
    }
    
    $xml .= "</sitemapindex>";      
    
    //write to file in /web
    $fileName = sfConfig::get('sf_web_dir') . '/sitemap-index.xml';
    $f = fopen($fileName, 'w');
    fwrite($f, $xml);
    fclose($f);  
  }
}