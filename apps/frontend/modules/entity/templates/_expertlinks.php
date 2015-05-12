<?php include_partial('global/section', array(
  'title' => 'Expert Tools'
)) ?>


<div class="padded margin_bottom">
<?php echo link_to('network search',EntityTable::getInternalUrl($entity, 'networkSearch')) ?><br>
<?php echo link_to('find connections',EntityTable::getInternalUrl($entity, 'findConnections')) ?><br>
<?php if ($entity['primary_ext'] == 'Org') : ?>
  <?php echo link_to('match related donors',EntityTable::getInternalUrl($entity, 'matchRelated')) ?><br>
<?php endif; ?>  
<?php echo link_to('twitter accounts', '@railsEditTwitter?id=' . $entity['id']) ?><br>
</div>