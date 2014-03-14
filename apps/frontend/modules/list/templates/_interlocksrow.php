<tr>
  <td style="width: 30%; padding-bottom: 0.6em">
    <?php echo entity_link($object, null) ?>
  </td>
  <td style="padding-bottom: 0.6em">
    <?php //$people = LsDoctrineQuery::create()->from('Entity e')->andWhereIn('e.id', explode(',', $object['people_ids']))->fetchArray() ?>
    <?php $people = EntityTable::getMultipleById(explode(',', $object['people_ids'])) ?>
    <?php $people_links = array() ?>
    <?php foreach ($people as $person) : ?>
      <?php $people_links[] = entity_link($person, null,false) ?>
    <?php endforeach; ?>  
    <?php echo implode(', ', $people_links) ?>
  </td>
</tr>