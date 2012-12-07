<?php include_partial('global/section', array(
  'title' => 'Donors',
  'pager' => $donor_pager,
  'pointer' => 'People and orgs who have donated to ' . $entity->name  . 'directly',  
)) ?>

<div class="padded">
<?php foreach ($donor_pager->execute() as $id => $ary) : ?>
  <?php include_partial('entity/entitywithrelationships', array(
    'related_entity' => $ary['entity'],
    'relationships' => $ary['rels'],
    'profiled_entity' => $entity
  )) ?>
<?php endforeach; ?>
</div>