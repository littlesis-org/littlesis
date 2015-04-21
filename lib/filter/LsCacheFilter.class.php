<?php

class LsCacheFilter extends sfCacheFilter
{
  const MINUTE_LIFETIME      = 60;
  const HOUR_LIFETIME        = 3600;
  const DAY_LIFETIME         = 86400;
  const WEEK_LIFETIME        = 604800;
  const MONTH_LIFETIME       = 2592000;

  //these are actions, organized by module, that are always cached without layout
  protected static $alwaysCached = array(
    'home' => array(
      'features'                  => self::WEEK_LIFETIME,
      'team'                      => self::WEEK_LIFETIME,
      'guide'                     => self::WEEK_LIFETIME,
      'faq'                       => self::WEEK_LIFETIME,
      'howto'                     => self::WEEK_LIFETIME,
      'disclaimer'                => self::WEEK_LIFETIME,
      'press'                     => self::WEEK_LIFETIME,
      'about'                     => self::WEEK_LIFETIME,
      'videos'                    => self::WEEK_LIFETIME,
      'analysts'                  => self::HOUR_LIFETIME
    ),
    'note' => array(
      'search'                    => self::MINUTE_LIFETIME
    ),
    'modification' => array(
      'latest'                    => self::MINUTE_LIFETIME
    ),
    'error' => array(
      'invalid'                   => self::MONTH_LIFETIME,
      'credentials'               => self::MONTH_LIFETIME,
      '404'                       => self::MONTH_LIFETIME,
      'internal'                  => self::MONTH_LIFETIME
    ),
    'group' => array(
      'list'                      => self::HOUR_LIFETIME,
      'updates'                   => self::MINUTE_LIFETIME
    ),
    'help' => array(
      'relationshipCategories'    => self::MONTH_LIFETIME
    ),
    'list' => array(
      'modifications'             => self::MONTH_LIFETIME,
      'entityModifications'       => self::MONTH_LIFETIME,
      'members'                   => self::MONTH_LIFETIME,
      'interlocks'                => self::MONTH_LIFETIME,
      'business'                  => self::MONTH_LIFETIME,    
      'government'                => self::MONTH_LIFETIME,    
      'otherOrgs'                 => self::MONTH_LIFETIME,    
      'giving'                    => self::MONTH_LIFETIME,    
      'funding'                   => self::MONTH_LIFETIME,
      'images'                    => self::DAY_LIFETIME,
      'pictures'                  => self::HOUR_LIFETIME
    ),
    'entity' => array(
      'relationships'             => self::MONTH_LIFETIME,
      'leadership'                => self::MONTH_LIFETIME,
      'board'                     => self::MONTH_LIFETIME,
      'family'                    => self::MONTH_LIFETIME,
      'friends'                   => self::MONTH_LIFETIME,
      'government'                => self::MONTH_LIFETIME,
      'business'                  => self::MONTH_LIFETIME,
      'otherPositions'            => self::MONTH_LIFETIME,
      'education'                 => self::MONTH_LIFETIME,
      'fundraising'               => self::MONTH_LIFETIME,
      'politicalDonors'           => self::MONTH_LIFETIME,
      'people'                    => self::MONTH_LIFETIME,
      'memberships'               => self::MONTH_LIFETIME,
      'owners'                    => self::MONTH_LIFETIME,
      'holdings'                  => self::MONTH_LIFETIME,
      'transactions'              => self::MONTH_LIFETIME,
      'donors'                    => self::MONTH_LIFETIME,
      'recipients'                => self::MONTH_LIFETIME,
      'lobbying'                  => self::MONTH_LIFETIME,
      'lobbiedBy'                 => self::MONTH_LIFETIME,
      'lobbyingTargets'           => self::MONTH_LIFETIME,
      'office'                    => self::MONTH_LIFETIME,
      'officeOf'                  => self::MONTH_LIFETIME,
      'image'                     => self::MONTH_LIFETIME,
      'images'                    => self::MONTH_LIFETIME,
      'address'                   => self::MONTH_LIFETIME,
      'network'                   => self::MONTH_LIFETIME,
      'interlocks'                => self::MONTH_LIFETIME,
      'schools'                   => self::MONTH_LIFETIME,
      'giving'                    => self::MONTH_LIFETIME,
      'funding'                   => self::MONTH_LIFETIME,
      'political'                 => self::MONTH_LIFETIME,
      'references'                => self::MONTH_LIFETIME,
      'modifications'             => self::MONTH_LIFETIME,
      'imageModifications'        => self::MONTH_LIFETIME,
      'imagesModifications'       => self::MONTH_LIFETIME,
      'relationshipModifications' => self::MONTH_LIFETIME,
      'childOrgs'                 => self::MONTH_LIFETIME,
      'lobbyingArmy'              => self::MONTH_LIFETIME,
      'networkSearch'             => self::WEEK_LIFETIME
    ),
    'relationship' => array(
      'modifications'             => self::MONTH_LIFETIME
    ),
    'user' => array(
      'modifications'             => self::MONTH_LIFETIME,
      'groups'                    => self::HOUR_LIFETIME
    )
  );

  
  //these are cached with layout if the user isn't logged in
  protected static $outsideCached = array(
    'home' => array(
      'index'                     => self::MONTH_LIFETIME
    ),
    'entity' => array(
      'view'                      => self::MONTH_LIFETIME,
      'references'                => self::MONTH_LIFETIME,
      'notes'                     => self::MONTH_LIFETIME,
      'map'                       => self::MONTH_LIFETIME,
      'interlocksMap'             => self::MONTH_LIFETIME
    ),
    'relationship' => array(
      'view'                      => self::MONTH_LIFETIME,
      'references'                => self::MONTH_LIFETIME,
      'notes'                     => self::MONTH_LIFETIME
    ),
    'list' => array(
      'view'                      => self::MONTH_LIFETIME,
      'references'                => self::MONTH_LIFETIME,
      'notes'                     => self::MONTH_LIFETIME,
      'list'                      => self::DAY_LIFETIME,
      'map'                       => self::MONTH_LIFETIME
    ),
    'group' => array(
      'view'                      => self::DAY_LIFETIME      
    ),
    'user' => array(
      'notes'                     => self::MONTH_LIFETIME,
      'note'                      => self::MONTH_LIFETIME
    ),
    'local' => array(
      'index'                     => self::HOUR_LIFETIME,
      'analysts'                  => self::DAY_LIFETIME,
      'notes'                     => self::HOUR_LIFETIME
    ),
    'map' => array(
      'view'                      => self::MONTH_LIFETIME    
    )
  );

  
  //these are cached without layout for logged in users
  protected static $insideCached = array(
    'entity' => array(
      'addRelationship'           => self::MONTH_LIFETIME
    ),
    'list' => array(
      'addEntiy'                  => self::MONTH_LIFETIME,
      'list'                      => self::MINUTE_LIFETIME
    )
  );
  
  public static function cachedActionsByModule($module)
  {
    $actions = array();

    if (self::$alwaysCached[$module])
    {
      $actions = array_merge($actions, array_keys(self::$alwaysCached[$module]));
    }

    if (self::$outsideCached[$module])
    {
      $actions = array_merge($actions, array_keys(self::$outsideCached[$module]));
    }

    if (self::$insideCached[$module])
    {
      $actions = array_merge($actions, array_keys(self::$insideCached[$module]));
    }

    return array_unique($actions);
  }

  public function execute($filterChain)
  {
    // execute this filter only once, if cache is set and no GET or POST parameters
    if (!sfConfig::get('sf_cache') || $this->skipCaching())
    {
      $filterChain->execute();
      return;
    }

    $this->setDynamicRules();

    if ($this->executeBeforeExecution())
    {
      $filterChain->execute();
    }

    $this->executeBeforeRendering();
  }
  
  
  public function skipCaching()
  {
    if ($this->getContext()->getRequest()->getMethodName() == "HEAD")
    {
      return true;
    }

    return false; //$this->getContext()->getUser()->isAuthenticated();
  }


  public function setDynamicRules()
  {
    $context = $this->getContext();
    $user = $context->getUser();
    $request = $context->getRequest();
    $module = $request->getParameter('module');
    $action = $request->getParameter('action');

    $cache = false;
    $lifetime = 0;
    $withLayout = false;


    //the following actions will not be hard cached when access is restricted to admins only
    $nuclearCachingExceptions = array(
      'sfGuardAuth' => array(
        'signin' => true
      ),
      'home' => array(
        'contact' => true,
        'join' => true,
        'confirmed' => true,
        'requested' => true,
        'joined' => true,
        'confirmEmail' => true,
        'chat' => true
      )
    );

    //if access is restricted to admins only, pages not in the home module will be cached for a week
    if (
      (sfConfig::get('app_login_admin_only') == 'on') && 
      (!$user->isAuthenticated() || !sfGuardUserTable::isAdmin($user->getGuardUser()->id)) && 
      !isset($nuclearCachingExceptions[$module][$action])
    )
    {
      $cache = true;
      $withLayout = true;
      $lifetime = self::WEEK_LIFETIME;
    }

    elseif ($lifetime = self::$alwaysCached[$module][$action])
    {
      $cache = true;
      $withLayout = $request->isXmlHttpRequest() || !$user->isAuthenticated();
    }
    elseif (!$user->isAuthenticated() && $lifetime = self::$outsideCached[$module][$action])
    {
      $cache = true;
      $withLayout = true;
    }
    elseif ($user->isAuthenticated() && $lifetime = self::$insideCached[$module][$action])
    {
      $cache = true;
      $withLayout = false;
    }

    if ($cache)
    {
      $context->getViewCacheManager()->addCache($module, $action, array(
        'withLayout' => $withLayout,
        'lifeTime' => $lifetime
      ));      
    }
  }
  
  
  static function getCachedActionsByModule($module)
  {
    return array_unique(array_merge(
      array_keys(self::$alwaysCached[$module]),
      array_keys(self::$insideCached[$module]),
      array_keys(self::$outsideCached[$module])
    ));
  }  
}
