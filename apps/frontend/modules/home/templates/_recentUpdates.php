<?php use_helper('Date') ?>

<?php include_partial('global/section', array(
  'title' => 'Recent Updates',
  'pointer' => isset($pointer) ? $pointer : 'Recent edits by LittleSis analysts' . (@$group ? ' in this group' : null) . ($sf_user->isAuthenticated() ?  null : '. ' . link_to('Sign up','home/join') . ' to become an analyst!'),
  'action' => array(
    'url' => @$more_url ? $more_url : 'modification/latest',
    'text' => 'more'
  )
)) ?>

<div class="padded">
<?php foreach($entities as $entity) : ?>
  <?php include_partial('entity/recentupdate', array('entity' => $entity)) ?>
<?php endforeach; ?>
</div>

<br />