<?php include_partial('global/section', array(
  'title' => 'Leadership & Staff',
  'pager' => $position_pager,
  'pointer' => 'People who have official positions in ' . $entity->name
)) ?>

<div class="padded">
<?php foreach ($position_pager->execute() as $id => $ary) : ?>
  <?php include_partial('entity/entitywithrelationships', array(
    'related_entity' => $ary['entity'],
    'relationships' => $ary['rels'],
    'profiled_entity' => $entity
  )) ?>
<?php endforeach; ?>
</div>