<?php slot('header_text', 'Change Password') ?>
<?php slot('rightcol', null) ?>

<?php if ($changed) : ?>

You have successfully changed your password.  Return to 
<?php echo link_to('your account','home/account') ?>.

<?php else : ?>
Enter your current password, then your new password twice.  
<br />
Your password must be 6-20 characters and can only contain letters and numbers.
<br />
<br />

<?php include_partial('global/formerrors', array('form' => $password_form)) ?>

<form action="<?php echo url_for('home/changePassword') ?>" method="POST">
<table>
  <?php echo input_hidden_tag('change_password[username]', $sf_user->getGuardUser()->username) ?>
  <?php include_partial('global/form', array(
    'form' => $password_form,
    'show_required' => false
  )) ?>
  <tr>
    <td></td>
    <td>
      <?php echo submit_tag('Change Password') ?>
    </td>
  </tr>
</table>
</form>

<?php endif; ?>