<?php use_helper('Pager') ?>

<?php $primary = $entity->getPrimaryExtension() ?>
<?php $title = ($primary == 'Person') ? 'People in Common Orgs' : 'Orgs with Common People' ?>
<?php $commonStr = ($primary == 'Person') ? 'Common Orgs' : 'Common People' ?>
<?php $pointer = ($primary == 'Person') ? 'People with positions in the same orgs as ' . $entity->name : 'Leadership and staff of ' . $entity->name . ' also have positions in these orgs' ?>

<?php include_partial('global/section', array(
  'title' => $title,
  'pointer' => $pointer
)) ?>

<?php include_partial('global/table', array(
  'columns' => array($primary, $commonStr),
  'pager' => $entity_pager,
  'row_partial' => 'entity/interlocksrow',
  'base_object' => $entity
)) ?>
