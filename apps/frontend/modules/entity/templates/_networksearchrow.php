<tr>
  <td style="width: 28%; padding-bottom: 0.6em">
    <?php echo entity_link($object, null) ?><br>
  </td>
  <td style="padding-bottom: 0.6em">
    <?php $entities = LsDoctrineQuery::create()->from('Entity e')->andWhereIn('e.id', explode(',', $object['degree1_ids']))->execute() ?>
    <?php $entity_links = array() ?>
    <?php foreach ($entities as $entity) : ?>
      <?php $entity_links[] = entity_link($entity, null,false) ?>
    <?php endforeach; ?>  
    <?php echo implode(', ', $entity_links) ?>

  </td>
</tr>
