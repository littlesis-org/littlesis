<?php include_partial('global/section', array(
  'title' => 'Friends',
  'pager' => $friend_pager,
  'pointer' => 'People with close social ties to ' . $entity->name
)) ?>

<div class="padded">
<?php foreach ($friend_pager->execute() as $id => $ary) : ?>
  <?php include_partial('entity/entitywithrelationships', array(
    'related_entity' => $ary['entity'],
    'relationships' => $ary['rels'],
    'profiled_entity' => $entity
  )) ?>
<?php endforeach; ?>
</div>