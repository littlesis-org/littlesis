<?php slot('header_text', "You're all set!") ?>

<span class="text_big">
Your email address (<strong><?php echo $profile->email ?></strong>) has been confirmed. 
<br />
<br />
You may now <strong><?php echo link_to('login', '@sf_guard_signin') ?></strong> using the email and password you provided when you signed up.
</span>