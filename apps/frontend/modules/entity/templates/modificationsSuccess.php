<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<?php include_partial('entity/modificationtabs', array('entity' => $entity)) ?>

<?php include_partial('global/section', array(
  'title' => 'Basic Modifications',
  'pointer' => 'Recent changes made to ' . $entity->name . '\'s vital stats'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Date', 'User', 'Action', 'Change', 'Options'),
  'pager' => $modification_pager,
  'row_partial' => 'modification/listrow'
)) ?>