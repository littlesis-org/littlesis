<?php use_helper('LsNumber') ?>

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

<?php if ($amount = $relationship['amount']) : ?>
 &sdot; <?php echo '$' . readable_number($amount) ?>
<?php endif; ?>

<?php if ($count = $relationship['filings']) : ?>
 <?php if ($count == 1) : ?>
 &sdot; <?php echo $count ?> filing 
 <?php else : ?>
 &sdot; <?php echo $count ?> filings
 <?php endif; ?>
<?php endif; ?>

<?php include_partial('relationship/onelinerdate', array('relationship' => $relationship)) ?>