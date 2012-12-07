<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => false)) ?>

<h2>Revolving Door Army</h2>

The following lobbyists have represented <?php echo entity_link($entity, null) ?> and have worked for
the federal government, often as top staff to key congressional committee members.

<br />
<br />

<table>
<?php $col = -1 ?>
  <tr>
<?php foreach ($lobbyists as $lobbyist) : ?>
  <?php if ($col == 3) : ?>
  </tr>
  <tr>
  <?php endif; ?>
    <td style="vertical-align: middle; text-align: center; padding: 2em;">
      <?php if ($image = $lobbyist['filename']) : ?>
        <?php echo link_to(image_tag(ImageTable::getPath($image, 'profile'), array('alt' => '', 'style' => 'height: 130px; border: 0;')), EntityTable::getInternalUrl($lobbyist)) ?>
      <?php else : ?>
        <?php echo link_to(image_tag('system'.DIRECTORY_SEPARATOR.'anon.png', array('style' => 'height: 130px; border: 0;')), EntityTable::getInternalUrl($lobbyist)) ?>
      <?php endif; ?>
      <br />
      <br />
      <?php echo entity_link($lobbyist) ?>
      <br />
      <em><?php echo $lobbyist['blurb'] ?></em>
    </td>
  <?php $col = ($col + 1) % 4 ?>
<?php endforeach; ?>
  </tr>
</table>


<br />
<br />

<h2>Lobbyist Hubs in Congress</h2>

The above lobbyists have previously worked as staff to the following members of congress.

<br />
<br />

<table>
<?php $col = -1 ?>
  <tr>
<?php foreach ($members as $member) : ?>
  <?php if ($col == 3) : ?>
  </tr>
  <tr>
  <?php endif; ?>
    <td style="vertical-align: middle; text-align: center; padding: 2em;">
      <?php if ($image = $member['filename']) : ?>
        <?php echo link_to(image_tag(ImageTable::getPath($image, 'profile'), array('alt' => '', 'style' => 'height: 130px; border: 0;')), EntityTable::getInternalUrl($member)) ?>
      <?php else : ?>
        <?php echo link_to(image_tag('system'.DIRECTORY_SEPARATOR.'anon.png', array('style' => 'height: 130px; border: 0;')), EntityTable::getInternalUrl($member)) ?>
      <?php endif; ?>
      <br />
      <br />
      <?php echo entity_link($member) ?>
      <br />
      <em><?php echo $member['blurb'] ?></em>
    </td>
  <?php $col = ($col + 1) % 4 ?>
<?php endforeach; ?>
  </tr>
</table>
  
