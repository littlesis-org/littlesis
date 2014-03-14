<tr>
  <td style="width: 30%; padding-bottom: 0.6em">
    <?php echo entity_link($object, null) ?>
  </td>
  <td style="padding-bottom: 0.6em">
    <?php $networkLinks = array() ?>
    <?php foreach (LsDoctrineQuery::create()->from('Entity e')->andWhereIn('e.id', explode(',', $object->getResultValue('first_degree_ids')))->execute() as $networkEntity) : ?>
      <?php $networkLinks[] = entity_link($networkEntity, null, false) ?>
    <?php endforeach; ?>
    <?php echo implode(', ', $networkLinks) ?>
  </td>
</tr>