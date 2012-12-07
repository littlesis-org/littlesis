<?php $fields = $object->Field ?>


<tr>
  <td><?php echo $object->created_at ?></td>
  <td><?php echo user_link($object->User) ?></td>
  <td>
    <?php $contactObject = $object->getObject() ?>
    <?php echo get_class($contactObject) ?> (id# <?php echo $contactObject->id ?>)
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
</tr>

<?php if (!$object->is_delete) : ?>
<?php foreach ($fields as $field) : ?>
<tr>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td>
    <strong><?php echo $field->getFieldNameForDisplay() ?></strong> (<?php echo $field->getOldValueForDisplay() ?> &rarr; <?php echo $field->getNewValueForDisplay() ?>)
  </td>
</tr>
<?php endforeach; ?>
<?php endif; ?>