<?php slot('header_text', 'Reset Password') ?>
<?php slot('rightcol', null) ?>

<?php if ($reset) : ?>

Your password has been reset and sent to <?php echo $profile->email ?>.

<?php else : ?>

Enter your email address and name below to request a new password. A new password will be sent to your email address.
<br />
<br />

<?php include_partial('global/formerrors', array('form' => $password_form)) ?>

<form action="<?php echo url_for('home/resetPassword') ?>" method="POST">
<table>
  <?php include_partial('global/form', array('form' => $password_form)) ?>
  <tr>
    <td></td>
    <td>
      <?php echo submit_tag('Reset Password') ?>
    </td>
  </tr>
</table>
</form>

<?php endif; ?>