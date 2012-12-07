<?php use_helper('Text') ?>

<?php slot('header_text', $group['display_name']) ?>
<?php slot('header_link', sfGuardGroupTable::getInternalUrl($group)) ?>
<?php slot("header_subtext", auto_link_text(html_entity_decode($group->blurb))) ?>

<?php if ($sf_user->isAuthenticated()) : ?>
<?php if (!isset($hide_actions) || !$hide_actions) : ?>
  <?php slot('header_actions', array(
    'edit' => array(
      'url' => $group->getInternalUrl('edit'),
      'condition' => ($sf_user->hasCredential('admin') || $sf_user->getGuardUser()->isGroupOwner($group->name))
    ),
    'leave group' => array(
      'url' => $group->getInternalUrl('leave'),
      'condition' => ($sf_user->hasGroup($group->name)),
      'options' => 'post=true confirm=Are you sure you want to leave this group?',
    ),
    'join group' => array(
      'url' => $group->getInternalUrl('join'),
      'condition' => (!$sf_user->hasGroup($group->name)),
      'options' => 'post=true'
    )
  )) ?>
<?php endif; ?>
<?php endif; ?>