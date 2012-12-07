<?php include_partial('global/section', array(
    'title' => 'Featured Lists',
    'pointer' => 'A selection of lists - some created by LittleSis analysts, others by outside publications - of especially powerful people and organizations profiled on LittleSis')) ?>

<table>
  <tr>
<?php $num = 0 ?>
<?php foreach ($lists as $list) : ?>
    <td class="featured_list">
      <div class="text_big margin_bottom"><?php echo list_link($list) ?></div>
      <span class="description"><?php echo $list->description ?></span>
    </td>
  <?php $num += 1 ?>
  <?php if ($num % 2 == 0) : ?>
  </tr>
  <tr>
  <?php endif; ?>
<?php endforeach; ?>
  </tr>
</table>