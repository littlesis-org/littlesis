<tr>
  <td>
    <strong><?php echo link_to($object->getOneLiner(), 'entity/addressModifications?id=' . $object->id) ?></strong>
  </td>
  <td>
    <?php echo $object->created_at ?> by <?php echo user_link($object->getCreatedByUser()) ?>
  </td>
  <td>
    <?php echo time_ago_in_words(strtotime($object->updated_at)) ?>
  </td>
  <td>
    <?php echo $object->is_deleted ? 'deteled' : 'active' ?>
  </td>
</td>