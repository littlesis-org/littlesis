<?php 
$rel = Objectable::getObjectByModelAndIdQuery($object['object_model'], $object['object_id'], true)
  ->leftJoin('relationship.Entity1 e1')
  ->leftJoin('relationship.Entity2 e2')
  ->leftJoin('relationship.Category c')
  ->setHydrationMode(Doctrine::HYDRATE_ARRAY)
  ->fetchOne()
?>

<?php //only show modification if relationship wasn't already listed as a merge ?>
<?php if (!in_array($object['object_id'], $holder->get('fromMerges'))) : ?>

<tr>
  <td><?php echo $object['created_at'] ?></td>
  <td><?php echo user_link($object['User']) ?></td>
  <td><?php echo ModificationTable::getType($object) ?></td>
  <td>
    <strong>
    <?php if ($rel) : ?>
      <?php echo link_to(RelationshipTable::getName($rel), RelationshipTable::generateRoute($rel)) ?>
    <?php else: ?>
      <?php echo link_to($object['object_name'], 'relationship/modifications?id=' . $object['object_id']) ?>
    <?php endif; ?>
    </strong>
  </td>
</tr>

<?php //if modification is merge, add the relationship ID to fromMerges so we can skip its previous modifications ?>
<?php if ($object['is_merge']) : ?>
  <?php $fromMerges = $holder->get('fromMerges') ?>
  <?php $fromMerges[] = $object['object_id'] ?>
  <?php $holder->set('fromMerges', $fromMerges) ?>
<?php endif; ?>

<?php endif; ?>