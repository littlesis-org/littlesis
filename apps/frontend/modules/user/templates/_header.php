<?php slot('header_text', "Analyst: " . $user->getProfile()->public_name) ?>
<?php slot('header_link', $user->isCurrentUser() ? 'home/notes' : $user->getInternalUrl()) ?>

<?php $actions = array(
  'groups' => array(
    'url' => $user->isCurrentUser() ? 'home/groups' : $user->getInternalUrl('groups')
  ),
  'changes' => array(
    'url' => $user->isCurrentUser() ? 'home/modifications' : $user->getInternalUrl('modifications')
  )
) ?>  

<?php if ($user->isCurrentUser()) : ?>
<?php $actions['settings'] = array(
  'url' => 'home/settings'
) ?>
<?php endif; ?>

<?php slot('header_actions', $actions) ?>