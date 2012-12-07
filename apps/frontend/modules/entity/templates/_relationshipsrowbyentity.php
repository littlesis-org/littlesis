<?php foreach ($base_object->getRelationshipsWithQuery($object)->execute() as $relationship) : ?>
  <?php include_partial('entity/relationshipsrowbydate', array(
    'object' => $relationship,
    'base_object' => $base_object
  )) ?>
<?php endforeach; ?>
