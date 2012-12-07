<tr class="text_small hover<?php echo $shaded ? ' shaded' : '' ?>">
  <td><?php echo $object->start_date ?></td>
  <td><?php echo LsNumber::makeReadable($object->amount, '$') ?></td>
  <td><?php echo excerpt($object->goods, 200) ?></td>
  <td>
    <?php echo link_to('Source', $object->getSourceUrl()) ?>
  </td>
</tr>