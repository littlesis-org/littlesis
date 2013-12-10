<?php

require_once(sfConfig::get('sf_plugins_dir').'/sfDoctrineGuardPlugin/modules/sfGuardAuth/lib/BasesfGuardAuthActions.class.php');

class sfGuardAuthActions extends BasesfGuardAuthActions
{
  public function executeSignin($request)
  {
    $user = $this->getUser();

    if ($user->isAuthenticated())
    {
      return $this->redirect('@homepage');
    }

    if ($request->getParameter('no_layout'))
    {
      $this->setLayout(false);
    }

    $this->form = new LoginForm();

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getParameter('signin'));

      if ($this->form->isValid())
      {
        $values = $this->form->getValues();

        //check that email has been confirmed
        $profile = Doctrine::getTable('sfGuardUserProfile')->findOneByEmail($values['username']);

        if (!$profile->is_confirmed)
        {
          $request->setError('', "This email address hasn't been confirmed; check your inbox for an email with a confirmation link");
          $this->getUser()->setAuthenticated(false);
          
          return sfView::SUCCESS;
        }

        if (!$profile->User->is_active)
        {
          $request->setError('', "This account is disabled; contact the system administrator");
          $this->getUser()->setAuthenticated(false);
          
          return sfView::SUCCESS;
        }

        //if logins restricted to admins only, check that they have admin powers
        if (sfConfig::get('app_login_admin_only') == 'on')
        {
          if (!sfGuardUserTable::isAdmin($profile->user_id))
          {
            $request->setError('', "Login has been disabled for non-administrators. Please try again later.");
            $this->getUser()->setAuthenticated(false);
            
            return sfView::SUCCESS;
          }
        }


        $remember = isset($values['remember']);
        $this->getUser()->signIn($values['user'], $remember);


        if (!$signinUrl = $request->getParameter('referer'))
        {
          $signinUrl = sfConfig::get('app_sf_guard_plugin_success_signinUrl', $user->getReferer($request->getReferer()));
        }

        $url = LsRouting::generateUrlForRedirect($signinUrl);

        return $this->redirect('' != $url ? $url : '@homepage');
      }
    }
    else
    {
      if ($request->isXmlHttpRequest())
      {
        $this->getResponse()->setHeaderOnly(true);
        $this->getResponse()->setStatusCode(401);

        return sfView::NONE;
      }

      $user->setReferer($request->getReferer());

      $module = sfConfig::get('sf_login_module');

      if ($this->getModuleName() != $module)
      {
        return $this->redirect($module.'/'.sfConfig::get('sf_login_action'));
      }
    }
  }

  public function executeSignout($request)
  {
    $session_id = $request->getCookie('_lilsis_session');
    $sql = "DELETE FROM sessions WHERE session_id = ?";
    $db = Doctrine_Manager::connection();
    $stmt = $db->execute($sql, array($session_id));

    parent::executeSignout($request);
  }
}