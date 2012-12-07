<?php $industries = array() ?>
<?php foreach ($entity->Industry as $industry) : ?>
  <?php $industries[] = $industry->getDisplayName() ?>
<?php endforeach; ?>

<?php $data = array(
  'Industry' => $industries
) ?>

<?php include_partial('global/datatable', array(
  'data' => $data,
  'label_width' => isset($label_width) ? $label_width : '80px'
)) ?>