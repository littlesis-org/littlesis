<?php if ($description1 = $relationship['description1']) : ?>
  <?php echo $description1 ?>
<?php else : ?>
  <?php echo RelationshipTable::getCategoryDefaultDescription($relationship) ?>
<?php endif; ?>

<?php include_partial('relationship/onelinerdate', array('relationship' => $relationship)) ?>