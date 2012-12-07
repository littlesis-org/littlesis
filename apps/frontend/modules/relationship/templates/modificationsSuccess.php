<?php include_partial('relationship/header', array('relationship' => $relationship, 'show_actions' => true)) ?>

<?php include_partial('global/section', array(
  'title' => 'Modifications',
  'pointer' => 'Recent changes made to this relationship'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Date', 'User', 'Action', 'Change', 'Options'),
  'pager' => $modification_pager,
  'row_partial' => 'modification/listrow'
)) ?>