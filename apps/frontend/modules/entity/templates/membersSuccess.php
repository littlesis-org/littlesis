<?php include_partial('global/section', array(
  'title' => 'Members',
  'pager' => $member_pager,
  'pointer' => 'Members of ' . $entity['name'] . ' without official positions'
)) ?>

<div class="padded">
<?php foreach ($member_pager->execute() as $id => $ary) : ?>
  <?php include_partial('entity/entitywithrelationships', array(
    'related_entity' => $ary['entity'],
    'relationships' => $ary['rels'],
    'profiled_entity' => $entity
  )) ?>
<?php endforeach; ?>
</div>