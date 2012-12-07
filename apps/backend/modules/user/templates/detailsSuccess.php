<?php slot('header_text', $profile->public_name) ?>

<strong>Name:</strong> <?php echo $profile->name_first ?> <?php echo $profile->name_last ?>
<br />
<strong>Email:</strong> <?php echo $profile->email ?>
<br />
<br />


<strong>How they heard about LittleSis:</strong><br />
<?php echo $profile->reason ?>
<br />
<br />


<strong>Why they want to be an analyst:</strong><br />
<?php echo $profile->analyst_reason ?>
<br />
<br />