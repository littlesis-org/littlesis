Dear <?php echo $profile->name_first ?>,

You have been invited to join LittleSis, an involuntary facebook for powerful Americans.  LittleSis is collaboratively edited and maintained by people like you.

To register an account and log in, just follow this link:
http://<?php echo sfContext::getInstance()->getRequest()->getHost() ?>/join?code=<?php echo $profile->invitation_code ?>


Thank you,
LittleSis Admin