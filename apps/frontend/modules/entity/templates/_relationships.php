<?php use_helper('Pager') ?>


<?php if ($entity['primary_ext'] == 'Person') : ?>

<?php if (!count($rels)) : ?>
  <?php include_partial('entity/norelationships', array('entity' => $entity)) ?>
<?php endif; ?>


<?php $sections = array(
  'Government Positions' => array(
    'more_action' => 'government',
    'category_ids' => array(RelationshipTable::POSITION_CATEGORY, RelationshipTable::MEMBERSHIP_CATEGORY),
    'extensions' => array('GovernmentBody'),
    'pointer' => 'Government bodies ' . $entity['name'] . ' has served in'
  ),
  'Business Positions' => array(
    'more_action' => 'business',
    'category_ids' => array(RelationshipTable::POSITION_CATEGORY, RelationshipTable::MEMBERSHIP_CATEGORY),
    'extensions' => array('Business'),
    'pointer' => 'Companies ' . $entity['name'] . ' has had a position in'
  ),
  'Other Positions & Memberships' => array(
    'more_action' => 'otherPositions',
    'category_ids' => array(RelationshipTable::POSITION_CATEGORY, RelationshipTable::MEMBERSHIP_CATEGORY),
    'exclude_extensions' => array('GovernmentBody', 'Business', 'Person'),
    'pointer' => 'Positions & memberships ' . $entity['name'] . ' has had outside of business & government'
  ),
  'Education' => array(
    'more_action' => 'education',
    'category_ids' => array(RelationshipTable::EDUCATION_CATEGORY),
    'pointer' => 'Schools ' . $entity['name'] . ' has attended',
  ),

  'Holdings' => array(
    'more_action' => 'holdings',
    'category_ids' => array(RelationshipTable::OWNERSHIP_CATEGORY),
    'pointer' => 'Orgs that ' . $entity['name'] . ' owns at least a piece of',
  ),
  'Services/Transactions' => array(
    'more_action' => 'transactions',
    'category_ids' => array(RelationshipTable::TRANSACTION_CATEGORY),
    'pointer' => 'People and orgs ' . $entity['name'] . ' has done business with',
    'order_by_amount' => true
  ),
  'Family' => array(
    'more_action' => 'family',
    'category_ids' => array(RelationshipTable::FAMILY_CATEGORY),
    'pointer' => 'People in the same family as ' . $entity['name'],
  ),
  'Professional Associates' => array(
    'more_action' => 'friends',
    'category_ids' => array(RelationshipTable::PROFESSIONAL_CATEGORY),
    'pointer' => 'People ' . $entity['name'] . ' has worked with closely'
  ),
  'In The Office Of' => array(
    'more_action' => 'officeOf',
    'category_ids' => array(RelationshipTable::POSITION_CATEGORY),
    'extensions' => array('Person'),
    'order' => 1,
    'pointer' => 'People ' . $entity['name'] . ' has worked for directly',
  ),
  'Friends & Colleagues' => array(
    'more_action' => 'friends',
    'category_ids' => array(RelationshipTable::SOCIAL_CATEGORY),
    'pointer' => 'People with close social ties to ' . $entity['name']
  ),
  'Donors' => array(
    'more_action' => 'donors',
    'category_ids' => array(RelationshipTable::DONATION_CATEGORY),
    'exclude_extensions' => array('PoliticalFundraising'),
    'order' => 2,
    'pointer' => 'People and orgs who have donated to ' . $entity['name']  . 'directly',
    'order_by_amount' => true
  ),
  'Donation/Grant Recipients' => array(
    'more_action' => 'recipients',
    'category_ids' => array(RelationshipTable::DONATION_CATEGORY),
    'order' => 1,
    'pointer' => 'People and orgs ' . $entity['name'] . ' has donated to',
    'order_by_amount' => true
  ),
  'Office/Staff' => array(
    'more_action' => 'office',
    'category_ids' => array(RelationshipTable::POSITION_CATEGORY),
    'extensions' => array('Person'),
    'order' => 2,
    'pointer' => 'People who have worked for ' . $entity['name'] . ' directly'
  ),
  'Political Fundraising Committees' => array(
    'more_action' => 'fundraising',
    'category_ids' => array(RelationshipTable::DONATION_CATEGORY),
    'extensions' => array('PoliticalFundraising'),
    'order' => 2,
    'pointer' => 'Orgs that have raised political donations for ' . $entity['name']
  ),
  'Lobbied By' => array(
    'more_action' => 'lobbiedBy',
    'category_ids' => array(RelationshipTable::LOBBYING_CATEGORY),
    'order' => 2,
    'pointer' => $entity['name'] . ' has been lobbied by:'
  ),
  'Targets of Lobbying' => array(
    'more_action' => 'lobbyingTargets',
    'category_ids' => array(RelationshipTable::LOBBYING_CATEGORY),
    'order' => 1,
    'pointer' => 'Officials and agencies ' . $entity['name'] . ' has lobbied'
  )
) ?>




<?php else : ?>

<?php if (!count($rels)) : ?>
  <?php include_partial('entity/norelationships', array('entity' => $entity)) ?>
<?php endif; ?>


<?php $sections = array(
  'Parent Organizations' => array(
    'more_action' => 'parentOrgs',
    'category_ids' => array(RelationshipTable::HIERARCHY_CATEGORY),
    'order' => (1)
  ),
  'Child Organizations' => array(
    'more_action' => 'childOrgs',
    'category_ids' => array(RelationshipTable::HIERARCHY_CATEGORY),
    'order' => (2)
  ),
  'Holdings' => array(
    'more_action' => 'holdings',
    'category_ids' => array(RelationshipTable::OWNERSHIP_CATEGORY),
    'order' => 1,
    'pointers' => 'Orgs that ' . $entity['name'] . ' owns at least a piece of'
  ),
  'Memberships' => array(
    'more_action' => 'memberships',
    'category_ids' => array(RelationshipTable::MEMBERSHIP_CATEGORY),
    'order' => 1,
    'pointer' => $entity['name'] . ' belongs to these umbrella groups'
  ),
  'Owners' => array(
    'more_action' => 'owners',
    'category_ids' => array(RelationshipTable::OWNERSHIP_CATEGORY),
    'order' => 2,
    'pointers' => 'People and orgs with ownership in ' . $entity['name']
  ),
  'Leadership & Staff' => array(
    'more_action' => 'leadership',
    'category_ids' => array(RelationshipTable::POSITION_CATEGORY),
    'order' => 2,
    'pointer' => 'People with official positions in ' . $entity['name'],
    'actions' => array(
      'board & execs &raquo;' => EntityTable::getInternalUrl($entity, 'board')
    )
  ),
  'Recipients' => array(
    'more_action' => 'recipients',
    'category_ids' => array(RelationshipTable::DONATION_CATEGORY),
    'order' => 1,
    'pointer' => $entity['name'] . ' has donated to:'
  ),
  'Members' => array(
    'more_action' => 'members',
    'category_ids' => array(RelationshipTable::MEMBERSHIP_CATEGORY),
    'order' => 2,
    'pointer' => 'Members of ' . $entity['name'] . ' without official positions'
  ),
  'Services/Transactions' => array(
    'more_action' => 'transactions',
    'category_ids' => array(RelationshipTable::TRANSACTION_CATEGORY),
    'pointer' => 'People and orgs ' . $entity['name'] . ' has done business with',
    'order_by_amount' => true
  ),
  'Donors' => array(
    'more_action' => 'donors',
    'category_ids' => array(RelationshipTable::DONATION_CATEGORY),
    'order' => 2,
    'pointer' => $entity['name'] . ' has received donations from:',
    'order_by_amount' => true
  ),
  'Students' => array(
    'more_action' => 'students',
    'category_ids' => array(RelationshipTable::EDUCATION_CATEGORY),
    'order' => 2,
    'pointer' => 'People who have attended ' . $entity['name']
  ),
  'Lobbied By' => array(
    'more_action' => 'lobbiedBy',
    'category_ids' => array(RelationshipTable::LOBBYING_CATEGORY),
    'order' => 2,
    'pointer' => $entity['name'] . ' has been lobbied by:'
  ),
  'Targets of Lobbying' => array(
    'more_action' => 'lobbyingTargets',
    'category_ids' => array(RelationshipTable::LOBBYING_CATEGORY),
    'order' => 1,
    'pointer' => 'Officials and agencies ' . $entity['name'] . ' has lobbied'
  )
) ?>

<?php endif; ?>



<?php foreach ($sections as $title => $ary) : ?>

<?php if (isset($ary['pager'])) : ?>

<?php if ($ary['pager']->getNumResults()) : ?>

<?php include_partial('global/subsection', array(
  'title' => $title,
  'pager' => $ary['pager'],
  'more' => EntityTable::getInternalUrl($entity, $ary['more_action']),
  'pointer' => isset($ary['pointer']) ? $ary['pointer'] : null,
  'actions' => isset($ary['actions']) ? $ary['actions'] : null
)) ?>

<div class="padded margin_bottom">
<?php foreach ($ary['pager']->execute() as $related_entity) : ?>
  <?php include_partial('entity/entitywithrelationships', array(
    'related_entity' => $related_entity,
    'relationships' => $related_entity['rels'],
    'profiled_entity' => $entity
  )) ?>
<?php endforeach; ?>
</div>
<br />

<?php endif; ?>

<?php else : ?>

<?php include_component('entity', 'relationshipSection', array(
  'entity' => $entity,
  'rels' => $rels,
  'category_ids' => $ary['category_ids'],
  'order' => isset($ary['order']) ? $ary['order'] : null,
  'extensions' => isset($ary['extensions']) ? $ary['extensions'] : null,
  'exclude_extensions' => isset($ary['exclude_extensions']) ? $ary['exclude_extensions'] : null,
  'order_by_num' => isset($ary['order_by_amount']) ? false : true,
  'order_by_amount' => isset($ary['order_by_amount']) ? true : false,
  'more_action' => $ary['more_action'],
  'title' => $title,
  'actions' => isset($ary['actions']) ? $ary['actions'] : null
)) ?>

<?php endif; ?>

<?php endforeach; ?>