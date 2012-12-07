<?php if (!$entity = Doctrine::getTable('Entity')->find($base_object[$object['object_id']])) : ?>
  <?php $entity = EntityTable::getDeletedById($entityId, Doctrine::HYDRATE_ARRAY) ?>
<?php endif; ?>

<tr>
  <td><?php echo $object['created_at'] ?></td>
  <td><?php echo user_link($object['User']) ?></td>
  <td><?php echo $object['is_create'] ? 'add' : 'remove' ?></td>
  <td>
    <?php if ($entity['merged_id'] && $merged = Doctrine::getTable('Entity')->find($entity['merged_id'])) : ?>
      <strong><?php echo $entity['name'] ?></strong> (merged into <?php echo entity_link($merged, null) ?>)
    <?php elseif ($entity['is_deleted']) : ?>
      <strong> . <?php echo $entity['name'] ?> . </strong> [DELETED]
    <?php else : ?>
      <?php echo entity_link($entity, null) ?>
    <?php endif; ?>
  </td>
</tr>