<table>
<?php $col = -1 ?>
  <tr>
<?php foreach ($lobbyists as $lobbyist) : ?>
  <?php if ($col == 5) : ?>
  </tr>
  <tr>
  <?php endif; ?>
    <td style="vertical-align: top; text-align: center; padding: 2em; font-size: 11px;">
      <?php if ($image = $lobbyist['filename']) : ?>
        <?php echo link_to(image_tag(ImageTable::getPath($image, 'profile'), array('alt' => '', 'style' => 'height: 50px; border: 0;')), EntityTable::getInternalUrl($lobbyist)) ?>
      <?php else : ?>
        <?php echo link_to(image_tag('system'.DIRECTORY_SEPARATOR.'anon.png', array('style' => 'height: 50px; border: 0;')), EntityTable::getInternalUrl($lobbyist)) ?>
      <?php endif; ?>
      <br />
      <br />
      <?php echo entity_link($lobbyist, null) ?>
    </td>
  <?php $col = ($col + 1) % 6 ?>
<?php endforeach; ?>
  </tr>
</table>