<?php slot('header_text', 'Invite a User') ?>

<?php echo link_to('Active', 'user/list?active=1') ?>&nbsp;
<?php echo link_to('Inactive', 'user/list?active=0') ?>&nbsp;
<?php echo link_to('All', 'user/list') ?>&nbsp;
<?php echo link_to('Invite', 'user/invite') ?>&nbsp;
<?php echo link_to('Editing Summary', 'user/summary') ?>

<?php if (isset($errors)) : ?>
  <?php include_partial('global/formerrors', array('form' => $user_invite_form)) ?>
<?php endif; ?>

<form action="<?php echo url_for('user/invite') ?>" method="POST">
  <table>  
    <?php include_partial('global/form', array('form' => $user_invite_form)) ?>
    <tr>
      <td></td>
      <td class="form_submit">
        <input type="submit" value="Invite" />
      </td>
    </tr>
  </table>
</form>