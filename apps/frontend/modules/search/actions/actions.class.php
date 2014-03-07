<?php

class searchActions extends sfActions
{
  public function executeSimple($request)
  {    
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);
    $terms = $request->getParameter('q', '');
    
    //search entities
    switch (sfConfig::get('app_search_engine'))
    {
      case 'sphinx':
        //networks to search
        $networkIds = (array) $request->getParameter('network_ids');

        //only show network options if user has home network other than United States
        if (count($networkIds))
        {
          $this->networks = LsDoctrineQuery::create()
            ->from('LsList l')
            ->whereIn('l.id', $networkIds)
            ->fetchArray();

        }
        else
        {
          $networkIds = null;
        }

        $this->results_pager = EntityTable::getSphinxPager($terms, $page, $num, $networkIds);
        break;
        
      case 'lucene':                      
        $ary = EntityTable::getLuceneArray($terms, null);
        $this->results_pager = new LsDoctrinePager($ary, $page, $num);
        break;
        
      case 'mysql':
      default:        
        $terms = explode(' ', $terms);    
        $q = EntityTable::getSimpleSearchQuery($terms);
        $this->results_pager = new Doctrine_Pager($q, $page, $num);
        break;
    }

    //search lists
    if (strlen($terms) > 2)
    {
      $db = Doctrine_Manager::connection();
      $sql = 'SELECT l.* FROM ls_list l WHERE l.name LIKE ? AND l.is_deleted = 0 AND l.is_network = 0 ' . 
             ($this->getUser()->hasCredential('admin') ? '' : 'AND l.is_admin = 0 ') .
             'ORDER BY l.name ASC';
      $stmt = $db->execute($sql, array('%' . $terms . '%'));
      $this->lists = $stmt->fetchAll(PDO::FETCH_ASSOC);      
    }

    //search groups
    if (strlen($terms) > 2)
    {
      $db = Doctrine_Manager::connection();

      if (sfConfig::get('app_rails_enabled'))
      {
        $sql = 'SELECT g.* FROM groups g WHERE (g.name LIKE ? OR g.tagline LIKE ? OR g.description LIKE ? OR g.slug LIKE ?) AND g.is_private = 0 ORDER BY g.name ASC';
        $stmt = $db->execute($sql, array('%' . $terms . '%', '%' . $terms . '%', '%' . $terms . '%', '%' . $terms . '%'));
      }
      else 
      {
        $sql = 'SELECT g.* FROM sf_guard_group g WHERE (g.display_name LIKE ? OR g.name LIKE ? OR g.blurb LIKE ?) AND g.is_working = 1 AND g.is_private = 0 ORDER BY g.display_name ASC';
        $stmt = $db->execute($sql, array('%' . $terms . '%', '%' . $terms . '%', '%' . $terms . '%'));
      }

      $this->groups = $stmt->fetchAll(PDO::FETCH_ASSOC);      
    }

    //search campaigns
    if (strlen($terms) > 2 && sfConfig::get('app_rails_enabled'))
    {
      $db = Doctrine_Manager::connection();

      $sql = 'SELECT c.* FROM campaigns c WHERE (c.name LIKE ? OR c.tagline LIKE ? OR c.description LIKE ? OR c.slug LIKE ?) ORDER BY c.name ASC';
      $stmt = $db->execute($sql, array('%' . $terms . '%', '%' . $terms . '%', '%' . $terms . '%', '%' . $terms . '%'));

      $this->campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);      
    }
  }


  public function executeAdvanced($request)
  {
    $this->entity_form = new EntityForm;
       
    $this->tier2_defs = array();
    $this->tier3_defs = array();

    $this->extension_forms = array();
    $this->primary_exts = array('Person', 'Org');
    
    foreach ($this->primary_exts as $primaryExt)
    {
      $primaryClass = $primaryExt . 'Form';
      $this->extension_forms[$primaryExt]['primary'] = new $primaryClass;
      
      $this->tier2_defs[$primaryExt] = ExtensionDefinitionTable::getByTier(2, $primaryExt);
      $this->tier3_defs[$primaryExt] = ExtensionDefinitionTable::getByTier(3, $primaryExt);
      
      foreach ($this->tier2_defs[$primaryExt] as $ext)
      {
        if ($ext->has_fields)
        {
          $tier2Class = $ext->name . 'Form';
          $this->extension_forms[$primaryExt]['other'][$ext->name] = new $tier2Class;
        }
      }

      foreach ($this->tier3_defs[$primaryExt] as $ext)
      {
        if ($ext->has_fields)
        {
          $tier3Class = $ext->name . 'Form';
          $this->extension_forms[$primaryExt]['other'][$ext->name] = new $tier3Class;
        }
      }    
    }
        
    $this->request_primary = null;
    
    if ($request->hasParameter('entity'))
    {
      $params = $request->getParameter('entity');

      //bind params to entity form
      $this->entity_form->setDefaults($params);


      //bind params to other forms, if a primary extension is selected
      if ($primary = $request->getParameter('primary'))
      {
        $primary = $this->request_primary = $primary[0];
        $this->extension_forms[$primary]['primary']->setDefaults($params);

        foreach ($this->extension_forms[$primary]['other'] as $form)
        {
          $form->setDefaults($params);
        }
      }


      //build extensions arary
      $extensions = array();

      if ($request->getParameter('extensions'))
      {
        $extensions = array_keys($request->getParameter('extensions'));
      }
      
      if ($primary)
      {
        $extensions[] = $primary;
        $allowedExtensions = ExtensionDefinitionTable::getNamesByParent($primary);
        $extensions = array_intersect($extensions, array_merge(array($primary), $allowedExtensions));        
      }

      //build fields array
      $fields = array_filter($params, array('LsLogic', 'isNotNull'));
      
      
      //build query and pager
      $page = $request->getParameter('page', 1);
      $num = $request->getParameter('num', 20);

      $q = EntityTable::getAdvancedSearchQuery($extensions, $fields);
      $this->results_pager = new Doctrine_Pager($q, $page, $num);
    }
  }
  
  
  public function executeAddress($request)
  {
    if ($str = $request->getParameter('address_search'))
    {
      if ($this->address = AddressTable::parse($str))
      {
        $page = $request->getParameter('page', 1);
        $num = $request->getParameter('num', 20);

        $q = $this->address->getNearbyAddressesQuery(0.2)
          ->groupBy('e.id');
          
        $this->results_pager = new Doctrine_Pager($q, $page, $num);
      }
      else
      {
        $request->setError('address_search_terms', 'The address you entered seems to be invalid');
      }
    }
  }
}
