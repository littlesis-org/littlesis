<?php include_partial('global/section', array(
  'title' => 'Admin Stuff'
)) ?>


<div class="padded margin_bottom">
<?php echo link_to('addresses',EntityTable::getInternalUrl($entity, 'addresses')) ?>
</div>