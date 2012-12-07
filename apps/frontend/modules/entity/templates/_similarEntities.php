<?php if (isset($similar_entities)) : ?>


<?php include_partial('global/section', array(
  'title' => 'Similar Entities'
)) ?>

<div class="padded">

<?php if (!count($similar_entities)) : ?>
  No similar names found.
  <br />
  <div style="padding-top: .5em">
  <?php echo link_to('Look for possible merges &raquo;', EntityTable::getInternalUrl($entity, 'merge')) ?>
  </div>
<?php else : ?>
  <div class="padded">
  <?php foreach($similar_entities as $similar_entity) : ?>
    <?php echo entity_link($similar_entity, null) ?>
    <br />
  <?php endforeach; ?>
  <br />
  <?php echo link_to('Begin merging process &raquo;', EntityTable::getInternalUrl($entity, 'merge')) ?>
  </div>
<?php endif; ?>

</div>

<br />

<?php endif; ?>