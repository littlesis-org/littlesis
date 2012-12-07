<tr>
  <td>
    <?php echo entity_link($object, null) ?>
  </td>
  <td>
    <?php $networkLinks = array() ?>
    <?php foreach (LsDoctrineQuery::create()->from('Entity e')->andWhereIn('e.id', explode(',', $object->getResultValue('first_degree_ids')))->execute() as $networkEntity) : ?>
      <?php $networkLinks[] = entity_link($networkEntity, null) ?>
    <?php endforeach; ?>
    <?php echo implode(', ', $networkLinks) ?>
  </td>
</tr>