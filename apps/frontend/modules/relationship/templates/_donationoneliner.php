<?php if ($description1 = $relationship['description1']) : ?>
  <?php echo $description1 == "Campaign Contribution" ? $relationship['filings'] . " contribution" . ($relationship['filings'] > 1 ? "s" : "") : $description1 ?>
<?php else : ?>
  <?php echo RelationshipTable::getCategoryDefaultDescription($relationship) ?>
<?php endif; ?>

<?php if ($amount = $relationship['amount']) : ?>
 &sdot; $<?php echo $amount ?>
<?php endif; ?>

<?php include_partial('relationship/onelinerdate', array('relationship' => $relationship)) ?>