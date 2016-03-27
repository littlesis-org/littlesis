<?php

class ImportCorpsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'import';
    $this->name             = 'corps';
    $this->entity = null;
    $this->dir = sfConfig::get('sf_root_dir') . "/data/";
    $this->list = null;
    $this->url = null;
    $this->url_name = null;
    $this->briefDescription = '';
    $this->appConfiguration = null;
    $this->db = null;
    $this->detailedDescription = <<<EOF
The [ImportCorps|INFO] task imports corps with ciks.
Call it with:

  [php symfony importCorps|INFO]
EOF;
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Test mode', false);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
    $this->addOption('entity_id', null, sfCommandOption::PARAMETER_REQUIRED, 'entity id');
    $this->addOption('list_id', null, sfCommandOption::PARAMETER_REQUIRED, 'list id');
    $this->addOption('filename', null, sfCommandOption::PARAMETER_REQUIRED, 'filename');    
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      

    $this->db = Doctrine_Manager::connection();
    
    if ($options['entity_id'])
    {
      $this->entity = Doctrine::getTable('Entity')->find($options['entity_id']); 
      $accept = strtolower($this->readline(" $this->entity ok? (y or n)"));
      if ($accept == 'n')
      {
        die;
      }
    
    }
    else
    {
      $this->list = Doctrine::getTable('LsList')->find($options['list_id']);
      $accept = strtolower($this->readline(" $this->list ok? (y or n)"));
      if ($accept == 'n')
      {
        die;
      }
    }
   
    $filename = $options['filename'];
    $handle = fopen($this->dir . $filename, "r");

    if ($handle) 
    {
      while ($row = fgets($handle)) 
      {
        $this->processRow($row);
      }
    }
  }
  
     
  public function processRow($row)
  {
    $arr = str_getcsv($row);
    $name = $arr[0];
    $sec_cik = $arr[1];
    $ticker = $arr[2];
    if (strtolower($name) == 'name') return false;
    $this->printDebug("$name with cik $sec_cik. Processing:");
    $corp_with_cik = 
    $q = LsDoctrineQuery::create()
        ->from('Entity e')
        ->leftJoin('e.PublicCompany pc')
        ->where('pc.sec_cik = ?', $sec_cik);
    $corps_with_cik = $q->execute();
    if (count($corps_with_cik) > 0)
    {
      $this->printDebug("\tCompany with existing cik found: $corps_with_cik[0] -- continuing");
      $this->addToList($corps_with_cik[0]);
      return true;
    }
    $name_ns = OrgTable::removeSuffixes($name) . "%";
    $q = EntityTable::getByExtensionQuery('Org')->leftJoin('e.Alias a')->addWhere('e.name like ? or a.name like ?',array($name_ns,$name_ns));
    $corp_name_matches = $q->execute();
    if (count($corp_name_matches))
    {
      $this->printDebug("\tpossible matches:");
    }
    foreach($corp_name_matches as $cnm)
    {
      $this->printDebug("\t$cnm->name");
    }
    foreach($corp_name_matches as $cnm)
    {
      $accept = strtolower($this->readline("\tIs $cnm->name the right match? (y or n)"));
      if ($accept == 'y')
      {
        if ($cnm->hasExtension('PublicCompany'))
        {
          if ($cnm->PublicCompany->sec_cik != null)
          {
            $this->printDebug("\tciks did not match: $sec_cik and $cnm->PublicCompany->sec_cik");
            break;
          }
          else
          {
            $cnm->PublicCompany->sec_cik = $sec_cik;
            $cnm->PublicCompany->ticker = $ticker;
            $this->printDebug("\tnew cik added");
            $cnm->save();
            $this->addToList($cnm);
            return true;
          }
        }
        else
        {
          $cnm->PublicCompany->sec_cik = $sec_cik;
          $cnm->PublicCompany->ticker = $ticker;
          $this->printDebug("\tPC extension and cik added");
          $cnm->save();
          $this->addToList($cnm);
          return true;
        } 
      }
    }

    $corp = new Entity;
    $corp->addExtension('Org');
    $corp->addExtension('Business');
    $corp->addExtension('PublicCompany');
    $corp->sec_cik = $sec_cik;
    $corp->ticker = $ticker;
    $corp->name = $name;
    $corp->save();
    $this->printDebug("\tNew company added: " . $name);
    $this->addToList($corp);
    return;
      
  }
  
  
  public function addToList($entity)
  {
    if(!$this->list) return;
    $q = LsDoctrineQuery::create()
            ->from('LsListEntity le')
            ->where('le.entity_id = ? and le.list_id = ?', array($entity->id, $this->list->id))
            ->fetchOne();
            
    if ($q)
    {
      $this->printDebug('   (already on list...)');
      return;
    }
    $le = new LsListEntity;
    $le->LsList = $this->list;
    $le->Entity = $entity;
    $le->save();
  }
  
  
  public function printDebug($str)
  {
    echo $str . "\n";
  }
  
  public function readline($prompt="", $possible = array('y','n','b'), $lim = 5)
  {
    $response = '';
    $ct = 0;
    while (!in_array($response,$possible) && $ct < $lim)
    {
      print $prompt;
      $out = "";
      $key = "";
      $key = fgetc(STDIN);        //read from standard input (keyboard)
      while ($key!="\n")        //if the newline character has not yet arrived read another
      {
        $out.= $key;
        $key = fread(STDIN, 1);
      }
      $response = $out;
      $ct++;
    }
    return $response;
  }

}

