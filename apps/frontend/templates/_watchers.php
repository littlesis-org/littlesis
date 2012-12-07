<?php if (isset($watchers) && $watchers) : ?>

<?php include_partial('global/section', array(
  'title' => "Who's Watching"
)) ?>

<div class="padded" style="padding-bottom: .6em">
&nbsp;<br />&nbsp;
</div>

<?php foreach($watchers as $watcher) : ?>
  <div style = "float:left; padding: .6em; height: 5em">
  <span style="vertical-align:bottom">
  <?php echo user_pic($watcher)?> 
  </span>
  <br />
  <span class="text_small" style="vertical-align:top">
  <?php echo user_link($watcher, null, false) ?>
  </span>
  </div>
<?php endforeach; ?>

<?php endif; ?>