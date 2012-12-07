<tr>
  <td>
    <?php echo entity_link($object, null) ?>
    <?php $rels = LsDoctrineQuery::create()->from('Relationship r')->andWhereIn('r.id', explode(',', $object['relationship_ids']))->fetchArray() ?>
    <?php $rel_links = array() ?>
    <?php foreach ($rels as $rel) : ?>
      <?php $rel_links[] = trim(get_partial('relationship/oneliner', array(
        'relationship' => $rel,
        'profiled_entity' => $base_object,
        'related_entity' => $object
      ))) ?>
    <?php endforeach; ?>  
    <?php echo implode(' ', $rel_links) ?>
  </td>
</tr>