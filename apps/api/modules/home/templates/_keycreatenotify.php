Hi <?php echo $user['name_first'] ?>,

Here's your LittleSis API key:

<?php echo $user['api_key'] . "\n" ?>

Feel free to be in touch if you encounter any problems!

<?php echo sfConfig::get('app_mail_sender_name') ?>

<?php echo sfConfig::get('app_mail_sender_address') ?>