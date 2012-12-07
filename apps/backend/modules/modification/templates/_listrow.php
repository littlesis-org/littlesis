<?php $fields = $object->Field ?>


<tr>
  <td><?php echo $object->created_at ?></td>
  <td><?php echo link_to($object->User->getProfile()->getPublicName(), 'modification/list?user_id=' . $object->user_id) ?></td>
  <td><strong><?php echo link_to($object->getName(), 'modification/list?model=' . $object->object_model . '&id=' . $object->object_id) ?></strong>
  <?php if (in_array($object->object_model,array('Entity','Relationship','Address','Image')) && $record = Doctrine::getTable($object->object_model)->find($object->object_id)) : ?>
     [<a href="<?php echo '../../' . strtolower($object->object_model) . DIRECTORY_SEPARATOR . 'view?id=' . $object->object_id ?>">page</a>]
  <?php endif; ?>  
  </td>
  <td><?php echo $object->object_model ?></td>
  <td><em><?php echo $object->getType() ?></em></td>

<?php if (!$object->is_delete && count($fields) && $field = $fields->remove(0)) : ?>
  <td>
    <strong><?php echo $field->getFieldNameForDisplay() ?></strong> (<?php echo $field->getOldValueForDisplay() ?> &rarr; <?php echo $field->getNewValueForDisplay() ?>)
  </td>
<?php elseif ($object->merge_object_id) : ?>
  <td>
    <?php if ($entity = Doctrine::getTable('Entity')->find($object->merge_object_id)) : ?>
      <?php echo 'Merged into ' . entity_link($entity) ?>
    <?php endif; ?>
  </td>
<?php else : ?>
  <td></td>
<?php endif; ?>
  
  <td>
    <?php echo link_to('limit by user', 'modification/list?user_id=' . $object->user_id) ?>
  </td>
</tr>

<?php if (!$object->is_delete) : ?>
<?php foreach ($fields as $field) : ?>
<tr>
  <td></td>
  <td></td>
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