<tr>
  <td>
    <?php echo entity_link($object, null) ?>
  </td>
  <td>
    <?php $donorLinks = array() ?>
    <?php foreach (LsDoctrineQuery::create()->from('Entity e')->andWhereIn('e.id', explode(',', $object->getResultValue('first_degree_ids')))->execute() as $donor) : ?>
      <?php $donorLinks[] = entity_link($donor, null) ?>
    <?php endforeach; ?>
    <?php echo implode(', ', $donorLinks) ?>
  </td>
</tr>