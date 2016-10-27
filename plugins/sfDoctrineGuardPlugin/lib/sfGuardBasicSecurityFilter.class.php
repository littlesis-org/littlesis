<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage plugin
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfGuardBasicSecurityFilter.class.php 7634 2008-02-27 18:01:40Z fabien $
 */
class sfGuardBasicSecurityFilter extends sfBasicSecurityFilter
{
  public function execute ($filterChain)
  {
      $context = $this->getContext();
      $user = $context->getUser();
      $firstCall = $this->isFirstCall();

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

    // if ($this->isFirstCall() and !$this->getContext()->getUser()->isAuthenticated())
    // {
        

    //   if ($cookie = $this->getContext()->getRequest()->getCookie(sfConfig::get('app_sf_guard_plugin_remember_cookie_name', 'sfRemember')))
    //   {
    //     $q = Doctrine_Query::create()
    //           ->from('sfGuardRememberKey r')
    //           ->innerJoin('r.sfGuardUser u')
    //           ->where('r.remember_key = ?', $cookie);

    //     if ($q->count())
    //     {
    //       $this->getContext()->getUser()->signIn($q->fetchOne()->sfGuardUser);
    //     }
    //   }
    // }

    
    parent::execute($filterChain);
  }
}