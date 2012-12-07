<?php include_partial('home/profileimage', array('profile' => $profile)) ?>
<br />
<?php include_component('user', 'groups', array('user' => $profile->User)) ?>
<br />
<?php include_partial('home/profile', array('profile' => $profile)) ?>
<br />
<?php if (!$profile->User->is_super_admin) : ?>
<?php include_component('home', 'points', array('profile' => $profile)) ?>
<br />
<?php endif; ?>
<?php include_partial('home/permissions') ?>
