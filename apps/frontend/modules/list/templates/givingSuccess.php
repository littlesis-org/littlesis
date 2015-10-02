<?php include_partial('list/basic', array('list' => $list)) ?>

<?php include_partial('list/membertabs', array('list' => $list)) ?>

<?php include_partial('global/section', array(
  'title' => 'Common Recipients',
  'pointer' => 'People and orgs that people from ' . $list['name'] . ' have donated the most to'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Recipient', 'Total', 'People'),
  'pager' => $committee_pager,
  'row_partial' => 'list/givingrow',
  'base_object' => $list
)) ?>