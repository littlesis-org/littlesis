<?php include_partial('relationship/membershiponeliner', array(
  'relationship' => $relationship,
  'profiled_entity' => $profiled_entity,
  'related_entity' => $related_entity
)) ?>

<?php $ed = LsDoctrineQuery::create()
  ->from('Education e')
  ->leftJoin('e.Degree d')
  ->where('e.relationship_id = ?', $relationship['id'])
  ->setHydrationMode(Doctrine::HYDRATE_ARRAY)
  ->fetchOne() ?>

<?php if ($ed['degree_id']) : ?>

  <?php $degree = $ed['Degree'] ?>
    
   &sdot; <?php if ($degree['abbreviation']) : ?>
       <?php echo $degree['abbreviation'] ?>
      <?php else : ?>
        <?php echo $degree['name'] ?>
      <?php endif; ?>
   <?php if ($ed['field']) : ?>
   (<?php echo $ed['field'] ?>)
   <?php endif; ?>
<?php endif; ?>