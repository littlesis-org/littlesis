<?php include_partial('list/header', array('list' => $list, 'show_actions' => false)) ?>

<table>
<?php $col = -1 ?>
  <tr>
<?php foreach ($entities as $entity) : ?>
  <?php if ($col == 4) : ?>
  </tr>
  <tr>
  <?php endif; ?>
    <td style="vertical-align: top; text-align: center; padding: 0.8em; padding-bottom: 1.2em;">
      <?php echo link_to(image_tag(ImageTable::getPath($entity['Image'][0]['filename'], 'profile'), array('alt' => '', 'style' => 'height: 100px; border: 0;')), EntityTable::getInternalUrl($entity)) ?>
      <br />
      <br />
      <?php echo entity_link($entity, null) ?>
    </td>
  <?php $col = ($col + 1) % 5 ?>
<?php endforeach; ?>
  </tr>
</table>