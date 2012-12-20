<?php use_helper('Pager') ?>


<?php $primary = $entity['primary_ext'] ?>
<?php $title = ($primary == 'Person') ? __('People in Common Orgs') : __('Orgs with Common People') ?>
<?php $commonStr = ($primary == 'Person') ? __('Common Orgs') : __('Common People') ?>
<?php $pointer = ($primary == 'Person') ? __('People with positions in the same orgs as') . " " . $entity['name'] : sprintf(__("Leadership and staff of %s also have positions in these orgs"), $entity['name']) ?>

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
