<?php use_helper('Date') ?>

<?php include_partial('group/header', array('group' => $group)) ?>


<span class="text_big">
Below are the profiles that have been most recently updated by a LittleSis analyst in this group.
</span>
<br />
<br />

<?php include_partial('global/section', array('title' => 'Recent Updates')) ?>

<?php include_partial('global/table', array(
  'columns' => array('Modified By', 'Name', 'Type(s)'),
  'pager' => $entity_pager,
  'row_partial' => 'entity/updatedlistrow'
)) ?>