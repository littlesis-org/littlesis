<?php include_partial('global/section', array(
  'title' => 'Services/Transactions',
  'pager' => $transaction_pager,
  'pointers' => 'People and orgs ' . $entity->name . ' has done business with'
)) ?>

<div class="padded">
<?php foreach ($transaction_pager->execute() as $id => $ary) : ?>
  <?php include_partial('entity/entitywithrelationships', array(
    'related_entity' => $ary['entity'],
    'relationships' => $ary['rels'],
    'profiled_entity' => $entity
  )) ?>
<?php endforeach; ?>
</div>