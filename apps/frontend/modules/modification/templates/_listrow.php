<?php $fields = $object['Field'] ?>

<tr>
  <td><?php echo $object['created_at'] ?></td>
  <td><?php echo user_link($object['User']) ?></td>
  <td><em><?php echo ModificationTable::getType($object) ?></em></td>

<?php if (!$object['is_delete'] && count($fields) && $field = array_shift($fields)) : ?>
  <?php $field['Modification'] = $object ?>
  <td>
    <strong><?php echo ModificationFieldTable::getFieldNameForDisplay($field) ?></strong> 
    (<?php echo ModificationFieldTable::convertValueForDisplay($field['old_value'], $field) ?>
    &rarr; 
    <?php echo ModificationFieldTable::convertValueForDisplay($field['new_value'], $field) ?>)
  </td>
<?php elseif ($object['merge_object_id']) : ?>
  <td>
    Merged into <?php echo entity_link(Doctrine::getTable('Entity')->find($object['merge_object_id'])) ?>
  </td>
<?php else : ?>
  <td></td>
<?php endif; ?>

  <td>
    <?php echo link_to('limit by user', $sf_request->getParameter('module') . '/modifications?id=' . $sf_request->getParameter('id') . '&user_id=' . $object['user_id']) ?>
  </td>
</tr>

<?php if (!$object['is_delete']) : ?>
<?php foreach ($fields as $field) : ?>
<?php $field['Modification'] = $object ?>
<tr>
  <td></td>
  <td></td>
  <td></td>
  <td>
    <strong><?php echo ModificationFieldTable::getFieldNameForDisplay($field) ?></strong> 
    (<?php echo ModificationFieldTable::convertValueForDisplay($field['old_value'], $field) ?>
    &rarr; 
    <?php echo ModificationFieldTable::convertValueForDisplay($field['new_value'], $field) ?>)
  </td>
  </td></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>