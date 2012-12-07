<?php include_partial('user/header', array('user' => $profile->User, 'show_actions' => true)) ?>

<?php slot('rightcol') ?>
  <?php include_partial('home/rightcol', array('profile' => $profile)) ?>
<?php end_slot() ?>