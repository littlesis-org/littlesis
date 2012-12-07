<?php slot('header_text', $network['name']) ?>
<?php slot('header_link', LsListTable::getNetworkInternalUrl($network)) ?>

<?php include_partial('global/section', array(
  'title' => 'Analysts in this Network',
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