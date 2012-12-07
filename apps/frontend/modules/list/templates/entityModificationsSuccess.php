<?php use_helper('Date') ?>

<?php include_partial('list/header', array('list' => $list)) ?>

<?php include_partial('global/contenttabs', array(
	'tabs' => array(
		'Basic' => 'list/modifications?id=' . $list->id,
		'Entities' => null
	)
)) ?>

<?php include_partial('global/section', array(
  'title' => 'Entity Additions/Removals',
	'pointer' => 'Recent additions to and subtractions from this list'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Date', 'User', 'Action', 'Entity'),
  'pager' => $entity_modification_pager,
  'row_partial' => 'modification/listentitylistrow',
  'base_object' => $list_entities
)) ?>