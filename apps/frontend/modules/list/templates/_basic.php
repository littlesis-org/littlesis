<?php include_partial('list/header', array('list' => $list, 'show_actions' => ($list->is_admin && !$sf_user->hasCredential('admin')) ? false : true)) ?>

<?php include_partial('list/rightcol', array('list' => $list)) ?>

<?php if ($list->is_admin) : ?>
  <em>This list is maintained by admin users. Please 
  <?php echo link_to('contact us','home/contact') ?>
  if you would like to suggest changes.</em>
  <br />
  <br />
<?php endif; ?>

<?php if ($list['description']) : ?>
<span class="profile_summary">
  <?php include_partial('global/excerpt', array('text' => $list['description'], 'id' => 'description', 'less' => true)) ?>
  <br />
  <br />
</span>
<?php endif; ?>