<?php include_partial('image/header', array('image' => $image, 'show_actions' => true)) ?>


<?php $data = array(
  $entity->getPrimaryExtension(true) => entity_link($entity),
  'Caption/credit' => $image->caption,
  'Free' => $image->is_free
) ?>

<?php include_partial('global/datatable', array(
  'data' => $data
)) ?>

<br />
<br />

<?php echo image_tag($image->getLargePath(), array('style' => 'max-width: 500px;')) ?>