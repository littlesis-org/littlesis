<?php use_helper('Date') ?>

<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<?php include_partial('entity/modificationtabs', array('entity' => $entity)) ?>

<?php include_partial('global/section', array(
  'title' => 'Images',
  'pointer' => 'Click an image link below to see its revision history'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Image', 'Created', 'Last Updated', 'Status'),
  'rows' => $images,
  'row_partial' => 'modification/imagelistrow'
)) ?>