<div id="no-relationships" class="cleanup text_big">
This <?php echo strtolower($entity['primary_ext']) ?> has no relationships. Be the first to 
<?php echo link_to('add one', EntityTable::generateRoute($entity, 'addRelationship')) ?>.
</div>
