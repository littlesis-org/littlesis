First name: <?php echo $user->Profile->name_first ?>

Last name: <?php echo $user->Profile->name_last ?>

Email: <?php echo $user->Profile->email ?>

Public name: <?php echo $user->Profile->public_name ?>


How they heard about LittleSis: 

<?php echo $user->Profile->reason ?>


Why they want to be an analyst: 

<?php echo $user->Profile->analyst_reason ?>


Date: <?php echo date('r') ?>