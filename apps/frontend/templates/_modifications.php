<?php if ($object['last_user_id'] && $publicName = sfGuardUserTable::getPublicNameById($object['last_user_id'])) : ?>

<?php use_helper('Date') ?>

<?php slot('header_right') ?>
Edited by 
<?php echo user_link_by_public_name($publicName) ?> <?php echo time_ago_in_words(strtotime($object['updated_at'])) ?> ago
&nbsp;
<?php if ($sf_user->isAuthenticated()) : ?>
  <nobr><?php echo link_to('History &raquo', $more_uri) ?></nobr>
<?php endif; ?>
<?php end_slot() ?>

<?php endif; ?>