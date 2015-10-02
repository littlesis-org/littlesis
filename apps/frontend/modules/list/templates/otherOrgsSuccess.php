<?php include_partial('list/basic', array('list' => $list)) ?>

<?php include_partial('list/membertabs', array('list' => $list)) ?>

<?php include_partial('global/section', array(
  'title' => 'Other Organizations',
  'pointer' => 'Non-business and non-govt orgs that people from ' . $list->name . ' have the most positions in'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Organization', 'People'),
  'pager' => $other_pager,
  'row_partial' => 'list/interlocksrow',
  'base_object' => $list
)) ?>