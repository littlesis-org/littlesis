<tr>
  <td>
    <?php echo entity_link($object, null) ?>
  </td>
  <td>
    <?php echo LsNumber::makeReadable($object['degree1_total'], '$') ?>
  </td>
  <td>
    <?php $people = EntityTable::getMultipleById(explode(',', $object['degree1_ids'])) ?>
    <?php $people_links = array() ?>
    <?php foreach ($people as $person) : ?>
      <?php $people_links[] = entity_link($person, null) ?>
    <?php endforeach; ?>  
    <?php echo implode(', ', $people_links) ?>
  </td>
</tr>