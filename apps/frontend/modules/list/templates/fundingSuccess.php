<?php include_partial('list/basic', array('list' => $list)) ?>

<?php include_partial('list/membertabs', array('list' => $list)) ?>

<?php include_partial('global/section', array(
  'title' => 'Common Donors',
  'pointer' => 'Donors that people from ' . $list['name'] . ' have received the most money from'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Donor', 'Candidates'),
  'pager' => $donor_pager,
  'row_partial' => 'list/fundingrow',
  'base_object' => $list
)) ?>