<tr>
  <td>
    <?php echo entity_link($object, null) ?>
  </td>
  <td>
    <?php $entities = LsDoctrineQuery::create()->from('Entity e')->andWhereIn('e.id', explode(',', $object['degree1_ids']))->execute() ?>
    <?php $entity_links = array() ?>
    <?php foreach ($entities as $entity) : ?>
      <?php $entity_links[] = entity_link($entity, null) ?>
    <?php endforeach; ?>  
    <?php echo implode(', ', $entity_links) ?>
  </td>
</tr>