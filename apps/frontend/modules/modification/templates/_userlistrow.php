<?php $fields = $object['Field'] ?>


<tr>
  <td><?php echo $object['created_at'] ?></td>
  <td><strong><?php echo ModificationTable::getName($object) ?></strong></td>
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
  <?php $merge_object = LsDoctrineQuery::create()->from('Entity e')->where('e.id = ? AND e.is_deleted IS NOT NULL', $object['merge_object_id'])->fetchOne() ?>
  <td>
    Merged into <?php echo entity_link($merge_object) ?>
  </td>
<?php else : ?>
  <td></td>
<?php endif; ?>  
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
</tr>
<?php endforeach; ?>
<?php endif; ?>