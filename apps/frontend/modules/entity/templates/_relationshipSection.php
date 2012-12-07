<?php if (isset($pager)) : ?>

<?php include_partial('global/subsection', array(
  'title' => $title,
  'pager' => $pager,
  'more' => isset($more_action) ? EntityTable::getInternalUrl($entity, $more_action) : null,
  'pointer' => isset($pointer) ? $pointer : null,
  'actions' => isset($actions) ? $actions : null
)) ?>

<div class="relationship-section">

<?php $ids = array(); ?>
<?php foreach ($pager->execute() as $id => $ary) : ?>
  <?php $ids[$id] = isset($ids[$id]) ? ($ids[$id] + 1) : 1 ?>
  <?php include_partial('entity/entitywithrelationships', array(
    'related_entity' => $ary,
    'relationships' => $ary['Relationships'],
    'profiled_entity' => $entity,
    'repeat' => $ids[$id]
  )) ?>
<?php endforeach; ?>
</div>

<?php endif; ?>