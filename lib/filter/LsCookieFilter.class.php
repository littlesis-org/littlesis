<?php

class LsCookieFilter extends sfFilter
{
  public function execute ($filterChain)
  {
    $context = $this->getContext();
    $user = $context->getUser();

    // if ($this->isFirstCall() and !$user->isAuthenticated())
    // {
    //   if ($cookie = $context->getRequest()->getCookie(sfConfig::get('app_sf_guard_plugin_remember_cookie_name', 'sfRemember')))
    //   {
    //     $q = Doctrine_Query::create()
    //           ->from('sfGuardRememberKey r')
    //           ->innerJoin('r.sfGuardUser u')
    //           ->where('r.remember_key = ?', $cookie);

    //     if ($q->count())
    //     {
    //       $user->signIn($q->fetchOne()->sfGuardUser);
    //     }
    //     else
    //     {
    //       $context->getResponse()->setCookie(sfConfig::get('app_sf_guard_plugin_remember_cookie_name', 'sfRemember'), false, time()-86400);
    //     }
    //   }
    // }
    
    if ($this->isFirstCall() and !$user->isAuthenticated())
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
        else
        {
          $context->getResponse()->setCookie(sfConfig::get('app_sf_guard_plugin_remember_cookie_name', 'sfRemember'), false, time()-86400);
        }
      }
    }

    $filterChain->execute();


    //set cookie to indicate whether user us logged in
    if ($user->isAuthenticated())
    {
      $context->getResponse()->setCookie('LittleSisUser', true);
    }
    else
    {
      $context->getResponse()->setCookie('LittleSisUser', false, time()-86400);
    }
  }
}