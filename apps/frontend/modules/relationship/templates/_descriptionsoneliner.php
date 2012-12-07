<?php if ($profiled_entity) : ?>
  <?php if ($profiled_entity['id'] == $relationship['entity1_id']) : ?>
    <?php if ($description = $relationship['description2']) : ?>
      <?php echo $description ?>    
    <?php else : ?>
      <?php echo RelationshipTable::getCategoryDefaultDescription($relationship) ?>        
    <?php endif; ?>
  <?php elseif ($profiled_entity['id'] == $relationship['entity2_id']) : ?>
    <?php if ($description = $relationship['description1']) : ?>
      <?php echo $description ?>    
    <?php else : ?>
      <?php echo RelationshipTable::getCategoryDefaultDescription($relationship) ?>        
    <?php endif; ?>
  <?php endif; ?>
<?php else : ?>
  <?php echo RelationshipTable::getCategoryDefaultDescription($relationship) ?>        
<?php endif; ?>

<?php include_partial('relationship/onelinerdate', array('relationship' => $relationship)) ?>