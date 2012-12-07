<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<?php include_partial('entity/modificationtabs', array('entity' => $entity)) ?>

<?php include_partial('global/section', array(
  'title' => 'Tag Modifications'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Date', 'User', 'Action', 'Tag'),
  'pager' => $tag_modification_pager,
  'row_partial' => 'modification/taglistrow'
)) ?>