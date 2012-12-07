<?php use_helper('LsText', 'Date') ?>

<tr class="hover" style="vertical-align: top;">
  <td><?php echo time_ago_in_words(strtotime($object['created_at'])) ?> ago</td>
  <td><?php echo $object['name_first'] . ' ' . $object['name_last'] ?></td>
  <td><?php echo $object['email'] ?></td>
  <td><?php echo $object['reason'] ?></td>
  <td><?php echo link_to('approve', 'api/approveUser?id=' . $object['id'], 'post=true') ?></td>
</tr>