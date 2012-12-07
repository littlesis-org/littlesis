<?php include_partial('global/subsection', array(
  'title' => 'Donors to Political Fundraising Committees',
  'pager' => $political_donor_pager,
  'pointer' => 'People who have given ' . $entity->name . ' political donations'
)) ?>

<div class="padded">
<?php foreach ($political_donor_pager->execute() as $related_entity) : ?>
  <?php include_partial('entity/relatedentity', array(
    'profiled_entity' => $entity,
    'related_entity' => $related_entity,
    'category_ids' => array(RelationshipTable::DONATION_CATEGORY),
    'order' => 1,
    'via_entity_ids' => $fundraising_committee_ids
  )) ?>
<?php endforeach; ?>
</div>