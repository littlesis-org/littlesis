<?php

class homeActions extends sfActions
{
  public function preExecute()
  {
    $this->setLayout('layout');
  }


  public function executeIndex($request)
  {
    return sfView::SUCCESS;
  }
  
  
  public function executeRegister($request)
  {
    $userParams = $request->getParameter('api_user');
    $this->user_form = new ApiUserForm;
    $this->created = false;

    if ($request->isMethod('post'))
    {
      //bind request params to form
      $captcha = array(
        'recaptcha_challenge_field' => $request->getParameter('recaptcha_challenge_field'),
        'recaptcha_response_field'  => $request->getParameter('recaptcha_response_field'),
      );
      $userParams = array_merge($userParams, array('captcha' => $captcha));
		  $this->user_form->bind($userParams);    

      //look for user with duplicate email
      $q = LsDoctrineQuery::create()
        ->from('ApiUser u')
        ->where('u.email = ?', $userParams['email']);        

      if ($q->count()) 
      {
        $validator = new sfValidatorString(array(), array(
          'invalid' => 'There is already an API user with that email address.'
        ));
        $this->user_form->getErrorSchema()->addError(new sfValidatorError($validator, 'invalid'), 'email');

        $request->setError('email', 'There is already a user with that email');
      }
      
      if ($this->user_form->isValid() && !$request->hasErrors())
      {
        //create inactive api user 
        $user = new ApiUser;
        $user->name_first = $userParams['name_first'];
        $user->name_last = $userParams['name_last'];
        $user->email = $userParams['email'];
        $user->reason = $userParams['reason'];
        $user->api_key = $user->generateKey();
        $user->is_active = 1;
        $user->save();

        //add admin notification email to queue
        $email = new ScheduledEmail;
        $email->from_name = sfConfig::get('app_mail_sender_name');
        $email->from_email = sfConfig::get('app_mail_sender_address');
        $email->to_name = sfConfig::get('app_mail_sender_name');
        $email->to_email = sfConfig::get('app_mail_sender_address');
        $email->subject = sprintf("%s (%s) has requested an API key", $user->getFullName(), $user->email);
        $email->body_text = $this->getPartial('keyrequestnotify', array(
          'user' => $user
        ));            
        $email->save();

        $this->created = true;


        //send approval email        
        $mailBody = $this->getPartial('keycreatenotify', array('user' => $user));
  
        $mailer = new Swift(new Swift_Connection_NativeMail());
        $message = new Swift_Message('Your LittleSis API key', $mailBody, 'text/plain');
        $from = new Swift_Address(sfConfig::get('app_mail_sender_address'), sfConfig::get('app_mail_sender_name'));
  
        $recipients = new Swift_RecipientList;
        $recipients->addTo($user->email, $user->name_first . ' ' . $user->name_last);  
        $recipients->addBcc(sfConfig::get('app_mail_sender_address'));
        
        $mailer->send($message, $recipients, $from);
        $mailer->disconnect();
      }
    }
  }
  
  
  public function executeDocumentation($request)
  {
    $browser = new sfWebBrowser();
    $this->prefix = $request->getUriPrefix();

    if ($extraPrefix = $request->getParameter('prefix'))
    {
      $this->prefix .= $extraPrefix;
    }

    if (!$apiKey = sfConfig::get('app_documentation_admin_key'))
    {
      return $this->renderText("Something's broken! Can't display API documentation.");
    }
    
    $orgId = 1;
    $personId = 1164;
    $relationshipId = 23;
    $listId = 23;

    $this->uris = array(
      'entity_basic'            => $this->prefix . '/entity/' . $orgId . '.xml',
      'entity_details'          => $this->prefix . '/entity/' . $orgId . '/details.xml',
      'entity_batch'            => $this->prefix . '/batch/entities.xml?ids=1,2,3,1201,28219,35306&details=1',
      'entity_aliases'          => $this->prefix . '/entity/' . $orgId . '/aliases.xml',
      'entity_relationships'    => $this->prefix . '/entity/' . $orgId . '/relationships.xml?cat_ids=1,7',
      'entity_related'          => $this->prefix . '/entity/' . $orgId . '/related.xml?cat_ids=1&order=2&is_current=1',
      'entity_leadership'       => $this->prefix . '/entity/' . $orgId . '/leadership.xml?is_current=1',
      'entity_orgs'             => $this->prefix . '/entity/' . $personId . '/orgs.xml',
      'entity_degree2'          => $this->prefix . '/entity/' . $orgId . '/related/degree2.xml?cat1_ids=1&cat2_ids=1&order1=2&order2=1',
      //'entity_leaders_degree2'  => $this->prefix . '/entity/' . $orgId . '/leadership/degree2.xml',
      'entity_lists'            => $this->prefix . '/entity/' . $orgId . '/lists.xml',
      'entity_childorgs'        => $this->prefix . '/entity/' . $orgId . '/child-orgs.xml',
      'entity_images'           => $this->prefix . '/entity/' . $orgId . '/images.xml',
      'entity_references'       => $this->prefix . '/entity/' . $orgId . '/references.xml',
      'entity_rel_references'   => $this->prefix . '/entity/' . $orgId . '/relationships/references.xml?cat_ids=1,7',
      'relationship_basic'      => $this->prefix . '/relationship/' . $relationshipId . '.xml',
      'relationship_details'    => $this->prefix . '/relationship/' . $relationshipId . '/details.xml',
      'relationship_batch'      => $this->prefix . '/batch/relationships.xml?ids=' . implode(',', range(74,95)) . '&details=1',
      'relationship_references' => $this->prefix . '/relationship/' . $relationshipId . '/references.xml',
      'list_basic'              => $this->prefix . '/list/' . $listId . '.xml',
      'list_entities'           => $this->prefix . '/list/' . $listId . '/entities.xml?type_ids=29',
      'entity_search'           => $this->prefix . '/entities.xml?q=treasury&type_ids=35',
      'entity_lookup'           => $this->prefix . '/entities/bioguide_id/O000167.xml',
      'entity_chains'           => $this->prefix . '/entities/chains/1;2.xml',
      'relationship_search'     => $this->prefix . '/relationships/1026;1.xml?cat_ids=1',
      'list_search'             => $this->prefix . '/lists.xml?q=forbes'
    );
    $this->responses = array();
    
    foreach ($this->uris as $key => $uri)
    {
      $uri = $this->addKeyToUri($uri, $apiKey);

      if ($browser->get($uri)->responseIsError())
      {
        throw new Exception("Couldn't get example URI: " . $uri);
      }

      $text = $browser->getResponseText();
      $this->responses[$key] = LsDataFormat::formatXmlString($text);
    }
  }
  
  
  public function addKeyToUri($uri, $key)
  {
    return $uri . (strstr($uri, '?') ? '&' : '?') . '_key=' . $key;
  }
}
