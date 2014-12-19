<?php $text = trim(get_partial('relationship/descriptionsoneliner', array(
  'relationship' => $relationship,
  'profiled_entity' => $profiled_entity,
  'related_entity' => $related_entity
))) ?>

<?php echo $text == 'Hierarchy' ? ($related_entity['id'] == $relationship['entity1_id'] ? 'child org' : 'parent org') : $text ?>