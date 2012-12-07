<?php

abstract class ForbesScraper extends Scraper
{
  
	protected $start = 0;
	protected $end = null;
	protected $list = null;
  protected $year = null;
	protected $refreshDays = 30;	

  protected $list_name = null;
  protected $list_description = null; 
  protected $list_fields = null;

  protected $baseurl = 'http://www.forbes.com/';

  
  

  abstract protected function import($url);
  abstract protected function setListOptions();
  
	public function execute(){

    try
    {
      $this->urls = $this->getUrls();
      if(!$this->urls)
      {
				$this->printDebug("Could not retrieve list of URLS");      
      }
		}
    catch (Exception $e)
    {
      throw $e;
    }	    
    
    $this->setListOptions();
    if($this->list_name == null|| $this->list_description == null || $this->list_fields == null){
      throw new Exception('setListOptions must define: list_name, list_description, list_fields');
    }

    $this->setList($this->list_name, $this->list_description, $this->list_fields);


		if(count( $this->urls) ){
			foreach ($this->urls as $count=>  $url)
			{
				//get DB connection for transactions		
				try 
				{
          
					//begin transaction
					$this->db->beginTransaction();
					$this->printDebug("\n***** Searching  *****");
          $this->printDebug("Memory used: " . LsNumber::makeBytesReadable(memory_get_usage()) );
          $this->printDebug("Now: ". date('l jS \of F Y h:i:s A') );
          
          $urlkey = md5($url);
					/*if ($this->hasMeta($urlkey, 'refesh_time') && time() < (int)$this->getMeta($urlkey, 'refesh_time') )
					{
            $this->printDebug("Refresh time: " . date('l jS \of F Y h:i:s A', (int)$this->getMeta($urlkey, 'refesh_time') ) );
						$this->printDebug("Already scraped; skipping");
						$this->db->rollback();
						continue;
					}*/
					
					$this->import($url);
          
					if ($this->limit === $count) { break; }		
					if ($this->testMode) { continue; }	
  
					//commit transaction
					$this->db->commit();
	
          $refresh_days = time() + ($this->refreshDays * 24 * 60 * 60);
          $this->saveMeta($urlkey, 'refesh_time', $refresh_days);					
					$this->printDebug( "OK");
				}
				catch (Exception $e)
				{
					//something bad happened, rollback
					$this->db->rollback();		
          throw $e;
				}
			}
		}
		else{
			$this->printDebug('No URLs found'); 
		}
		
	}
  
	public function setStart($start)
	{
		$this->start = $start;
	}
	
	public function setEnd($end)
	{
		$this->end = $end;
	}

	public function setYear($year)
	{
    
    if(!$year){
      $years = array_keys($this->list_urls);
      $this->year = end($years);
      reset($this->list_urls);
    }
    else{
      $this->year = $year;
    }
	}
  
  protected function setList($title, $description, $fields='name, description, is_ranked')
  {
    
		$count = count($this->urls);
		$end = $this->end ? $this->end : $count;
    reset($this->urls);

    if( !$this->year ){      
      throw new Exception('ForbesScraper:setYear has to be called before ForbesScraper:setList');
    }
    
    $this->list_name = $title. " ($this->year)"; //'Forbes Largest Private Companies (' . $this->year . ')';
    $list = Doctrine_Query::create()
          ->from('LsList L')
          ->where('L.name = ?', $this->list_name)
          ->fetchOne();
    
    //if thlis year's fortune list doesn't already exist, create it    
    if (!$list)
    {
      try
      {
        $list = new LsList;
        $list->name = $this->list_name;
        $list->description = $description; //"Fortune Magazine's list of large US private companies";
        $list->is_ranked = 1;
        $list->save();
        $this->list = $list;
        
        $ref = new Reference;
        $ref->object_model = 'LsList';
        $ref->object_id = $list->id;
        $ref->fields = $fields;
        $ref->source = $this->list_urls[$this->year]['source_url'];
        $ref->save();        
      }
       catch (Exception $e)
      {
				$this->db->rollback();		
        throw $e;
      }
    }
    else
    {
      $this->list = $list;
    }
  } 

  protected function getList($entity){
    
    if($this->list == null){
      throw new Exception('List is not reclared');
    }

    return Doctrine_Query::create()
          ->from('LsListEntity L')
          ->where('L.list_id = ? AND L.entity_id = ?', array( $this->list->id, $entity->id) )
          ->fetchOne();
  }
  

  
	protected function getUrls()
	{
    
    if($this->list_urls[$this->year]['enabled'] == false){
      throw new Exception('Year not available');    
    }
    
    
		$urls = array();

    
		for ($n = 1; $n <= 20; $n++)
		{
      $page = null;
      if($this->list_urls[$this->year]['counting_exception'] == 'underscore'){
        $page = ($n != 1) ? '_' . $n : '';
      }
      elseif($this->list_urls[$this->year]['counting_exception'] != false && $this->list_urls[$this->year]['counting_exception'] != 'underscore'){
        $page = $this->list_urls[$this->year]['counting_exception'].$n;
      }
      elseif($this->list_urls[$this->year]['counting_exception'] == false){
         $page = ($this->list_urls[$this->year]['count_by'] * $n)+1;     
      }
      else{
        $page = $n;
      }

			$listUrl = $this->list_urls[$this->year]['list_base_url'] . $page . $this->list_urls[$this->year]['append'];
			
      //echo $listUrl."\n";
			if (!$this->browser->get($listUrl)->responseIsError())
			{
				$text = $this->browser->getResponseText();

				preg_match_all($this->list_urls[$this->year]['reg_ex_match'], $text, $matches, PREG_PATTERN_ORDER);
					
				while ($match = current($matches[1]))
				{
					$urls[] = $this->list_urls[$this->year]['profile_url'] . $match;				
					next($matches[1]);
				}			
			}
			else
			{
				break;
			}
		}
		
		return $urls;
	}
  
	protected function attachImage(Entity $entity, $url)
	{

		$filename =  ImageTable::createFiles(preg_replace('/^https/i', 'http', $url), basename($url));		
		
		if ( $filename )
		{
			$this->printDebug( "Filename saved as " . $filename);

			//insert image row
			$image = new Image;
			$image->Entity  = $entity;
			$image->filename  = $filename;
			$image->url  = $url;
			$image->title = $entity->name;
			$image->is_featured = 1;
			$image->is_free = 0;
			$image->save();			
			$image->addReference($url, null, array('filename'), 'Forbes.com');
			$this->printDebug( "Imported image ID: " . $image->getId() );
			return true;			
		}
		else{
			$this->printDebug( "File could not be created");
			return false;			
		}
	}
  
  protected function hasImageAttached($person){

    if (EntityTable::getByExtensionQuery('Org')->leftJoin('e.Image i')->addWhere('i.id == ?', $person->id)->fetchOne())
    {
      return true;
    }
    else{
      return false;
    }
    
  }
  
  protected function saveToList($company, $rank){
  
    $list = Doctrine_Query::create()
          ->from('LsListEntity L')
          ->where('L.list_id = ? AND L.entity_id = ?', array( $this->list->id, $company->id) )
          ->fetchOne();
          
    if($list){
        $this->printDebug($this->list->name." (already saved)");      
    }
    else{
      if($company->id && $this->list->id){
        $listentity = new LsListEntity;
        $listentity->entity_id = $company->id;
        $listentity->list_id = $this->list->id;
        $listentity->rank = $rank;
        $listentity->save(); 
        $this->printDebug($this->list->name." (saved)");        
      }
      else{
        if($this->list->id){
          $this->printDebug("List ID not set");      
        }
        else{
          $this->printDebug("Company ID not set");          
        }
      
      }
    }
  
  }
  
  protected function generatePerson($name_str, $summary = null, $orgs = null ){
    $name_arr = LsLanguage::parseFlatName($name_str);
    extract($name_arr);
    
    $person = new Entity;
    $person->addExtension('Person'); 
    $person->name_prefix = $name_prefix;
    $person->name_first = $name_first;
    $person->name_middle = $name_middle;
    $person->name_last = $name_last;
    $person->name_suffix = $name_suffix;
    $person->name_nick = $name_nick;
    return $person;
  }
  
  
}
  
