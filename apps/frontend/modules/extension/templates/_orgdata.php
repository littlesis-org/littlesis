<?php use_helper('LsNumber') ?>
<?php $data = array(
  'Types' => implode(', ', array_keys(EntityTable::getExtensionsForDisplay($entity, false))),
  'Start Date' => Dateable::convertForDisplay($entity['start_date']),
  'End Date' => Dateable::convertForDisplay($entity['end_date']),
  'Website' => $entity['website'] ? link_to(excerpt($entity['website'], 25, '...', false), $entity['website']) : null,
  'Employees' => readable_number($entity['employees']),
  'Revenue' => readable_number($entity['revenue'], '$'),
  //'FedSpending ID' => $entity->fedspending_id,
  //'LDA Registrant ID' => $entity->lda_registrant_id
) ?>

<?php include_partial('global/datatable', array(
  'data' => $data,
  'label_width' => isset($label_width) ? $label_width : '80px'
)) ?>