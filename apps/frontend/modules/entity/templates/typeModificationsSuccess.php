<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<?php include_partial('entity/modificationtabs', array('entity' => $entity)) ?>

<?php include_partial('global/section', array(
  'title' => 'Type Modifications'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Date', 'User', 'Action', 'Type'),
  'rows' => $ext_modifications,
  'row_partial' => 'modification/extlistrow'
)) ?>