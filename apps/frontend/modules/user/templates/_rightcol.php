<?php include_partial('user/profileimage', array('profile' => $profile)) ?>
<br />
<?php include_component('user', 'groups', array('user' => $profile->User)) ?>
<br />
<?php if (($profile["public_name"] && $profile["show_full_name"]) || $profile["bio"]) : ?>
<?php include_partial('user/profile', array('profile' => $profile)) ?>
<br />
<?php endif; ?>
<?php if ($sf_user->hasCredential('contributor') && !$profile->User->is_super_admin) : ?>
<?php include_component('user', 'points', array('profile' => $profile)) ?>
<br />
<?php endif; ?>
<?php include_partial('user/permissions', array('user' => $profile->User)) ?>
