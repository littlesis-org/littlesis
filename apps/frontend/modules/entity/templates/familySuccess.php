<?php include_partial('global/section', array(
  'title' => 'Family',
  'pager' => $family_pager,
  'pointer' => 'People in the same family as ' . $entity->name,
)) ?>

<div class="padded">
<?php foreach ($family_pager->execute() as $id => $ary) : ?>
  <?php include_partial('entity/entitywithrelationships', array(
    'related_entity' => $ary['entity'],
    'relationships' => $ary['rels'],
    'profiled_entity' => $entity
  )) ?>
<?php endforeach; ?>
</div>