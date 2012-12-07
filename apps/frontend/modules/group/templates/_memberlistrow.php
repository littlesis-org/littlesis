<tr class="hover">
  <td style="width: 30px;"><?php echo user_pic($object, 'small', array('style' => 'width: 30px; border: 0;')) ?></td>
  <td class="text_big"><?php echo user_link($object) ?></td>
  <td><?php echo $object['sfGuardUserGroup'][0]['is_owner'] ? 'owner' : '' ?></td>
  <td><?php echo $object['Profile']['score'] ?></td>
  <td><?php echo $object['sfGuardUserGroup'][0]['updated_at'] ?></td>
  <td>
  <?php if ($sf_user->hasCredential('admin')) : ?>
    <?php if ($object['sfGuardUserGroup'][0]['is_owner']) : ?>
      <?php echo link_to('unmake owner', $base_object->getInternalUrl('changeOwner', array('user_id' => $object['id'], 'is_owner' => '0')), 'post=true confirm=Are you sure?') ?>
      <br />    
    <?php else : ?>
      <?php echo link_to('make owner', $base_object->getInternalUrl('changeOwner', array('user_id' => $object['id'], 'is_owner' => '1')), 'post=true confirm=Are you sure?') ?>
      <br />
    <?php endif; ?>
  <?php endif; ?>
    <?php echo link_to('remove', $base_object->getInternalUrl('removeUser', array('user_id' => $object['id'])), 'post=true confirm=Are you sure?') ?>
  </td>
</tr>