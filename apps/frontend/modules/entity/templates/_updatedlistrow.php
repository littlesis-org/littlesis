<tr class="hover">
  <td style="vertical-align: top;">
    <span class="text_small"><?php echo user_link($object['LastUser']) ?>
    <?php echo time_ago_in_words(strtotime($object['updated_at'])) ?> ago</span>
  </td>
  <td style="vertical-align: top;">
    <?php echo entity_link($object, null) ?>
  </td>
  <td>
    <span style="font-size: 12px; font-style: italic;"><?php echo $object['blurb'] ?></span>
  </td>
</tr>