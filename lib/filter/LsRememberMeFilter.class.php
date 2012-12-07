<?php

class LsRememberMeFilter extends sfFilter
{
  public function execute ($filterChain)
  {
    if ($this->isFirstCall() and !$this->getContext()->getUser()->isAuthenticated())
    {
      if ($cookie = $this->getContext()->getRequest()->getCookie(sfConfig::get('app_sf_guard_plugin_remember_cookie_name', 'sfRemember')))
      {
        $q = Doctrine_Query::create()
              ->from('sfGuardRememberKey r')
              ->innerJoin('r.sfGuardUser u')
              ->where('r.remember_key = ?', $cookie);

        if ($q->count())
        {
          $this->getContext()->getUser()->signIn($q->fetchOne()->sfGuardUser);
        }
      }
    }

    $filterChain->execute();
  }
}