<?php include_partial('list/header', array('list' => $list, 'show_actions' => false)) ?>

<?php if ($entities == null) : ?>
  There are too many entities on this list for picture page.
<?php else : ?>

<table>
<?php $col = -1 ?>
  <tr>
<?php foreach ($entities as $entity) : ?>
  <?php if ($col == 3) : ?>
  </tr>
  <tr>
  <?php endif; ?>
    <td style="vertical-align: middle; text-align: center; padding: 2em;">
      <?php if ($image = $entity['filename']) : ?>
        <?php echo link_to(image_tag(ImageTable::getPath($image, 'profile'), array('alt' => '', 'style' => 'height: 130px; border: 0;')), EntityTable::getInternalUrl($entity)) ?>
      <?php else : ?>
        <?php echo link_to(image_tag('system'.DIRECTORY_SEPARATOR.'anon.png', array('style' => 'height: 130px; border: 0;')), EntityTable::getInternalUrl($entity)) ?>
      <?php endif; ?>
      <br />
      <br />
      <?php echo entity_link($entity) ?>
      <br />
      <em><?php echo $entity['blurb'] ?></em>
    </td>
  <?php $col = ($col + 1) % 4 ?>
<?php endforeach; ?>
  </tr>
</table>

<?php endif; ?>


<br />
<br />

  
