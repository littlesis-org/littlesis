<?php

class LsSecurityFilter extends sfBasicSecurityFilter
{
  public function execute ($filterChain)
  {
    $context = $this->getContext();
    $user = $context->getUser();
    $firstCall = $this->isFirstCall();

    //attempt to log in user if they have the sfRemember cookie
    if (sfconfig::get('app_login_enabled') && $firstCall && !$user->isAuthenticated())
    {
      if ($cookie = $context->getRequest()->getCookie(sfConfig::get('app_sf_guard_plugin_remember_cookie_name', 'sfRemember')))
      {
        $q = Doctrine_Query::create()
          ->from('sfGuardRememberKey r')
          ->innerJoin('r.sfGuardUser u')
          ->where('r.remember_key = ?', $cookie);

        //if login restricted to admins only
        if (sfConfig::get('app_login_admin_only') == 'on')
        {
          $q->leftJoin('u.sfGuardUserGroup g')
            ->leftJoin('u.sfGuardUserPermission p')
            ->andWhere('g.group_id = 1 OR p.permission_id = 1');
        }

        if ($q->count())
        {
          $user->signIn($q->fetchOne()->sfGuardUser);
        }
        else
        {
          $context->getResponse()->setCookie(sfConfig::get('app_sf_guard_plugin_remember_cookie_name', 'sfRemember'), false, time()-86400);
        }
      }
    }

    if ($firstCall && (sfConfig::get('app_login_admin_only') == 'on') && $user->isAuthenticated())
    {
      if (!sfGuardUserTable::isAdmin($user->getGuardUser()->id))
      {
        $options = $context->getStorage()->getOptions();
        $cookieName = $options['session_name'];        
        $user->setAuthenticated(false);
        $context->getResponse()->setCookie(sfConfig::get('app_sf_guard_plugin_remember_cookie_name', 'sfRemember'), false, time()-86400);
        $context->getResponse()->setCookie($cookieName, false, time()-86400);
      }
    }

    if (!sfconfig::get('app_login_enabled'))
    {
      $user->signOut();
    }

    $filterChain->execute();


    //set cookie to indicate whether user us logged in
    if (sfconfig::get('app_login_enabled') && $user->isAuthenticated())
    {
      $context->getResponse()->setCookie('LittleSisUser', true);
    }
    else
    {
      $context->getResponse()->setCookie('LittleSisUser', false, time()-86400);
    }
  }
}