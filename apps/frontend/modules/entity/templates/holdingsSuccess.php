<?php include_partial('global/section', array(
  'title' => 'Holdings',
  'pager' => $holding_pager,
  'pointer' => 'Orgs that ' . $entity->name . ' owns at least a piece of'
)) ?>

<div class="padded">
<?php foreach ($holding_pager->execute() as $id => $ary) : ?>
  <?php include_partial('entity/entitywithrelationships', array(
    'related_entity' => $ary['entity'],
    'relationships' => $ary['rels'],
    'profiled_entity' => $entity
  )) ?>
<?php endforeach; ?>
</div>