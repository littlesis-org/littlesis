<?php

class apiActions extends sfActions
{
  static function lastRequestSort($r1, $r2)
  {
    return strcmp($r1['last_request'], $r2['last_request']);
  }

  public function executeUsers($request)
  {
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 50);

    $this->active_users = LsDoctrineQuery::create()
      ->select('u.*, COUNT(r.id) AS num_requests, MAX(r.created_at) AS last_request, COUNT(DISTINCT r.ip_address) AS num_ips')
      ->from('ApiUser u')
      ->leftJoin('u.Request r')
      ->where('r.id IS NOT NULL')
      ->andWhere('r.created_at > ?', date('Y-m-d', strtotime('4 weeks ago')))
      ->orderBy('last_request DESC')
      ->groupBy('u.id')
      ->fetchArray();      

    $this->pending_users = LsDoctrineQuery::create()
      ->from('ApiUser u')
      ->where('u.is_active = 0')
      ->orderBy('u.created_at DESC')
      ->fetchArray();
  }
  
  
  public function executeApproveUser($request)
  {
    if ($request->isMethod('post'))
    {
      if (!$apiUser = Doctrine::getTable('ApiUser')->find($request->getParameter('id')))
      {
        $this->forward404();
      }
      
      $apiUser->is_active = 1;
      $apiUser->save();


      //send approval email
      $mailBody = $this->getPartial('accountcreatenotify', array('user' => $apiUser));

      $mailer = new Swift(new Swift_Connection_NativeMail());
      $message = new Swift_Message('Your LittleSis API key', $mailBody, 'text/plain');
      $from = new Swift_Address(sfConfig::get('app_api_sender_address'), sfConfig::get('app_api_sender_name'));

      $recipients = new Swift_RecipientList;
      $recipients->addTo($apiUser['email'], $apiUser['name_first'] . ' ' . $apiUser['name_last']);  
      $recipients->addBcc(sfConfig::get('app_api_sender_address'));
      
      $mailer->send($message, $recipients, $from);
      $mailer->disconnect();
    }
    
    $this->redirect('api/users');
  }

  public function executeLog($request)
  {
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 100);

    //get data
    $q = LsDoctrineQuery::create()
      ->from('ApiRequest r')
      ->leftJoin('r.ApiUser u')
      ->orderBy('r.created_at DESC')
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);

    if ($userId = $request->getParameter('user_id'))
    {
      $q->where('u.id = ?', $userId);
      
      $this->api_user = Doctrine::getTable('ApiUser')->find($userId);
    }

    $q->limit($num);
    $q->offset(($page - 1) * $num);
    $data = $q->execute();

    //get count
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT COUNT(*) FROM api_request r';
    $stmt = $db->execute($sql);
    $count = $stmt->fetch(PDO::FETCH_COLUMN);

    //make pager
    $this->request_pager = new LsDoctrinePager($data, $page, $num);
    $this->request_pager->isSubsetWithCount(true);
    $this->request_pager->setNumResults($count);
  }
}