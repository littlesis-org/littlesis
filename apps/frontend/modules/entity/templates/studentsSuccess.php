<?php include_partial('global/section', array(
  'title' => 'Students',
  'pager' => $student_pager,
  'pointer' => 'People who have attended ' . $entity->name
)) ?>

<div class="padded">
<?php foreach ($student_pager->execute() as $id => $ary) : ?>
  <?php include_partial('entity/entitywithrelationships', array(
    'related_entity' => $ary['entity'],
    'relationships' => $ary['rels'],
    'profiled_entity' => $entity
  )) ?>
<?php endforeach; ?>
</div>