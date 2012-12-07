<div class="related_entity">
<?php echo entity_link($related_entity) ?> &bull;
<?php $rels = array() ?>

<?php if (isset($related_entity['rel_ids'])) : ?>
  <?php $q = LsDoctrineQuery::create()
    ->from('Relationship r')
    ->whereIn('r.id', explode(',', $related_entity['rel_ids']))
    ->orderBy('r.start_date DESC, r.end_date DESC')
    ->setHydrationMode(Doctrine::HYDRATE_ARRAY) ?>
<?php elseif (isset($via_entity_ids)) : ?>
  <?php $q = RelationshipTable::getBetweenEntityAndGroupQuery($related_entity, $via_entity_ids, isset($category_ids) ? $category_ids : null, isset($order) ? $order : null) ?>
<?php else : ?>
  <?php $q = $profiled_entity->getRelationshipsWithQuery($related_entity, isset($category_ids) ? $category_ids : null, null, null, null, isset($order) ? $order : null) ?>
<?php endif; ?>

<?php foreach ($q->fetchArray() as $relationship) : ?>
  <?php $rels[] = trim(get_partial('relationship/oneliner', array(
    'relationship' => $relationship,
    'profiled_entity' => $profiled_entity,
    'related_entity' => $related_entity
  ))) ?>
  <?php die ?>
<?php endforeach; ?>

<?php echo implode(', ', $rels) ?>
</div>