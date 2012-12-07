<?php include_partial('group/header', array('group' => $group)) ?>

<h2>Add User</h2>

Search for a user to add to this group:<br />
<br />

<form action="<?php echo url_for($group->getInternalUrl('addUser')) ?>" method="GET">
<?php echo input_tag('q', $sf_request->getParameter('q')) ?> <?php echo submit_tag('Search', 'class=button_small') ?>
</form>

<br />
<br />

<?php if (isset($result_pager)) : ?>
<?php include_partial('global/section', array(
  'title' => 'Results',
  'pager' => $result_pager
)) ?>
<div class="padded">
<?php foreach ($result_pager->execute() as $user) : ?>
  <strong><?php echo link_to('select', $group->getInternalUrl('addUser', array('user_id' => $user['id'])), 'post=true') ?></strong>
  &nbsp;
  <span class="text_big"><?php echo user_link($user) ?></span>
  <br />
<?php endforeach; ?>
</div>
<?php endif; ?>