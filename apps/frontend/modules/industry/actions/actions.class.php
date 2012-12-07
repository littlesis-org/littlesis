<?php

class industryActions extends sfActions
{
  public function checkCategory($request)
  {
    if ($categoryId = $request->getParameter('category'))
    {
      if (!$this->category = Doctrine::getTable('OsCategory')->findOneByCategoryId($categoryId))
      {
        $this->forward404();
      }
    }
  }

  public function checkIndustry($request)
  {
    if ($industryId = $request->getParameter('industry'))
    {
      if (!$this->industry = Doctrine::getTable('OsCategory')->findOneByIndustryId($industryId))
      {
        $this->forward404();
      }
    }
  }

  public function checkSector($request)
  {
    if ($sector = $request->getParameter('sector'))
    {
      if (!$this->sector = Doctrine::getTable('OsCategory')->findOneBySectorName($sector))
      {
        $this->forward404();
      }
    }
  }

  public function executeList($request)
  {
    $q = LsDoctrineQuery::create()
      ->from('OsCategory c')
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);
    
    if ($sector = $request->getParameter('sector'))
    {
      $q->where('c.sector_name = ?', $sector);
    }

    $this->categories = $q->execute();
    usort($this->categories, array('OsCategoryTable', 'categoryCmp'));
  
    $this->sectors = OsCategoryTable::getSectorNames();
  }

  public function executeCategory($request)
  {
    $this->checkCategory($request);
    $this->sector = $this->category['sector_name'];
    $this->industry = $this->category['industry_name'];

    $this->type = $request->getParameter('type');
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 50);
    
    switch ($this->type)
    {
      case 'people':
        $q = LsDoctrineQuery::create()
          ->from('Entity e')
          ->leftJoin('e.OsEntityCategory ec')
          ->leftJoin('ec.OsCategory c')
          ->leftJoin('e.Person p')
          ->where('e.primary_ext = ?', 'Person')
          ->andWhere('c.category_id = ?', $this->category['category_id'])
          ->orderBy('p.name_last ASC, p.name_first ASC')
          ->setHydrationMode(Doctrine::HYDRATE_ARRAY);      
        $this->person_pager = new LsDoctrinePager($q, $page, $num);   
        break;

      case 'orgs':
        $q = LsDoctrineQuery::create()
          ->from('Entity e')
          ->leftJoin('e.OsEntityCategory ec')
          ->leftJoin('ec.OsCategory c')
          ->where('e.primary_ext = ?', 'Org')
          ->andWhere('c.category_id = ?', $this->category['category_id'])
          ->orderBy('e.name ASC')
          ->setHydrationMode(Doctrine::HYDRATE_ARRAY);        
        $this->org_pager = new LsDoctrinePager($q, $page, $num);      
        break;

      default:
        $db = LsDb::getDbConnection();
        $sql = "SELECT COUNT(DISTINCT(e.id)) FROM entity e " .
               "LEFT JOIN os_entity_category ec ON (ec.entity_id = e.id) " .
               "WHERE ec.category_id = ? AND e.primary_ext = ?";
        $stmt = $db->execute($sql, array($this->category['category_id'], 'Person'));
        $this->person_count = $stmt->fetch(PDO::FETCH_COLUMN);  
        $stmt = $db->execute($sql, array($this->category['category_id'], 'Org'));
        $this->org_count = $stmt->fetch(PDO::FETCH_COLUMN);    
        break;    
    }
  }
  
  public function executePeople($request)
  {
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 50);

    $this->checkCategory($request);

    if ($this->category)
    {
      $this->sector = $this->category['sector_name'];
      $this->industry = $this->category['industry_name'];
      
      $q = LsDoctrineQuery::create()
        ->from('Entity e')
        ->leftJoin('e.OsEntityCategory ec')
        ->leftJoin('ec.OsCategory c')
        ->leftJoin('e.Person p')
        ->where('e.primary_ext = ?', 'Person')
        ->andWhere('c.category_id = ?', $this->category['category_id'])
        ->orderBy('p.name_last ASC, p.name_first ASC')
        ->setHydrationMode(Doctrine::HYDRATE_ARRAY);      

      $this->person_pager = new LsDoctrinePager($q, $page, $num);      
    }
  }

  public function executeOrgs($request)
  {
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 50);

    $this->checkCategory($request);

    if ($this->category)
    {
      $this->sector = $this->category['sector_name'];
      $this->industry = $this->category['industry_name'];
      
      $q = LsDoctrineQuery::create()
        ->from('Entity e')
        ->leftJoin('e.OsEntityCategory ec')
        ->leftJoin('ec.OsCategory c')
        ->where('e.primary_ext = ?', 'Org')
        ->andWhere('c.category_id = ?', $this->category['category_id'])
        ->orderBy('e.name ASC')
        ->setHydrationMode(Doctrine::HYDRATE_ARRAY);      

      $this->org_pager = new LsDoctrinePager($q, $page, $num);      
    }
  }
}