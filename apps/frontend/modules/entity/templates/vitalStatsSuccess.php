<!-- NAME-VALUE PAIRS -->
<?php include_partial('global/section', array(
  'title' => 'Basic Info'
)) ?>

<div class="padded margin_bottom">
<?php foreach ($entity->getExtensionsHavingFields() as $extension) : ?>
  <?php include_partial('extension/' . strtolower($extension) . 'data', array(
    'entity' => $entity,
    'label_width' => '140px'
  )) ?>
<?php endforeach; ?>
</div>


