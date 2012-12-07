First name: <?php echo $user->Profile->name_first ?>

Last name: <?php echo $user->Profile->name_last ?>

Email: <?php echo $user->Profile->email ?>

Public name: <?php echo $user->Profile->public_name ?>


Who they are, and why they signed up:  

<?php echo $user->Profile->reason ?> 

<?php if ($group) : ?>

 User wants to join group: <?php echo $group ?>
<?php endif; ?>


Date: <?php echo date('r') ?>