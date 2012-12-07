<?php slot('header_text', 'Confirm Email') ?>


<div class="form_errors">
<?php echo image_tag(
  'system' . DIRECTORY_SEPARATOR . 'error.png',
  'align=top'
) ?> This email confirmation URL is invalid.  You may have already confirmed your email -- try logging in using the form below.
</div>


<br /><br />


<?php include_partial('global/formerrors', array('form' => $form)) ?>

<?php include_partial('global/login', array('form' => $form)) ?>


<script>
document.getElementById('signin_username').focus();
</script>