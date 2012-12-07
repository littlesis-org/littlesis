<tr>
  <td><?php echo $object->Profile->getName() ?></td>

  <td>
    <?php if ($object->Profile->public_name) : ?>
      <?php echo link_to($object->Profile->public_name, frontend_base() . '/user/profile?id=' . $object->id) ?>
    <?php endif; ?>
  </td>
  <td>
    <?php echo $object->username ?>   
  </td>
  <td><?php echo $object->created_at ?></td>
  <td><?php echo $object->is_active ? 'Yes' : 'No' ?></td>
  <td><?php echo $object->last_login ?></td>
  <td>
    <?php echo link_to('details', 'user/details?id=' . $object->id) ?>&nbsp;

    <?php echo link_to('edit', 'sfGuardUser/edit?id=' . $object->id) ?>&nbsp;

    <?php if (!$object->is_active && !$object->Profile->invitation_code) : ?>
      <?php echo link_to('approve', 'user/approve?id=' . $object->id, 'post=true') ?>
    <?php endif; ?>
        
    <a href="mailto:<?php echo $object->username ?>?subject=littlesis" ">email</a>&nbsp;
    
    <a href="http://littlesis.org/home/notes?compose=1&user_id=<?php echo $object->id ?>">note</a>
    
  </td>
    
</tr>
