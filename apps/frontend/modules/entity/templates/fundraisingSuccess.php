<?php include_partial('global/section', array(
  'title' => 'Political Fundraising Committees',
  'pager' => $fundraising_pager,
  'pointer' => 'Orgs that have raised political donations for ' . $entity->name
)) ?>

<div class="padded">
<?php foreach ($fundraising_pager->execute() as $id => $ary) : ?>
  <?php include_partial('entity/entitywithrelationships', array(
    'related_entity' => $ary['entity'],
    'relationships' => $ary['rels'],
    'profiled_entity' => $entity
  )) ?>
<?php endforeach; ?>
</div>