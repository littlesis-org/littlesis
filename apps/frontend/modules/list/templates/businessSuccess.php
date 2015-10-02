<?php include_partial('list/basic', array('list' => $list)) ?>

<?php include_partial('list/membertabs', array('list' => $list)) ?>

<?php include_partial('global/section', array(
  'title' => 'Businesses',
  'pointer' => 'Companies that people from ' . $list->name . ' have had positions in'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Company', 'People'),
  'pager' => $business_pager,
  'row_partial' => 'list/interlocksrow',
  'base_object' => $list
)) ?>