<?php include_partial('global/section', array(
  'title' => 'Business Positions',
  'pager' => $business_pager,
  'pointer' => 'Companies ' . $entity->name . ' has had a position in'
)) ?>

<div class="padded">
<?php foreach ($business_pager->execute() as $id => $ary) : ?>
  <?php include_partial('entity/entitywithrelationships', array(
    'related_entity' => $ary['entity'],
    'relationships' => $ary['rels'],
    'profiled_entity' => $entity
  )) ?>
<?php endforeach; ?>
</div>