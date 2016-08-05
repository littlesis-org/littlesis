<?php slot('leftcol') ?>


<?php if (!cache('rightcol_profileimage', 86400)) : ?>
  <?php include_partial('entity/profileimage', array('entity' => $entity)) ?>
  <br />
  <?php cache_save() ?>
<?php endif; ?>


<?php include_partial('global/modifications', array('object' => $entity)) ?>
<br />


<?php if ($sf_user->isAuthenticated() || !cache('rightcol_references', 86400)) : ?>
  <?php include_partial('global/references', array('object' => $entity)) ?>
  <br />
  <?php if (!$sf_user->isAuthenticated()) : ?>
    <?php cache_save() ?>
  <?php endif; ?>
<?php endif; ?>


<?php if (!cache('rightcol_stats', 86400)) : ?>
  <?php include_partial('entity/vitalstats', array('entity' => $entity)) ?>
  <br />
  <?php cache_save() ?>
<?php endif; ?>


<?php if ($sf_user->isAuthenticated() || !cache('rightcol_lists', 86400)) : ?>
  <?php include_partial('global/lists', array('entity' => $entity)) ?>
  <br />
  <?php if (!$sf_user->isAuthenticated()) : ?>
    <?php cache_save() ?>
  <?php endif; ?>
<?php endif; ?>


<?php if ($sf_user->hasCredential('editor') || $sf_user->hasCredential('merger')) : ?>

  <?php if ($sf_user->hasCredential('merger') && !cache('similarEntities')) : ?>
    <?php include_component('entity', 'similarEntities', array('entity' => $entity)) ?>
    <?php cache_save() ?>
  <?php endif; ?>
  
  <?php /* TOO SLOW FOR NOW
  <?php if ($sf_user->hasCredential('editor') && !cache('watchers')) : ?>
    <?php include_component('entity', 'watchers', array('entity' => $entity)) ?>
    <?php cache_save() ?>
  <?php endif; ?>
  */ ?>

<?php endif; ?>


<?php end_slot() ?>
