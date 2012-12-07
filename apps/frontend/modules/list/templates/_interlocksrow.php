<tr>
  <td>
    <?php echo entity_link($object, null) ?>
  </td>
  <td>
    <?php //$people = LsDoctrineQuery::create()->from('Entity e')->andWhereIn('e.id', explode(',', $object['people_ids']))->fetchArray() ?>
    <?php $people = EntityTable::getMultipleById(explode(',', $object['people_ids'])) ?>
    <?php $people_links = array() ?>
    <?php foreach ($people as $person) : ?>
      <?php $people_links[] = entity_link($person, null) ?>
    <?php endforeach; ?>  
    <?php echo implode(', ', $people_links) ?>
  </td>
</tr>