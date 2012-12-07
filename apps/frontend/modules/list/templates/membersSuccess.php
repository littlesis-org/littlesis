<?php use_helper('LsText') ?>


<?php include_partial('global/section', array('title' => 'Members')) ?>


<?php if ($list->is_ranked) : ?>

<?php include_partial('global/table', array(
  'columns' => array('Rank', 'Name', 'About'),
  'pager' => $list_entity_pager,
  'row_partial' => 'list/rankedentityrow'
)) ?>

<?php else : ?>

<?php include_partial('global/table', array(
  'columns' => array('Name', 'About'),
  'pager' => $list_entity_pager,
  'row_partial' => 'list/entityrow'
)) ?>

<?php endif; ?>

