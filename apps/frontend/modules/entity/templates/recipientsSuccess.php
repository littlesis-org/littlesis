<?php include_partial('global/section', array(
  'title' => 'Donation/Grant Recipients',
  'pager' => $recipient_pager,
  'pointer' => 'People and orgs ' . $entity->name . ' has donated to'
)) ?>

<div class="padded">
<?php foreach ($recipient_pager->execute() as $id => $ary) : ?>
  <?php include_partial('entity/entitywithrelationships', array(
    'related_entity' => $ary['entity'],
    'relationships' => $ary['rels'],
    'profiled_entity' => $entity
  )) ?>
<?php endforeach; ?>
</div>