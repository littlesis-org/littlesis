<?php if (count($groups)) : ?>

<div style="font-size: 13px; padding: 1.5em; background-color: #fffff0; border: 1px solid #bbb;">

<span class="section_title">Research Groups</span> 
<span class="section_actions"><?php echo link_to('more', '@groups') ?></span>

<?php foreach($groups as $group) : ?>
  <div class="group-name"><?php echo group_link($group) ?></div>
  <div class="group-blurb"><?php echo $group['blurb'] ?></div>
<?php endforeach; ?>

</div>

<br />
<br />

<?php endif; ?>