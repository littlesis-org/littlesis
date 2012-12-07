<?php use_helper('LsNumber') ?>

<?php $data = array(
  'Endowment' => readable_number($entity->endowment, '$'),
  'Students' => $entity->students,
  'Faculty' => $entity->faculty,
  'Tuition' => readable_number($entity->tuition, '$'),
  'Private' => $entity->is_private
) ?>

<?php include_partial('global/datatable', array(
  'data' => $data,
  'label_width' => isset($label_width) ? $label_width : '80px'
)) ?>