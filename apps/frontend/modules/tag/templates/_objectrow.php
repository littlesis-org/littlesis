<tr>
  <td>
    <?php echo $object ?>
  </td>
  <td>
    <?php if (get_class($object) == 'Entity') : ?>
      <?php echo $object->getPrimaryExtension($forDisplay=true) ?>
    <?php else : ?>
      <?php echo get_class($object) ?>
    <?php endif; ?>
  </td>
</tr>