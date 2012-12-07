<?php include_partial('relationship/header', array('relationship' => $relationship, 'show_actions' => true)) ?>


<?php include_partial('relationship/rightcol', array('relationship' => $relationship)) ?>


<?php if (!cache('main', 86400)) : ?>

<div class="relationship-main">
<?php include_partial('relationship/' . strtolower(RelationshipCategoryTable::getNameById($relationship['category_id'])) . 'view', array(
  'relationship' => $relationship, 'current' => $current
)) ?>
</div>

<?php cache_save() ?>
<?php endif; ?>
