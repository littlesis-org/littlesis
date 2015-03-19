<?php use_helper('LsNumber') ?>

<?php $data = array(
  'Types' => implode(', ', array_keys(EntityTable::getExtensionsForDisplay($entity, false))),
  'Start Date' => Dateable::convertForDisplay($entity['start_date']),
  'End Date' => Dateable::convertForDisplay($entity['end_date']),
  'Partner 1' => $entity['partner1'] ? entity_link($entity['partner1']) : null,
  'Partner 2' => $entity['partner2'] ? entity_link($entity['partner2']) : null
) ?>

<?php include_partial('global/datatable', array(
  'data' => $data,
  'label_width' => isset($label_width) ? $label_width : '80px'
)) ?>