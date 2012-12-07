<?php slot('header_text', 'Forbidden') ?>

<div class="form_errors">
<?php echo image_tag(
  'system' . DIRECTORY_SEPARATOR . 'error.png',
	'align=top'
) ?> You don't have the proper credentials to perform this action. 

<?php if (!$sf_user->isAuthenticated()) : ?>
  You might need to be logged in. <?php echo link_to('Login', '@sf_guard_signin') ?> or <?php echo link_to('sign up', '@join') ?> and try again. 
<?php endif; ?>

If you believe this is a mistake, <?php echo link_to('contact', '@contact') ?> the site administrator.
</div>

<?php include_partial('error/whatsnext') ?>