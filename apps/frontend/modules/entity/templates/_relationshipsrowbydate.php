<tr class="hover">
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
    <?php if ($base_object->id == $object->entity1_id) : ?>
      <?php echo link_to($object->description2 ? $object->description2 : $object->Category->default_description, 'relationship/view?id=' . $object->id) ?>
    <?php else : ?>
      <?php echo link_to($object->description1 ? $object->description1 : $object->Category->default_description, 'relationship/view?id=' . $object->id) ?>
    <?php endif; ?>
  </td>
  <td>
    <?php echo $object->getDetails() ?>
  </td>
  <td><?php echo $object->getDatespan() ?></td>
</tr>