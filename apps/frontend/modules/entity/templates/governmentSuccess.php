<?php include_partial('global/section', array(
  'title' => 'Government Positions',
  'pager' => $govt_pager,
  'pointer' => 'Govt agencies ' . $entity->name . ' has served in'
)) ?>

<div class="padded">
<?php foreach ($govt_pager->execute() as $id => $ary) : ?>
  <?php include_partial('entity/entitywithrelationships', array(
    'related_entity' => $ary['entity'],
    'relationships' => $ary['rels'],
    'profiled_entity' => $entity
  )) ?>
<?php endforeach; ?>
</div>