<tr>
  <td style="padding-bottom: .6em">
    <?php echo entity_link($object, null) ?>
  </td>
  <td style="padding-bottom: .6em">
    <?php $recipientLinks = array() ?>
    <?php $recipients = LsDoctrineQuery::create()->from('Entity e')->andWhereIn('e.id', explode(',', $object['degree1_ids']))->fetchArray() ?>
    <?php foreach ($recipients as $recipient) : ?>
      <?php $recipientLinks[] = entity_link($recipient, null,false) ?>
    <?php endforeach; ?>
    <?php echo implode(', ', $recipientLinks) ?>
  </td>
</tr>
