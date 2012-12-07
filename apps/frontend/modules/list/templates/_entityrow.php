  <tr class="hover">
    <td>
      <?php echo entity_link($object->Entity, '') ?>
      <?php if (($object->LsList->is_admin == 0 && $sf_user->hasCredential('editor')) || $sf_user->hasCredential('admin')) : ?>
        <span class="text_small"><?php echo link_to('remove', 'list/removeEntity?id=' . $object->id, 'post=true confirm=Are you sure?') ?></span>
      <?php endif; ?>        
    </td>
    <td>
      <em style="font-size: 12px;"><?php echo excerpt($object->Entity->blurb) ?></em>
    </td>
  </tr>
