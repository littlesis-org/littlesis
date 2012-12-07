<?php $obj = $object->getObject() ?>
<tr>
  <td>
    <?php echo user_link($object->User) ?>
    <?php echo time_ago_in_words(strtotime($object->created_at)) ?> ago
  </td>
  <td><?php echo object_link($obj) ?></td>
  <td>
    <?php echo link_to($object->title, $obj->getInternalUrl('comments') . '#' . $object->id) ?>
  </td>
</tr>