<?php

class UKMPCandidateScraper extends Scraper
{
  protected $delay         = 2; // Delay between API calls
  protected $cache_timeout = 86400; // Keep API results for a day
  
  public function execute()
  {
    if (!$this->safeToRun('uk-mp-candidates'))
    {
      $this->printDebug('Script already running');
      die;
    }
    
    
    // Get (or create) the UK local Network
    $uk = Doctrine::getTable('LsList')->findOneByName('United Kingdom');
    if (!$uk)
    {
      $uk = new LsList;
      $uk->name         = 'United Kingdom';
      $uk->is_network   = 1;
      $uk->description  = 'People and organizations with significant influence on the policies of the United Kingdom';
      $uk->display_name = 'uk';
      $uk->save();
    }
    
    // Get the MP list
    $raw = $this->getMPs();


    // Add new MPs to the list
    foreach($raw as $mp)
    {
      $this->printDebug(sprintf('Processing %s', $mp['name']));

      // Split name
      $entity = PersonTable::parseFlatName($mp['name']);
      $entity->blurb = 'Prospective Parliamentary Candidate for '.$mp['constituency'];

      $q = TagTable::getByTripleQuery('yournextmp', 'url', $mp['url']);
      $r = $q->count();
      if ($r)
      {
        $this->printDebug('Already processed, skipping.');
        continue;
      }
      
      
      // Get political party
      $q = EntityTable::getByExtensionQuery('PoliticalParty')
        ->addWhere('e.name = ?', $mp['party']);
    
      if (!$partyEntity = $q->fetchOne())
      {
        $partyEntity = new Entity;
        $partyEntity->addExtension('Org');
        $partyEntity->addExtension('PoliticalParty');
        $partyEntity->name = $mp['party'];
        $partyEntity->blurb = 'UK Political Party';
        $partyEntity->save(null, true, array($uk->id));
        
        $this->printDebug("Created new political party: " . $mp['party']);
      }

      // Save entity to UK Network
      $entity->party_id = $partyEntity->id;
      $entity->save(null, true, array($uk->id));

      // Add party relationship 
      $r = new Relationship;
      $r->entity1_id    = $entity->id;
      $r->entity2_id    = $partyEntity->id;
      $r->setCategory('Membership');
      $r->description1  = 'Prospective parliamentary candidate';
      $r->is_current    = true;
      // $r->start_date = // Don't know where we can get this, and "now" seems kind of wrong

      $r->save();


      // Add YourNextMP triple
      $entity->addTagByTriple('yournextmp', 'url', $mp['url']);
      

      // Add references
      $ref = new Reference;
      $ref->addFields(array('name_first', 'name_last', 'name_middle')); // Don't need this
      $ref->source       = $mp['url'];
      $ref->name         = 'YourNextMP.com - ' . $entity['name'];
      $ref->object_model = 'Entity';
      $ref->object_id    = $entity->getId();
      $ref->save();
      unset($ref);
      
      $ref = new Reference;
      $ref->addFields(array('name'));
      $ref->source       = $mp['party_url'];
      $ref->name         = 'YourNextMP.com - ' . $partyEntity['name'];
      $ref->object_model = 'Entity';
      $ref->object_id    = $partyEntity->getId();
      $ref->save();
      unset($ref);

      $ref = new Reference;
      $ref->addFields(array('name'));
      $ref->source       = $mp['url'];
      $ref->name         = 'YourNextMP.com - ' . $entity['name'];
      $ref->object_model = 'Relationship';
      $ref->object_id    = $r->getId();
      $ref->save();
      unset($ref);      
      $r->free(true);
      unset($r);

      
      // Add image?
      if ($mp['image']) {
        if ($fileName = ImageTable::createFiles($mp['image']))
        {
          //insert image record
          $image = new Image;
          $image->filename    = $fileName;
          $image->title       = $entity['name'];
          $image->caption     = 'From YourNextMP under CC-BY-SA license.';
          $image->is_featured = true;
          $image->is_free     = true;
          $image->url         = $mp['image'];

          $this->printDebug("Imported image: " . $image->filename);
        }

        $image->Entity = $entity;
        $image->save();

        if ($mp['image'])
        {
          //save image source
          $image->addReference($mp['image']);

          $this->printDebug("Saved image reference");
        }

        unset($image);
      }


      // Add party image?
      if ($mp['party_image']) {
        if ($fileName = ImageTable::createFiles($mp['party_image']))
        {
          //insert image record
          $partyImage = new Image;
          $partyImage->filename    = $fileName;
          $partyImage->title       = $partyEntity['name'];
          $partyImage->caption     = 'From YourNextMP under CC-BY-SA license.';
          $partyImage->is_featured = true;
          $partyImage->is_free     = true;
          $partyImage->url         = $mp['party_image'];

          $this->printDebug("Imported image: " . $partyImage->filename);
        }

        $partyImage->Entity = $partyEntity;
        $partyImage->save();

        if ($mp['party_image'])
        {
          //save image source
          $partyImage->addReference($mp['party_image']);

          $this->printDebug("Saved image reference");
        }

        unset($partyImage);
      }

      
      unset($entity);
      unset($partyEntity);
      
    }

  }


  public function fetchJson($url)
  {
    $cache = sprintf('%s/%08X.dat', sfConfig::get('sf_cache_dir'), crc32($url));
    
    // No cache, old cache, or invalid cache? Fetch the URL.
    if (!is_file($cache) || (filemtime($cache) < (time() - $this->cache_timeout)) |! $result = file_get_contents($cache))
    {
      $result = file_get_contents($url); // FIXME: Use the Scraper browser object instead?
      file_put_contents($cache, $result);

      $this->printDebug('Sleeping…');
      sleep($this->delay);
    } 
    
    if (!$result)
    {
      throw new Exception("Couldn't get page information from YourNextMP.com.");
    }


    // Decode the response
    $response = json_decode($result, true);
    if (!is_array($response) || !isset($response['pager']) || !isset($response['result']))
    {
      throw new Exception("Unexpected response format.");
    }

    return $response;
  }


  
  public function getMps()
  {
    $url_root   = 'http://www.yournextmp.com';
    $list_root  = $url_root.'/candidates/all/';
    $page_num   = 1; // Assume page 1 exists
    $url_suffix = '?output=json';


    // Fetch the first page
    $this->printDebug('Retrieving first page…');
    $url = $list_root . $page_num . $url_suffix;
    $response = $this->fetchJson($url);
    
    // Get a list of pages to fetch
    $this->printDebug('Building page list…');
    $page_urls = array();
    foreach(range(1,$response['pager']['last_page']) as $page_num)
    {
      $page_urls[] = $list_root . $page_num . $url_suffix;
    }
    $page_count = count($page_urls);
    $this->printDebug('Found '.$page_count.' pages.');


    $candidates = array();


    foreach($page_urls as $idx => $url){
      $this->printDebug('Retrieving page '.($idx+1));
      if ($idx != 0) // We already have the first page
      {
        $response = $this->fetchJson($url);
      }

      // For each page, get the entities
      foreach($response['result'] as $person)
      {
        if (!isset($person['seats']) || !count($person['seats']) || !isset($person['party']))
        {
          $this->printDebug('Skipping partyless MP: '.$person['name']);
          continue;
        }
        
        $candidates[$url_root.$person['path']] = array(
          'url'          => $url_root.$person['path'],
          'name'         => $person['name'],
          'party'        => $person['party']['name'],
          'party_url'    => $url_root.$person['party']['path'],
          'constituency' => $person['seats'][0]['name'],
          'image'        => isset($person['image']['large']['url']) ? $person['image']['large']['url'] : null,
          'party_image'  => isset($person['party']['image']['large']['url']) ? $person['party']['image']['large']['url'] : null,
        );
        
      }
    }

    $this->printDebug(sprintf('Collected %d candidates', count($candidates)));
    return $candidates;
  
  }





}


