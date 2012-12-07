<tr>
  <td><?php echo $object['ApiUser']['name_first'] . ' ' . $object['ApiUser']['name_last'] ?></td>
  <td><?php echo link_to($object['ApiUser']['email'], 'api/log?user_id=' . $object['ApiUser']['id']) ?></td>
  <td><?php echo excerpt($object['resource'], 70) ?></td>
  <td><?php echo $object['updated_at'] ?></td>
</tr>