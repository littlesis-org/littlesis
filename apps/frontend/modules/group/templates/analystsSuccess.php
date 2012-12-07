<?php include_partial('group/header', array('group' => $group)) ?>

<?php include_partial('global/section', array(
  'title' => 'Analysts in this Group',
  'pager' => $user_pager
)) ?>

<div class="padded">
<?php foreach ($user_pager->execute() as $user) : ?>
  <div style="float: left; padding: .6em; height: 5em; text-align: center;">
    <?php echo user_pic($user)?> 
    <br />
    <span class="text_small"><?php echo user_link($user) ?></span>
  </div>
<?php endforeach; ?>
</div>