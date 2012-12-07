<?php echo json_encode(array(
  'id' => $entity->id,
  'ext' => $entity['primary_ext'],
  'link' => entity_link($entity, null, false)
)) ?>