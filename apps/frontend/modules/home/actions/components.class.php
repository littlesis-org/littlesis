<?php

class homeComponents extends sfComponents
{
  public function executeMenu()
  {
    $this->items = array();

    if ($this->getUser()->isAuthenticated())
    {
      $name = $this->getUser()->getGuardUser()->getProfile()->public_name;
      
      $this->items[$name]['highlighted'] = true;
      $this->items[$name]['url'] = 'home/notes';
      $this->items[$name]['items'][$this->getContext()->getI18N()->__('Groups')] = 'home/groups';    
      $this->items[$name]['items'][$this->getContext()->getI18N()->__('Notes')] = 'home/notes';
      $this->items[$name]['items'][$this->getContext()->getI18N()->__('Edits')] = 'home/modifications';
      $this->items[$name]['items'][$this->getContext()->getI18N()->__('Settings')] = 'home/settings';    
      $this->items[$name]['items'][$this->getContext()->getI18N()->__('Logout')] = '@sf_guard_signout';

      $network_name = LsListTable::getNameById(sfGuardUserTable::getHomeNetworkId());
      $network_path = LsListTable::getDisplayNameById(sfGuardUserTable::getHomeNetworkId());

      $this->items[$network_name]['url'] = '@localHome?name=' . 
        $network_path;
      $this->items[$network_name]['highlighted'] = true;
      $this->items[$network_name]['items'][$this->getContext()->getI18N()->__('Notes')] = 'local/notes?name=' . $network_path;
      $this->items[$network_name]['items'][$this->getContext()->getI18N()->__('Analysts')] = 'local/analysts?name=' . $network_path;

      //recent views
      if ($this->getUser()->getGuardUser()->getProfile()->enable_recent_views && $entityIds = $this->getUser()->getAttribute('viewed_entity_ids'))
      {
        $this->items[$this->getContext()->getI18N()->__('Recent')] = array(
          'url' => null,
          'highlighted' => true,
          'items' => array()
        );
  
        //flip entity ids from user session
        $flippedEntityIds = array_flip($entityIds);
        $orderedEntities = array();
  
        //get viewed entities from db
        $entities = LsDoctrineQuery::create()
          ->from('Entity e')
          ->whereIn('e.id', $entityIds)
          ->setHydrationMode(Doctrine::HYDRATE_ARRAY)
          ->execute();

        //order entities
        foreach ($entities as $entity)
        {
          $orderedEntities[$flippedEntityIds[$entity['id']]] = $entity;
        }

        ksort($orderedEntities);
        
        foreach ($orderedEntities as $entity)
        {
          $this->items[$this->getContext()->getI18N()->__('Recent')]['items'][$entity['name']] = EntityTable::generateRoute($entity);
        }
      }
    }
    else
    {
      if (sfConfig::get('app_login_enabled'))
      {
        $this->items[$this->getContext()->getI18N()->__("Login")] = array('url' => '@sf_guard_signin');
        $this->items[$this->getContext()->getI18N()->__("Sign Up")] = array('url' => '@join');
      }
    }

    $this->items = array_merge($this->items, array(
      $this->getContext()->getI18N()->__('Explore') => array(
        'items' => array(
          $this->getContext()->getI18N()->__('Lists') => 'list/list',
          //'Top Analysts' => '@analysts',
          $this->getContext()->getI18N()->__('Recent Updates') => 'modification/latest',
          $this->getContext()->getI18N()->__('Analyst Notes') => '@notes',
          $this->getContext()->getI18N()->__('Research Groups') => '@groups'
        )
      ),
      $this->getContext()->getI18N()->__('Add') => null,
      $this->getContext()->getI18N()->__('Help') => array(
        'items' => array(
          $this->getContext()->getI18N()->__('Site Guide') => '@guide',
          $this->getContext()->getI18N()->__('FAQ') => '@faq',
          $this->getContext()->getI18N()->__('Analyst Howto') => '@howto',
          $this->getContext()->getI18N()->__('Videos') => '@videos'
        )
      ),
      $this->getContext()->getI18N()->__('About') => array(
        'url' => '@about',
        'items' => array(
          $this->getContext()->getI18N()->__('Features') => '@features',
          $this->getContext()->getI18N()->__('Our Team') => '@team',
          $this->getContext()->getI18N()->__('Blog') => 'http://blog.littlesis.org',
          $this->getContext()->getI18N()->__('Press') => '@press',
          $this->getContext()->getI18N()->__('Data API') => 'http://api.littlesis.org',
          $this->getContext()->getI18N()->__('Source Code') => 'https://github.com/littlesis-org/littlesis',
          $this->getContext()->getI18N()->__('Disclaimer') => '@disclaimer',
          $this->getContext()->getI18N()->__('Contact Us') => '@contact'          
        )
      )
    ));

    if (!$this->getUser()->isAuthenticated())
    {
      unset($this->items[$this->getContext()->getI18N()->__('About')]['items'][$this->getContext()->getI18N()->__('Blog')]);
      $this->items[$this->getContext()->getI18N()->__('Blog')] = array(
        'url' => 'http://blog.littlesis.org'
      );      
    }

    if ($this->getUser()->isAuthenticated())
    {
      $this->items[$this->getContext()->getI18N()->__('Add')] = array(
        'items' => array(
          $this->getContext()->getI18N()->__('Person') => 'entity/addPerson',
          $this->getContext()->getI18N()->__('Organization') => 'entity/addOrg'
        )
      );
    }

    if ($this->getUser()->hasCredential('lister'))
    {
      $this->items[$this->getContext()->getI18N()->__('Add')]['items'][$this->getContext()->getI18N()->__('List')] = 'list/add';
    }

    if ($this->getUser()->hasCredential('admin'))
    {
      $base = backend_base();
      unset($this->items['Help']);
      $this->items[$this->getContext()->getI18N()->__('About')]['items'][$this->getContext()->getI18N()->__('Blog')] = 'http://blog.littlesis.org';

      /* hiding for now...
      $this->items[$this->getContext()->getI18N()->__('Admin')] = array(
        'url' => $base,
        'items' => array(
          $this->getContext()->getI18N()->__('Users') => $base . '/user/list',
          $this->getContext()->getI18N()->__('Groups') => $base . '/sfGuardGroup',
          $this->getContext()->getI18N()->__('Modifications') => $base . '/modification/list',
          $this->getContext()->getI18N()->__('API') => $base . '/api/users'
        )
      );
      */
    }
  }
  
  
  public function executeListViews()
  {
    $user = $this->getUser();
    
    if ($user->isAuthenticated() && $user->getGuardUser()->getProfile()->enable_recent_views)
    {
      $this->entities = array();

      //get viewed entity ids from user session
      if ($entityIds = $user->getAttribute('viewed_entity_ids'))
      {
        $flippedEntityIds = array_flip($entityIds);
  
        //get viewed entities from db
        $entities = LsDoctrineQuery::create()
          ->from('Entity e')
          ->whereIn('e.id', $entityIds)
          ->setHydrationMode(Doctrine::HYDRATE_ARRAY)
          ->execute();

        //order entities
        foreach ($entities as $entity)
        {
          $this->entities[$flippedEntityIds[$entity['id']]] = $entity;
        }

        ksort($this->entities);
      }
    }
  }

   
  public function executeImageGallery()
  {
    $count = 3200; //approximate number of person images bigger than 50x50

    $pos = rand(1, 40);
    $chr = dechex(rand(0, 15));
    $limit = 70;
    $max = round($count / 16);
    $offset = rand(0, $max - $limit);

    $q = LsDoctrineQuery::create()
      ->from('Image i')
      ->leftJoin('i.Entity e')
      ->leftJoin('e.ExtensionRecord r')
      ->where('i.height > 50 AND i.width > 50 AND SUBSTRING(i.filename, ?, 1) = ?', array($pos, $chr))
      ->addWhere('r.definition_id = ?', 1)
      ->orderBy('i.filename')
      ->offset($offset)
      ->limit($limit);
      
    $this->images = $q->fetchArray();
  }
  
  
  public function executeStats()
  {
    $this->stats = array();

    $db = Doctrine_Manager::connection();
    $sql = 'SELECT COUNT(*) FROM reference';
    $stmt = $db->execute($sql);
    $citationResults = $stmt->fetch(PDO::FETCH_COLUMN);

    $sql = 'SELECT COUNT(*) FROM relationship WHERE is_deleted = 0';
    $stmt = $db->execute($sql);
    $relationshipResults = $stmt->fetch(PDO::FETCH_COLUMN);
            
    $this->stats['Misc'] = array(
      array(
        'display_name' => 'Citation',
        'num' => $citationResults
      ),
      array(
        'display_name' => 'Relationship',
        'num' => $relationshipResults
      )
    );
  
    $this->stats['Profiles'] = LsDoctrineQuery::create()
      ->select('d.display_name, COUNT(r.id) num')
      ->from('ExtensionDefinition d')
      ->leftJoin('d.ExtensionRecord r')
      ->leftJoin('r.Entity e')
      ->where('e.is_deleted = 0')
      ->groupBy('d.id')
      ->orderBy('num DESC')
      ->having('num > 0')
      ->fetchArray();
  }
  
  
  public function executeFeaturedProfiles()
  {
    $listId = isset($this->list_id) ? $this->list_id : 16;

    $slots = $this->getResponse()->getSlots();
    
    if (!$this->featured_ids = @$slots['featured_ids'])
    {    
      $this->featured_ids = array();
    }

    if (count($this->featured_ids) < 5)
    {
      $q = LsDoctrineQuery::create()
        ->select('le.entity_id')
        ->from('LsListEntity le')
        ->leftJoin('le.Entity e')
        ->leftJoin('e.Image i')
        ->where('le.list_id = ?', $listId)
        ->orderBy('le.updated_at DESC')
        ->andWhereNotIn('le.entity_id', $this->featured_ids)
        ->limit(5 - count($this->featured_ids));
        
      foreach ($q->execute() as $row)
      {
        $this->featured_ids[] = $row['entity_id'];
      }
    }
  }
  
  
  public function executeBlogFeed()
  {
    $this->featured_list_id = isset($this->featured_list_id) ? $this->featured_list_id : 16;
    $this->featured_slot_name = isset($this->featured_slot_name) ? $this->featured_slot_name : 'featured_ids';
    $this->feed_link = isset($this->feed_link) ? $this->feed_link : 'http://feeds2.feedburner.com/EyesOnTheTies';
    $this->more_link = isset($this->more_link) ? $this->more_link : 'http://blog.littlesis.org';

    //blog feed might fail
    try
    {
      $url = isset($this->feed) ? $this->feed : 'http://blog.littlesis.org/feed/';
      $feed = sfFeedPeer::createFromWeb($url);
      $posts = $feed->getItems();  
    }
    catch (Exception $e)
    {
      $posts = array();
    }
    

    $count = 0;
    $this->posts = array();
    $this->featured_post = null;

    foreach ($posts as $post)
    {
      if (in_array('Featured', $post->getCategories()) && !$this->featured_post)
      {
        $this->featured_post = $post;
      }
      else
      {
        $this->posts[] = $post;
        $count++;
      }      

      if ($count > 3)
      {
        break;
      }
    }
    
    if (!$this->featured_post && $count)
    {
      $this->featured_post = array_shift($this->posts);
    }
    
    //set feautured entiites for mini-profiles
    if ($this->featured_post)
    {
      //get featured entities from blog post, looking for 'class="_featured"'
      if (preg_match_all('#<([^>]*class="_featured"[^>]*)>#', $this->featured_post->getContent(), $blogLinks))
      {
        $ids = array();

        foreach ($blogLinks[0] as $blogLink)
        {
          if (preg_match('#littlesis\.org/(person|org)/(\d+)#',$blogLink, $linkMatch))
          {
            $ids[] = $linkMatch[2];
          }
        }

        $db = Doctrine_Manager::connection();

        //get ids of entities already on featured list
        $sql = 'SELECT le.entity_id FROM ls_list_entity le WHERE le.list_id = ?';        
        $stmt = $db->execute($sql, array($this->featured_list_id));
        $existingIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        //set featured_ids slot so that they show up in the right order
        $this->getResponse()->setSlot($this->featured_slot_name, $ids);

        //add featured entities to featured list
        foreach ($ids as $id)
        {
          if (in_array($id, $existingIds))
          {
            //just refresh the updated_at time
            $sql = 'UPDATE ls_list_entity SET updated_at = NOW() WHERE list_id = ? AND entity_id = ?';
            $stmt = $db->execute($sql, array($this->featured_list_id, $id));
          }
          else
          {
            //make sure entity exists
            $sql = 'SELECT COUNT(*) FROM entity e WHERE e.id = ? AND e.is_deleted = 0';
            $stmt = $db->execute($sql, array($id));

            if ((int) $stmt->fetch(PDO::FETCH_COLUMN) > 0)
            {
              //add to list
              $sql = 'INSERT INTO ls_list_entity (list_id, entity_id, last_user_id) VALUES (?, ?, 1)';
              $stmt = $db->execute($sql, array($this->featured_list_id, $id));
            }
          }
        }
      }
    }
  }


  public function executeNameWire()
  {
    $feed = sfFeedPeer::createFromWeb('http://blog.littlesis.org/category/namewire/feed');
    $this->posts = $feed->getItems();  

    if (count($this->posts) > 3)
    {
      $this->posts = array_slice($this->posts, 0, 3);
    }
  }
  
  
  public function executeRecentUpdates()
  {
    $q = new Doctrine_RawSql;
    $q->select('{e.*}, {i.*}, {u.*}, {p.*}')
      ->from('entity e LEFT JOIN image i ON (e.id = i.entity_id AND i.is_deleted = 0) LEFT JOIN sf_guard_user u ON (u.id = e.last_user_id) LEFT JOIN sf_guard_user_profile p ON (u.id = p.user_id)')
      ->where('e.is_deleted IS NULL OR e.is_deleted = ?', false)
      ->addComponent('e', 'Entity e')
      ->addComponent('i', 'e.Image i')
      ->addComponent('u', 'e.LastUser u')
      ->addComponent('p', 'u.Profile p')
      ->orderBy('e.updated_at DESC')
      ->limit(@$this->limit ? $this->limit : 10);
    
    if (@$this->group)
    {
      if (count($userIds = $this->group->getUserIds()))
      {
        $q->andWhereIn('e.last_user_id', $userIds);
        if(count($entityIds = $this->group->getEntityIds()))
        {
          $q->andWhereIn('e.id', $entityIds);
        }
      }
      else
      {
        $q->andWhere('1=0');
      }
    }
    
    if ($this->network)
    {
      $q->leftJoin('ls_list_entity le ON (e.id = le.entity_id)')
        ->addComponent('le', 'e.LsListEntity le')
        ->andWhere('le.list_id = ?', $this->network['id']);
    }

    $this->entities = $q->execute();
  }
  
  
  public function executeFeaturedLists()
  {
    $this->lists = LsDoctrineQuery::create()
      ->from('LsList l')
      ->limit(10)
      ->where('l.is_admin = ?', false)
      ->andWhere('l.is_featured = ?', true)
      ->execute();  
  }
  
  
  public function executeTopAnalysts()
  {
    $this->users = sfGuardUserProfileTable::getTopUsersQuery()->limit(10)->execute();  
  }
  
  
  public function executePoints()
  {
    //$this->profile->refreshScore();
    //$this->stats = $this->profile->getShortSummary();
  }
  
  
  public function executeUserNotes()
  {
    if ($this->getUser()->getProfile()->enable_notes_list)
    {
      $q = NoteTable::getNotesByRecordQuery($this->record)
        ->andWhere('n.user_id = ?', $this->getUser()->getGuardUser()->id)
        ->limit(10);
        
      $this->notes = $q->execute();
    }
  }
  
  
  public function executeRecentNotes()
  {
    $s = new LsSphinxClient($page = 1, $num = 10);

    if (isset($this->network_ids))
    {
      $s->setFilter('network_ids', $this->network_ids);    
    }
    
    $userIds = array_filter(array_unique(array(0, sfGuardUserTable::getCurrentUserId())), 'strlen');
    $s->setFilter('visible_to_user_ids', $userIds);
    $this->notes = NoteTable::getSphinxRecords($s, null, Doctrine::HYDRATE_ARRAY); 
  }
  
  
  public function executeResearchGroups()
  {
    $this->groups = LsDoctrineQuery::create()
      ->from('sfGuardGroup g')
      ->where('g.is_working = 1 AND g.is_private = 0')
      ->orderBy('g.created_at ASC')
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY)
      ->limit(2)
      ->execute();
  }
}