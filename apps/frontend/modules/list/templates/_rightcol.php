<?php slot('rightcol') ?>
  <?php include_partial('global/modifications', array(
    'object' => $list,
    'model' => 'LsList',
    'more_uri' => LsListTable::getInternalUrl($list, 'modifications')
  )) ?>
  <?php include_partial('list/search', array('list' => $list)) ?>
  <br />
  <?php include_component('reference', 'list', array(
    'object' => $list,
    'model' => 'LsList',
    'more_uri' => LsListTable::getInternalUrl($list, 'references')
  )) ?>
  <br />
  
<?php if ($sf_user->hasCredential('bulker')) : ?>
  <?php include_partial('list/expertlinks', array('list' => $list)) ?>
  <br />
  
<?php endif; ?>
  <?php include_partial('global/notes', array(
    'record' => $list,
    'model' => 'LsList',
    'more_uri' => LsListTable::getInternalUrl($list, 'notes')
  )) ?>
<?php end_slot() ?>