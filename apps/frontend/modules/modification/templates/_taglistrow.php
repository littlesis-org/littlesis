<?php $tagIdField = $object->is_create ? 'new_value' : 'old_value' ?>
<?php $tag = Doctrine::getTable('Tag')->find($object->Field[0]->$tagIdField) ?>

<tr>
  <td><?php echo $object->created_at ?></td>
  <td><?php echo user_link($object->User) ?></td>
  <td><?php echo $object->is_create ? 'add' : 'remove' ?></td>
  <td><strong><?php echo link_to($tag->getName(), 'tag/view?name='. $tag->getName()) ?></strong></td>
</tr>