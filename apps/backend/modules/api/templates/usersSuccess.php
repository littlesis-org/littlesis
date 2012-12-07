<?php slot('header_text', 'API Users') ?>

Active API users during the past four weeks.
<br />
<br />

<?php include_partial('global/table', array(
  'columns' => array('Name', 'Email', 'IP Addresses', 'Last Request', ''),
  'rows' => $active_users,
  'row_partial' => 'api/userlistrow'
)) ?>

<br />

<h3>Pending Users</h3>

<?php include_partial('global/table', array(
  'columns' => array('Date', 'Name', 'Email', 'Reason', ''),
  'rows' => $pending_users,
  'row_partial' => 'api/pendinguserlistrow'
)) ?>
