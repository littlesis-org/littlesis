<tr>
  <td>
    <?php echo entity_link($object, null) ?>
  </td>
  <td>
    <?php echo LsNumber::makeReadable($object['total'], '$') ?>
  </td>
  <td>
    <?php $recipientLinks = array() ?>
    <?php foreach (LsDoctrineQuery::create()->from('Entity e')->andWhereIn('e.id', explode(',', $object['degree1_ids']))->fetchArray() as $recipient) : ?>
      <?php $recipientLinks[] = entity_link($recipient, null) ?>
    <?php endforeach; ?>
    <?php echo implode(', ', $recipientLinks) ?>
  </td>
</tr>