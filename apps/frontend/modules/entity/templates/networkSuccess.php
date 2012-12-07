<?php use_helper('Pager') ?>


<?php $primary = $entity->getPrimaryExtension() ?>
<?php $title = ($primary == 'Person') ? 'People in Common Orgs' : 'Orgs with Common People' ?>
<?php $commonStr = ($primary == 'Person') ? 'Common Orgs' : 'Common People' ?>


<?php include_partial('global/section', array('title' => $title)) ?>

<?php include_partial('global/table', array(
  'columns' => array($primary, $commonStr),
  'pager' => $entity_pager,
  'row_partial' => 'entity/networkrow',
  'base_object' => $entity
)) ?>
