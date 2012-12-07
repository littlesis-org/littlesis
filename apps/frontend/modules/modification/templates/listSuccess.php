<?php use_helper('Date') ?>
<?php $lower = strtolower(get_class($object)) ?>
<?php include_partial($lower . '/header', array($lower => $object)) ?>

<?php include_partial('global/section', array(
  'title' => 'Modifications',
  'pager' => $modification_pager
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Date', 'User', 'Action', 'Change', 'Options'),
  'pager' => $modification_pager,
  'row_partial' => 'modification/listrow'
)) ?>