<?php use_helper('LsText', 'Date') ?>

<tr class="hover" style="vertical-align: top;">
  <td><?php echo $object['name_first'] . ' ' . $object['name_last'] ?></td>
  <td><?php echo $object['email'] ?></td>
  <td><?php echo $object['num_ips'] ?></td>
  <td><?php echo time_ago_in_words(strtotime($object['last_request'])) ?> ago</td>
  <td><?php echo link_to($object['num_requests'] . ' requests', 'api/log?user_id=' . $object['id']) ?></td>
</tr>