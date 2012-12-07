<?php include_partial('global/section', array(
  'title' => 'In the Office Of',
  'pager' => $office_pager,
  'pointer' => 'People ' . $entity->name . ' has worked for directly'
)) ?>

<div class="padded">
<?php foreach ($office_pager->execute() as $id => $ary) : ?>
  <?php include_partial('entity/entitywithrelationships', array(
    'related_entity' => $ary['entity'],
    'relationships' => $ary['rels'],
    'profiled_entity' => $entity
  )) ?>
<?php endforeach; ?>
</div>