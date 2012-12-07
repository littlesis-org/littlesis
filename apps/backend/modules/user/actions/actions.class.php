<?php

/**
 * admin user actions.
 *
 * @package		admin
 * @subpackage  user
 * @author		Matthew Skomarovsky
 */
class userActions extends sfActions
{
	public function executeList($request)
	{
		$num = $request->getParameter('num', 20);
		$page = $request->getParameter('page', 1);
		$active = $request->getParameter('active', null);

    $q = Doctrine_Query::create()
      ->select('u.*, p.*')
      ->from('sfGuardUser u')
      ->leftJoin('u.Profile p')
      ->groupBy('u.id');
    
    if ($order = $request->getParameter('order'))
    {
      if ($order == 'created')
      {
        $q->orderBy('u.created_at DESC');
      }
      else if ($order == 'alpha')
      {
        $q->orderBy('u.username ASC');
      }
      else if ($order == 'login')
      {
        $q->orderBy('u.last_login DESC');
      }
      else 
      {
        $q->orderBy('u.last_login DESC');
      }
    }
    else
    {
      $q->orderBy('u.created_at DESC');
    }
    
    if ($start = $request->getParameter('start'))
    {
      $start = date('Y-m-d H:i:s', strtotime($start));
      $q->addWhere('u.created_at > ?', $start);
    }

    if ($end = $request->getParameter('end'))
    {
      $end = date('Y-m-d H:i:s', strtotime($end));
      $q->addWhere('u.created_at < ?', $end);
    }
    
    if ($terms = $request->getParameter('user_search'))
    {
      $terms = explode(' ', $terms);
      foreach($terms as $term)
      {
        $q->andWhere('u.username like ? or p.public_name like ? or p.name_first like ? or p.name_last like ?',array('%' . $term . '%','%' . $term . '%', '%' .$term . '%','%' . $term . '%'));
      }
    }
    
		if (!is_null($active))
		{
			$q->addWhere('u.is_active = ?', $active);
		}
      
    $this->users_pager = new Doctrine_Pager($q, $page, $num);
	}
	
	public function executeSummary($request)
	{
	  $num = $request->getParameter('num', 20);
		$page = $request->getParameter('page', 1);
		$start = null;
		$end = null;
		
		$q = Doctrine_Query::create()
		    ->select('u.*,count(m.id) as mod_count')
		    ->from('sfGuardUser u')
		    ->leftJoin('u.Profile p')
		    ->leftJoin('u.Modification m')
		    ->where('u.is_super_admin = ?', 0)
		    ->groupBy('u.id');
	
	  if ($order = $request->getParameter('order'))
    {
      if ($order == 'modifications')
      {
        $q->orderBy('mod_count DESC');
      }
      else if ($order == 'login')
      {
        $q->orderBy('u.last_login DESC');
      }
    }
    else 
    {
      $q->orderBy('mod_count DESC');
    }
		
	  if ($start = $request->getParameter('start'))
    {
      $start = date('Y-m-d H:i:s', strtotime($start));
      $q->addWhere('m.created_at > ?', $start);
    }

    if ($end = $request->getParameter('end'))
    {
      $end = date('Y-m-d H:i:s', strtotime($end));
      $q->addWhere('m.created_at < ?', $end);
    }
    
    if ($span = $request->getParameter('span'))
    {
      $start = date('Y-m-d H:i:s', time() - $span * 60 * 60);
      $q->addWhere('m.created_at > ?', $start);
    }
    
    if ($username = $request->getParameter('user_search'))
    {
      $terms = explode(' ', $request->getParameter('user_search'));
      foreach($terms as $term)
      {
        $q->andWhere('u.username like ? or p.public_name like ? or p.name_first like ? or p.name_last like ?',array('%' . $term . '%','%' . $term . '%', '%' .$term . '%','%' . $term . '%'));
      }
    }
    
		$this->user_pager = new LsDoctrinePager($q, $page, $num);
		
	
	}
	
	public function executeApprove($request)
	{
    $user = Doctrine::getTable('sfGuardUser')->find($request->getParameter('id'));
		$this->forward404Unless($user);
		

    //give user analyst permissions
    if (!$user->hasCredential('contributor'))
    {
      $user->addPermissionByName('contributor');
    }

    if (!$user->hasCredential('editor'))
    {
      $user->addPermissionByName('editor');
    }


		//make user active
		$user->is_active = true;
		$user->save();


		//send user email notification of approval
    $mailBody = $this->getPartial('accountcreatereceipt', array(
      'user' => $user,
      'is_invited' => false
    ));        
    $message = new Swift_Message('You are now a LittleSis analyst!', $mailBody, 'text/plain');

    $mailer = new Swift(new Swift_Connection_NativeMail());
    $fromAddress = new Swift_Address(sfConfig::get('app_mail_invite_sender_address'), sfConfig::get('app_mail_invite_sender_name'));
    $toAddress = new Swift_Address($user->username, $user->Profile->getName());

    $mailer->send($message, $toAddress, $fromAddress);
    $mailer->disconnect();


		$this->redirect('user/list');
	}

	public function executeInvite($request)
	{
    $this->user_invite_form = new UserInviteForm();

		if ($request->isMethod('post'))
		{
      $userParams = $request->getParameter('user');
      $this->user_invite_form->bind($userParams);

      if ($this->user_invite_form->isValid())
      {
        $db = Doctrine_Manager::connection();
  
        try
        {
          $db->beginTransaction();
  
  
          //create user
          $user = new sfGuardUser();
          $user->username = $userParams['email'];
          $user->algorithm = 'sha1';
          $user->setPassword('pending');
          $user->is_active = false;
          $user->addGroupByName($userParams['group']);

          $user->save();  

          
          //create user profile
          $profile = new sfGuardUserProfile();
          $profile->user_id = $user->id;
          $profile->name_first = $userParams['name_first'];
          $profile->name_last = $userParams['name_last'];
          $profile->email = $userParams['email'];
          $profile->is_visible = false;
          $profile->invitation_code = substr(sha1($profile->getEmail() . time()), 0, 10);
          $profile->save();
    

          //send welcome email
          $mailBody = $this->getPartial($userParams['template'], array(
            'profile' => $profile
          ));

          $mailer = new Swift(new Swift_Connection_NativeMail());
          $message = new Swift_Message("You're invited to become a LittleSis analyst!", $mailBody, 'text/plain');
          $address = new Swift_Address(sfConfig::get('app_mail_invite_sender_address'), sfConfig::get('app_mail_invite_sender_name'));

          $mailer->send($message, $profile->email, $address);
          $mailer->disconnect();

          
          //all's well, commit transaction
          $db->commit();
        }
        catch (Exception $e)
        {
          $db->rollback();
          throw $e;
        }

        $this->redirect('user/list');
      }
      
      //gather errors
      $this->errors = $this->user_invite_form->getErrorSchema()->getErrors();
		}	
	}
	
	
	public function executeDetails($request)
	{
	  $user = Doctrine::getTable('sfGuardUser')->find($request->getParameter('id'));
	  $this->forward404Unless($user);
	  
	  $this->profile = $user->Profile;
	}
}