<?php

class localActions extends sfActions
{
  public function checkNetwork($request)
  {
    $name = $request->getParameter('name');
    
    if (!$this->network = LsListTable::getNetworkByDisplayName($name))
    {
      $this->forward404();
    }      
  }

  public function executeIndex($request)
  {
    $this->checkNetwork($request);
    $this->getResponse()->setTitle($this->network['name'] . ' power elite');

    //get users with this home network
    $q = LsDoctrineQuery::create()
      ->from('sfGuardUser u')
      ->leftJoin('u.Profile p')
      ->where('p.home_network_id = ?', $this->network->id)
      ->andWhere('u.id > 7')
      ->orderBy('u.last_login DESC')
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);
      
    $this->user_pager = new LsDoctrinePager($q, $page=1, $num=20);
    
    // Local template override? Use it.
    $template = 'index';
    if (is_file(sfConfig::get('sf_app_module_dir').'/local/templates/'.$template.ucfirst($this->network['display_name']).'Success.php'))
    {
      $template = 'index'.ucfirst($this->network['display_name']);
    }
    $this->setTemplate($template);

  }
  
  
  public function executeNotes($request)
  {
    $this->checkNetwork($request);

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);

    $this->query = $request->getParameter('query');

    $s = new LsSphinxClient($page, $num);    
    $s->setFilter('network_ids', array($this->network['id']));        
    $s->setFilter('visible_to_user_ids', array_unique(array(0, sfGuardUserTable::getCurrentUserId())));

    $this->note_pager = NoteTable::getSphinxPager($s, $this->query, Doctrine::HYDRATE_ARRAY);
  }
  
  
  public function executeAnalysts($request)
  {
    $this->checkNetwork($request);

    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 50);

    $q = LsDoctrineQuery::create()
      ->from('sfGuardUser u')
      ->leftJoin('u.Profile p')
      ->where('p.home_network_id = ?', $this->network['id'])
      ->orderBy('p.public_name')
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);
      
    $this->user_pager = new LsDoctrinePager($q, $page, $num);  
  }
}