<?php include_partial('global/section', array(
  'title' => 'Other Positions & Memberships',
  'pager' => $position_pager,
  'pointer' => 'Positions & memberships ' . $entity->name . ' has had outside of business & govt agencies'
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