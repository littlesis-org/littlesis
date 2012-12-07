<?php include_partial('global/section', array(
  'title' => 'Research Groups',
  'action' => array('text' => 'details', 'url' => sfGuardUserTable::getInternalUrl($user, 'groups'))
)) ?>

<div class="padded">
<?php foreach ($groups as $group) : ?>
  <?php echo group_link($group) ?>
  <br />
<?php endforeach; ?>
</div>
