<tr>
  <td>
    <?php echo entity_link($object, null) ?>
  </td>
  <td>
    <?php $networkLinks = array() ?>
    <?php $commonExt = ($base_object->getPrimaryExtension() == 'Person') ? 'Org' : 'Person' ?>
    <?php $query = call_user_func(array($base_object, 'getCommon' . $commonExt . 'sByPositionQuery'), $object) ?>
    <?php foreach ($query->execute() as $networkEntity) : ?>
      <?php $networkLinks[] = entity_link($networkEntity, null) ?>
    <?php endforeach; ?>
    <?php echo implode(', ', $networkLinks) ?>
  </td>
</tr>