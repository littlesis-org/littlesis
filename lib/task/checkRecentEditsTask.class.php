<?php

class checkRecentEditsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'check';
    $this->name             = 'recentEdits';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [check:recentEdits|INFO] task does things.
Call it with:

  [php symfony check:recentEdits|INFO]
EOF;
    // add arguments here, like the following:
    //$this->addArgument('application', sfCommandArgument::REQUIRED, 'The application name');
    // add options here, like the following:
    //$this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
        
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Test mode', false);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
    $this->addOption('minutes', null, sfCommandOption::PARAMETER_REQUIRED, 'minutes', 10);
    $this->addOption('send_email', null, sfCommandOption::PARAMETER_REQUIRED, 'send_email', 0);
  }

  protected function execute($arguments = array(), $options = array())
  {
    // add code here
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);
    $minutes = $options['minutes'];
    $time_date_to_check = date('Y-m-d H:i:s',time() - (60 * $minutes));
    $mods = LsDoctrineQuery::create()
        ->select('m.user_id,count(m.id)')
        ->from('Modification m')
        ->where('m.created_at > ? and m.user_id > ?', array($time_date_to_check, 2))
        ->groupBy('m.user_id')
        ->execute();
        
    $message_arr = array('spikes' => array(), 'new' => array());
    $new_users = array();
    $spike_users = array();
    foreach($mods as $mod)
    {
      $previous_mods_count = LsDoctrineQuery::create()
        ->select('m.id')
        ->from('Modification m')
        ->where('m.created_at < ? and m.user_id = ?', array($time_date_to_check, $mod->user_id))
        ->count();
      if ($previous_mods_count == 0)
      {
        $user = Doctrine::getTable('sfGuardUser')->find($mod->user_id);
        $new_users[] = $user->username . '(' . $mod->count . ')';;
        $message_arr['new'][] = $user->Profile->public_name . " / " . $user->username . " / "  . 'http://littlesis.org/user/modifications?id=' . $user->id . "\n\t$mod->count modifications in last $minutes";
      }
      else if ($mod->count > $previous_mods_count * .1)
      {      
        $user = Doctrine::getTable('sfGuardUser')->find($mod->user_id);
        $spike_users[] = $user->username . '(' . $mod->count . ')';
        $message_arr['spikes'][] = $user->Profile->public_name . " / " . $user->username . " / "  . 'http://littlesis.org/user/modifications?id=' . $user->id . "\n\t$mod->count modifications in last $minutes minutes vs. previous count of $previous_mods_count modifications";
      }
    }
    $message = '';
    if (count($message_arr['new']))
    {
      $message = "New editing:\n\n";
      $message .= implode("\n\n",$message_arr['new']);
    }
    if (count($message_arr['spikes']))
    {
      if (strlen($message))
      {
        $message .= "\n\n";
      }
      $message .= "Spike in editing:\n\n";
      $message .= implode("\n\n",$message_arr['spikes']);      
    }
    $short = "n:" . implode("/",$new_users) . "|s:" . implode("/",$spike_users);    
    
    if (strlen($message))
    {
      $subject = count($message_arr['spikes']) . ' spikes & ' . count($message_arr['new']) . ' new -- ' . date('m-d H:i', time());  
      $mailer = new Swift(new Swift_Connection_NativeMail());
      $message = new Swift_Message($subject, $message, 'text/plain');
      $short = new Swift_Message('',$short, 'text/plain');
      $address = new Swift_Address(sfConfig::get('app_mail_contact_sender_address'), sfConfig::get('app_mail_contact_sender_name'));
    
      $mailer->send($message, sfConfig::get('app_mail_contact_recipient_address'), $address);
      $mailer->send($short, '7189160925@txt.att.net', $address);
      $mailer->send($short, '6464682400@txt.att.net', $address);
      $mailer->disconnect();
    }
  }
}