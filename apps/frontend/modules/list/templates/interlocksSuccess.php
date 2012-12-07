<?php include_partial('list/header', array('list' => $list, 'show_actions' => ($list->is_admin && !$sf_user->hasCredential('admin')) ? false : true)) ?>


<?php include_partial('global/section', array(
  'title' => 'Businesses',
  'pointer' => 'Companies that people from ' . $list->name . ' have the most positions in'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Company', 'People'),
  'pager' => $business_pager,
  'row_partial' => 'list/interlocksrow',
  'base_object' => $list,
  'more' => $list->getInternalUrl('business')
)) ?>


<br />


<?php include_partial('global/section', array(
  'title' => 'Government Bodies',
  'pointer' => 'Government bodies that people from ' . $list->name . ' have the most positions in'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Govt Body', 'People'),
  'pager' => $government_pager,
  'row_partial' => 'list/interlocksrow',
  'base_object' => $list,
  'more' => $list->getInternalUrl('government')
)) ?>


<br />


<?php include_partial('global/section', array(
  'title' => 'Other Organizations',
  'pointer' => 'Non-business and non-govt orgs that people from ' . $list->name . ' have the most positions in'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Org', 'People'),
  'pager' => $other_pager,
  'row_partial' => 'list/interlocksrow',
  'base_object' => $list,
  'more' => $list->getInternalUrl('otherOrgs')
)) ?>