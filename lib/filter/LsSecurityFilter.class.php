<?php

class LsSecurityFilter extends sfBasicSecurityFilter
{
  public function execute ($filterChain)
  {
    $context = $this->getContext();
    $user = $context->getUser();
    $firstCall = $this->isFirstCall();

    // attempt to log in with rails cookie
    if ($firstCall && (!$user->isAuthenticated() || !$user->getGuardUser())) 
    {
        
        if ($cookie = $context->getRequest()->getCookie('_lilsis_session')) 
        {
            $sql = "SELECT data from sessions WHERE session_id = ?";
            $db = Doctrine_Manager::connection();
            $stmt = $db->execute($sql, array($cookie));
            $results = $stmt->fetchAll();
            
            if (count($results) > 0) {
                $sf_user_id = json_decode($results[0]["data"])->value->sf_user_id;
                $q = Doctrine_Query::create()
                    ->from('sfGuardUser')
                    ->where('id = ?', $sf_user_id);

                $sf_user = $q->fetchOne();
                if ($sf_user)
                {
                    $user->signIn($sf_user);
                }
            }

        }
    }


    //attempt to log in user if they have the sfRemember cookie
    if (sfconfig::get('app_login_enabled') && $firstCall && (!$user->isAuthenticated() || !$user->getGuardUser()))
    {
      if ($cookie = $context->getRequest()->getCookie(sfConfig::get('app_sf_guard_plugin_remember_cookie_name', 'sfRemember')))
      {
    
        $q = Doctrine_Query::create()
          ->from('sfGuardRememberKey r')
          ->innerJoin('r.sfGuardUser u')
          ->where('r.remember_key = ?', $cookie);
        
        if ($q->count())
        {
          $user->signIn($q->fetchOne()->sfGuardUser);
        }
      }
    }

    // if ($firstCall && (sfConfig::get('app_login_admin_only') == 'on') && $user->isAuthenticated())
    // {
    //   if (!sfGuardUserTable::isAdmin($user->getGuardUser()->id))
    //   {
    //     $options = $context->getStorage()->getOptions();
    //     $cookieName = $options['session_name'];        
    //     $user->setAuthenticated(false);
    //     $context->getResponse()->setCookie(sfConfig::get('app_sf_guard_plugin_remember_cookie_name', 'sfRemember'), false, time()-86400);
    //     $context->getResponse()->setCookie($cookieName, false, time()-86400);
    //   }
    // }

    // if (!sfconfig::get('app_login_enabled'))
    // {
    //   $user->signOut();
    // }

    $filterChain->execute();


    //set cookie to indicate whether user us logged in
    // if (sfconfig::get('app_login_enabled') && $user->isAuthenticated())
    // {
    //   $context->getResponse()->setCookie('LittleSisUser', true);
    // }
    // else
    // {
    //   $context->getResponse()->setCookie('LittleSisUser', false, time()-86400);
    // }
  }
}