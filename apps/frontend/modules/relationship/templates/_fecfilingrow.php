<tr class="text_small hover<?php echo $shaded ? ' shaded' : '' ?>">
  <td><?php echo $object->getStartDateForDisplay() ?></td>
  <td><?php echo LsNumber::makeReadable($object->amount, '$') ?></td>
  <td><?php echo $object->fec_filing_id ?></td>
  <td>
    <?php if ($url = $object->getSourceUrl()) : ?>
      <?php echo link_to('Source', $url) ?>
    <?php endif; ?>
  </td>
</tr>