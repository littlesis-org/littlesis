<?php use_helper('LsNumber') ?>

<?php include_partial('relationship/membershiponeliner', array(
  'relationship' => $relationship,
  'profiled_entity' => $profiled_entity,
  'related_entity' => $related_entity
)) ?>

<?php $o = LsDoctrineQuery::create()
  ->from('Ownership o')
  ->where('o.relationship_id = ?', $relationship['id'])
  ->setHydrationMode(Doctrine::HYDRATE_ARRAY)
  ->fetchOne() ?>
  
<?php if ($stake = $o['percent_stake']) : ?>
  &sdot; <?php echo $stake . '% stake' ?>
<?php elseif ($shares = $o['shares']) : ?>
  &sdot; <?php echo readable_number($shares) . ' shares' ?> 
<?php endif; ?>