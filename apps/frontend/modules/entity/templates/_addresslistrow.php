<tr>
  <td>
    <?php echo sprintf("%01.1f", AddressTable::getDistanceBetweenCoords(
      $object->latitude, 
      $object->longitude,
      $base_object->latitude,
      $base_object->longitude
    )) ?>
  </td>
  <td><?php echo entity_link($object->Entity, null) ?></td>
  <td>
    <span class="text_small"><?php echo $object->getOneLiner() ?></span>
  </td>
</tr>