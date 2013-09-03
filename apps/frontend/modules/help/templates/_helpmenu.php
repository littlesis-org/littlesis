<div id="help_menu">

<ul class="help_menu_links">

<?php foreach ($items as $key => $ary) : ?>
  <li>
    <?php echo link_to($ary['name'],"help/" . $key,$key == $current ? "class='help_active'" : "") ?>
    <?php if (isset($ary['children'])) : ?>
      <ul>
      <?php foreach ($ary['children'] as $child_key => $child_ary) : ?>
        <li><?php echo link_to($child_ary['name'],"help/" . $child_key,$child_key == $current ? "class='help_active'" : "") ?>
      <?php endforeach; ?>
      </ul>
    <?php endif; ?>
<?php endforeach; ?>

</ul>

</div>