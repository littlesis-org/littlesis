<?php include_partial('relationship/header', array('relationship' => $relationship, 'show_actions' => true)) ?>

<?php include_partial('relationship/modificationtabs', array('relationship' => $relationship)) ?>

<?php include_partial('global/section', array(
  'title' => 'Tag Modifications'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Date', 'User', 'Action', 'Tag'),
  'pager' => $tag_modification_pager,
  'row_partial' => 'modification/taglistrow'
)) ?>