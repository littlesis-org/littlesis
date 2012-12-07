<?php use_helper('Date') ?>

<?php include_partial('list/header', array('list' => $list)) ?>

<?php include_partial('global/contenttabs', array(
	'tabs' => array(
		'Basic' => null,
		'Entities' => $list->getInternalUrl('entityModifications')
	)
)) ?>

<?php include_partial('global/section', array(
  'title' => 'Basic Modifications',
  'pointer' => 'Recent changes to the basic properties of this list'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Date', 'User', 'Action', 'Change', 'Options'),
  'pager' => $modification_pager,
  'row_partial' => 'modification/listrow'
)) ?>