<?php include_partial('global/section', array(
  'title' => 'Owners',
  'pager' => $owner_pager,
  'pointer' => 'People and orgs with ownership in ' . $entity->name
)) ?>

<div class="padded">
<?php foreach ($owner_pager->execute() as $id => $ary) : ?>
  <?php include_partial('entity/entitywithrelationships', array(
    'related_entity' => $ary['entity'],
    'relationships' => $ary['rels'],
    'profiled_entity' => $entity
  )) ?>
<?php endforeach; ?>
</div>