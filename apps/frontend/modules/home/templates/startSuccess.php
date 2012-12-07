<?php use_helper('Date', 'LsText') ?>


<?php if (!$sf_user->isAuthenticated()) : ?>
  <?php include_partial('home/intro') ?>
<?php endif; ?>


<?php if (!cache('blog_feed', 600)) : ?>
  <?php include_component('home', 'blogFeed') ?>
  <?php cache_save() ?>
<?php endif; ?>


<?php if ($sf_user->isAuthenticated() || !cache('recent_notes', 60)) : ?>
  <?php include_component('home', 'recentNotes') ?>  
  <?php if (!$sf_user->isAuthenticated()) : ?>
    <?php cache_save() ?>
  <?php endif; ?>
<?php endif; ?>


<?php slot('usercol') ?>
  <?php if (!cache('featuredProfiles', 600)) : ?>
    <?php include_component('home', 'featuredProfiles') ?>
    <?php cache_save() ?>
  <?php endif; ?>
  <?php if (!cache('updates', 60)) : ?>  
    <?php include_component('home', 'recentUpdates') ?>  
    <?php cache_save() ?>
  <?php endif; ?>
<?php end_slot() ?>