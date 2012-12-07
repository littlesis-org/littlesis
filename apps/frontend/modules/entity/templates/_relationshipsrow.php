<tr class="hover<?php if ($shaded) { echo ' shaded'; } ?>">
  <td>
    <span class="text_big">
    <?php if ($object->Entity1->id == $base_object->id) : ?>
      <?php echo entity_link($object->Entity2) ?>
    <?php else : ?>
      <?php echo entity_link($object->Entity1) ?>
    <?php endif; ?>
    </span>
  </td>
  <td>
    <?php echo link_to($object->Category->display_name, 'relationship/view?id=' . $object->id) ?>
    <?php if (($base_object->id == $object->entity1_id) && $object->description2) : ?>
      (<?php echo $object->description2 ?>)
    <?php elseif (($base_object->id == $object->entity2_id) && $object->description1) : ?>
      (<?php echo $object->description1 ?>)
    <?php endif; ?>
  </td>
  <td>
    <?php echo $object->getDetails() ?>
  </td>
  <td><?php echo $object->getDatespan() ?></td>
</tr>