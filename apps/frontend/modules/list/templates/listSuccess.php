<?php slot('header_text', 'Lists') ?>
<?php slot('header_link', '@lists') ?>

<?php slot('pointer') ?>
Lists are collections of people and/or organizations that don't belong to a formal group.  
Many of them were originally compiled by media organizations, frequently in order to rank people by power, wealth, or influence.
<?php end_slot() ?>

<?php include_partial('global/pointer', array('text' => get_slot('pointer'))) ?>

<?php include_partial('global/section', array(
  'title' => 'Lists'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Name', 'Count', 'Description'),
  'pager' => $list_pager,
  'row_partial' => 'list/listrow'
)) ?>