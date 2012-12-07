<?php use_helper('LsNumber') ?>

<?php $person = EntityTable::getPersonById($entity['id']) ?>
<?php $legalName = PersonTable::getLegalName($person) ?>
<?php $data = array(
  'Types' => implode(', ', array_keys(EntityTable::getExtensionsForDisplay($entity, false))),
  'Legal Name' => $entity['name'] == $legalName ? null : $legalName,
  'Birthplace' => $person['birthplace'],
  'Date of Birth' => Dateable::convertForDisplay($entity['start_date']),
  'Date of Death' => Dateable::convertForDisplay($entity['end_date']),
  'Gender' => PersonTable::getGenderName($person),
  //'Website' => $entity['website'] ? link_to($entity['website'], $entity['website']) : null,
  //'Political Party' => $entity->Party->exists() ? entity_link($entity->Party, null) : ($entity->is_independent ? 'Independent' : null),
  //'Net Worth' => readable_number($entity->net_worth, '$')
) ?>

<?php include_partial('global/datatable', array(
  'data' => $data,
  'label_width' => isset($label_width) ? $label_width : '80px'
)) ?>