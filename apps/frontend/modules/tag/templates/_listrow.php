<tr>
  <td><?php echo link_to($object->getName(), 'tag/view?name=' . $object->getName()) ?></td>
  <td><?php echo $object->ObjectTag[0]->num ?></td>
  <td>
    <?php if ($ot = $object->getLatestObjectTag()) : ?>
      <?php if ($object = $ot->getObject()) : ?>
        <?php echo object_link($object) ?> at <?php echo $ot->created_at ?>
      <?php endif; ?>
    <?php endif; ?>
</tr>