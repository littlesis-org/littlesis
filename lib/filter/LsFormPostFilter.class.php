<?php

class LsFormPostFilter extends sfFilter
{
	public function execute($filterChain)
	{
    $request = $this->getContext()->getRequest();    
    $sessionUser = $this->getContext()->getUser();

    if ($sessionUser->isAuthenticated() && !$sessionUser->hasCredential('admin') && !$sessionUser->hasCredential('deleter') && $request->isMethod('post') && $request->getParameter('action') != 'descriptions')
    {
      $user = $sessionUser->getGuardUser();
      $params = array_diff_key($request->getParameterHolder()->getAll(), array('module' => null, 'action' => null));      

      //log form post
      $post = new UserFormPost;
      $post->User = $user;
      $post->module = $request->getParameter('module');
      $post->action = $request->getParameter('action');
      $post->params = in_array($request->getParameter('action'), array('signin', 'join', 'changePassword')) ? null : http_build_query($params);
      $post->save();


      //check for badness
      $banned = false;
      $fiveMinutesAgo = date('Y-m-d H:i:s', time() - 5*60);

      $q = LsDoctrineQuery::create()
        ->from('UserFormPost p')
        ->where('p.user_id = ? AND p.created_at > ?', array($user->id, $fiveMinutesAgo))
        ->orderBy('p.created_at DESC');

      if ($q->count() > 40)
      {
        //BAD! logout and de-activate user
        $sessionUser->setAuthenticated(false);
        
        $user->is_active = false;
        $user->save();
        
        $banned = true;
      }

      if ($q->count() > 20)
      {
        //SUSPICIOUS! notify admins
        sfLoader::loadHelpers('Partial');
        $mailBody = get_partial('home/formpostnotify', array(
          'user' => $user,
          'posts' => $q->execute(),
          'banned' => $banned
        ));

        $mailer = new Swift(new Swift_Connection_NativeMail());
        $message = new Swift_Message('Suspicious user activity', $mailBody, 'text/plain');
        $address = new Swift_Address(sfConfig::get('app_mail_alert_sender_address'), sfConfig::get('app_mail_alert_sender_name'));
  
        $mailer->send($message, sfConfig::get('app_mail_alert_recipient_address'), $address);
        $mailer->disconnect();
        
        $this->sent = true;
      }
    }
			
		$filterChain->execute();
	}
}

?>