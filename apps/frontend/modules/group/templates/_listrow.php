<tr class="hover">
  <td class="padded"><strong><?php echo group_link($object) ?></strong></td>
  <td class="padded"><?php echo $object['users'] ?></td>
  <td class="padded"><?php echo $object['blurb'] ?></td>
  <td class="padded"><nobr><?php echo date('M j, Y', strtotime($object['created_at'])) ?></nobr></td>
</tr>