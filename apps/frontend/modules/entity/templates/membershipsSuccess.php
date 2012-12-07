<?php include_partial('global/section', array(
  'title' => 'Memberships',
  'pager' => $membership_pager,
  'pointer' => $entity->name . ' is a member of these organizations'
)) ?>

<div class="padded">
<?php foreach ($membership_pager->execute() as $id => $ary) : ?>
  <?php include_partial('entity/entitywithrelationships', array(
    'related_entity' => $ary['entity'],
    'relationships' => $ary['rels'],
    'profiled_entity' => $entity
  )) ?>
<?php endforeach; ?>
</div>