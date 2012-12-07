<?php include_partial('global/section', array(
  'title' => 'Government Bodies',
  'pointer' => 'Government bodies that people from ' . $list->name . ' have the most positions in'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Govt Body', 'People'),
  'pager' => $government_pager,
  'row_partial' => 'list/interlocksrow',
  'base_object' => $list
)) ?>