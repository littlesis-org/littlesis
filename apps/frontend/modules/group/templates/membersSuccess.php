<?php include_partial('group/header', array('group' => $group)) ?>

<?php include_partial('global/section', array(
  'title' => 'Group Members',
  'action' => array(
    'text' => 'add',
    'url' => $group->getInternalUrl('addUser'),
    'condition' => $sf_user->hasCredential('admin') || $sf_user->getGuardUser()->isGroupOwner($group['name'])
  )
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('', 'Username', 'Role', 'Score', 'Member Since', 'Actions'),
  'pager' => $user_pager,
  'row_partial' => 'group/memberlistrow',
  'base_object' => $group
)) ?>