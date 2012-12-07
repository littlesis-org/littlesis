<?php use_helper('Date') ?>

<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<?php include_partial('entity/modificationtabs', array('entity' => $entity)) ?>

<?php include_partial('global/section', array(
  'title' => 'Addresses',
  'pointer' => 'Click an address below to see its revision history'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Address', 'Created', 'Last Updated', 'Status'),
  'rows' => $addresses,
  'row_partial' => 'modification/addresslistrow'
)) ?>

<br />
<br />


<?php include_partial('global/section', array(
  'title' => 'Phone & Email Modifications',
  'pointer' => 'Recent changes made to phone & email info for ' . $entity->name
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Date', 'User', 'Item', 'Action', 'Change'),
  'pager' => $contact_modification_pager,
  'row_partial' => 'modification/contactlistrow'
)) ?>