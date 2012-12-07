<?php

class SendScheduledEmailsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'email';
    $this->name             = 'send-scheduled';
    $this->briefDescription = 'sends scheduled emails and marks them as sent';
    $this->detailedDescription = <<<EOF
This task is for generating sending emails from the scheduled_email table.
EOF;
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'How many emails to send at once', 20);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      


    $db = Doctrine_Manager::connection();

    
    //get scheduled emails
    $q = LsDoctrineQuery::create()
      ->from('ScheduledEmail se')
      ->where('se.is_sent = ?', false)
      ->limit($options['limit'])
      ->orderBy('se.id');

    foreach ($q->execute() as $email)
    {
      $mailer = new Swift(new Swift_Connection_NativeMail());
      if ($email->body_html)
      {
        $message = new Swift_Message($email->subject);
        $message->attach(new Swift_Message_Part($email->body_text));
        $message->attach(new Swift_Message_Part($email->body_html, 'text/html'));
      }
      else
      {
        $message = new Swift_Message($email->subject, $email->body_text, 'text/plain');      
      }
      $from_address = new Swift_Address($email->from_email, $email->from_name);
      $to_address = new Swift_Address($email->to_email, $email->to_name);

      if ($sent = $mailer->send($message, $to_address, $from_address))
      {
        $email->is_sent = true;
        $email->save();

        echo "Successfully sent scheduled email #" . $email->id . "\n";
        echo "  From: " . $email->from_email . "\n";
        echo "  To: " . $email->to_email . "\n";
        echo "  Subject: " . $email->subject . "\n";
        echo "\n";
      }
      else
      {
        echo "Error sending scheduled email #" . $email->id . "\n";
        echo "\n";
      }

      $mailer->disconnect();    
    }
    
    
    //DONE
    LsCli::beep();
  }
}