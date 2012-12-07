<?php use_helper('Pager') ?>


<?php include_partial('global/section', array(
  'title' => 'Schools Attended',
  'pointer' => 'People with positions in ' . $entity->name . ' have attended these schools'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('School', 'People'),
  'pager' => $school_pager,
  'row_partial' => 'entity/networkschoolrow',
  'base_object' => $entity
)) ?>