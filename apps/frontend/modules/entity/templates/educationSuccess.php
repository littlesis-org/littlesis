<?php include_partial('global/section', array(
  'title' => 'Education',
  'pager' => $education_pager,
  'pointer' => 'Schools ' . $entity->name . ' has attended'
)) ?>

<div class="padded">
<?php foreach ($education_pager->execute() as $id => $ary) : ?>
  <?php include_partial('entity/entitywithrelationships', array(
    'related_entity' => $ary['entity'],
    'relationships' => $ary['rels'],
    'profiled_entity' => $entity
  )) ?>
<?php endforeach; ?>
</div>