<?php if ($description1 = $relationship['description1']) : ?>
  <?php echo $description1 ?>
<?php else : ?>
  <?php echo RelationshipTable::getCategoryDefaultDescription($relationship) ?>
<?php endif; ?>

<?php if ($amount = $relationship['amount']) : ?>
 &sdot; $<?php echo $amount ?>
<?php endif; ?>

<?php if ($count = $relationship['filings']) : ?>
 <?php if ($count == 1) : ?>
 &sdot; <?php echo $count ?> filing 
 <?php else : ?>
 &sdot; <?php echo $count ?> filings
 <?php endif; ?>
<?php endif; ?>

<?php include_partial('relationship/onelinerdate', array('relationship' => $relationship)) ?>