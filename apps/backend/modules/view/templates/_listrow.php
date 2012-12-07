<tr>
  <td><?php echo $object->created_at ?></td>
  <td>
    <?php echo user_link($object->User, 'frontend') ?>
  </td>
  <td>
    <strong><?php echo object_link($object->getObject(true), 'frontend') ?></strong>
  </td>
  <td>
    <?php if (!$sf_request->getParameter('user_id')) : ?>
      <span class="text_small"><?php echo link_to('limit by user', 'view/list?user_id=' . $object->user_id . '&object_model=' . $sf_request->getParameter('object_model') . '&object_id=' . $sf_request->getParameter('object_id')) ?></span>  
      &nbsp;
    <?php endif; ?>

    <?php if (!$sf_request->getParameter('object_model') || !$sf_request->getParameter('object_id')) : ?>
      <span class="text_small"><?php echo link_to('limit to page', 'view/list?user_id=' . $sf_request->getParameter('user_id') . '&object_model=' . $object->object_model . '&object_id=' . $object->object_id) ?></span>
    <?php endif; ?>
  </td>
</tr>