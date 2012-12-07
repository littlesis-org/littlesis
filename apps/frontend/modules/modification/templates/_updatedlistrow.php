<?php $fields = $object->Field ?>


<tr>
  <td>
    <?php echo user_link($object->User) ?> 
    <?php echo time_ago_in_words(strtotime($object->created_at)) ?>
    ago
  </td>  
  <td>
    <?php if ($obj = $object->getObject()) : ?>
      <?php echo object_link($obj) ?>
    <?php else : ?>
      <strong><?php echo $object->object_name ?></strong>
    <?php endif; ?>
  </td>
  <td><em><?php echo $object->getType() ?></em></td>

<?php if (!$object->is_delete && count($fields) && $field = $fields->remove(0)) : ?>
  <td>
    <strong><?php echo $field->getFieldNameForDisplay() ?></strong> (<?php echo $field->getOldValueForDisplay() ?> &rarr; <?php echo $field->getNewValueForDisplay() ?>)
  </td>
<?php elseif ($object->merge_object_id) : ?>
  <td>
    Merged into <?php echo entity_link(Doctrine::getTable('Entity')->find($object->merge_object_id)) ?>
  </td>
<?php else : ?>
  <td></td>
<?php endif; ?>
  
  <td>
    <?php echo link_to('more changes', 'modification/list?user_id=' . $object->user_id) ?>
  </td>
</tr>

<?php if (!$object->is_delete) : ?>
<?php foreach ($fields as $field) : ?>
<tr>
  <td></td>
  <td></td>
  <td></td>
  <td>
    <strong><?php echo $field->getFieldNameForDisplay() ?></strong> (<?php echo $field->getOldValueForDisplay() ?> &rarr; <?php echo $field->getNewValueForDisplay() ?>)
  </td>
  </td></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>