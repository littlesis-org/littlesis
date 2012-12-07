<?php slot('header_text', $group->name) ?>

<?php echo include_partial('global/warning', array(
  'message' => 'You must be ' . ($sf_user->isAuthenticated() ? '' : 'signed in and ' ) . 'a member of this group to access this page.'
)) ?>