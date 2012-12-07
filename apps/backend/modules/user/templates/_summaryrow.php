
<?php $user = Doctrine::getTable('sfGuardUser')->find($object['id']) ?>

<tr>
  <td><?php echo $user->Profile->getName() ?></td>
  <td>
    <?php if ($user->Profile->public_name) : ?>
      <?php echo link_to($user->Profile->public_name, frontend_base() . 'user/'. $user->Profile->public_name . '/modifications') ?></td>
    <?php endif; ?>
  </td>
  <td><?php echo $user->username ?></td>
  <td><?php echo $user->last_login ?></td>
  <td>
    <?php echo $object['mod_count'] ?>
  </td>
</tr>
