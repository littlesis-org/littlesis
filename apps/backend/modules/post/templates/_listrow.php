<tr>
  <td><?php echo $object->created_at ?></td>
  <td><?php echo user_link($object->User, 'frontend') ?></td>
  <td><?php echo $object->module ?></td>
  <td><?php echo $object->action ?></td>
  <td title="<?php echo $object->params ?>"><?php echo excerpt($object->params, 80) ?></td>
  <td>
    <?php if (!$sf_request->getParameter('user_id')) : ?>
      <?php echo link_to('limit by user', 'post/list?user_id=' . $object->user_id) ?>
    <?php endif; ?>
  </td>
</tr>