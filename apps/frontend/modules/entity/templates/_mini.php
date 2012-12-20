<?php use_helper('LsText') ?>

<?php if ($entity) : ?>
<div class="entity-mini" <?php echo isset($border) ? 'style="border: ' . $border . ';"' : '' ?>>
  <div class="entity-mini-image">
    <?php $anonFile = ($entity['primary_ext'] == 'Person') ? 'mini-anon.png' : 'mini-anons.png' ?>
    <?php $image_path = $entity['image_path'] ? 'profile' . DIRECTORY_SEPARATOR . $entity['image_path'] : 'system' . DIRECTORY_SEPARATOR . $anonFile ?>
    <?php echo link_to(image_tag($image_path, array('alt' => '')), EntityTable::generateRoute($entity), array('title' => $entity['name'])) ?>
  </div>

  <div class="entity-mini-text">
    <?php echo entity_link($entity, 'entity-mini-name') ?><br />
  
    <?php if ($entity['blurb']) : ?>
      <span class="entity-mini-description"><?php echo $entity['blurb'] ?><br /></span>
    <?php endif; ?>

    <span style="font-size: 6px;"><br /></span>
  
    <span class="entity-mini-count"><?php echo $relationship_count ?> <?pho echo __("relationships") ?>:</span>
   
    <?php $related_links = array() ?>
    <?php foreach ($sample_relateds as $related) : ?>
      <?php $related_links[] = entity_link($related, 'entity-mini-related', false, excerpt($related['name'], 40)) ?>
    <?php endforeach; ?>
    <?php if (count($relateds) > 3) : ?>
      <?php $related_links[] = '<nobr>' . entity_link($entity, 'entity-mini-related', false, __('More') . ' &raquo;') . '</nobr>' ?>
    <?php endif; ?>
    
    <?php echo implode(" &bull; ", $related_links) ?>
  </div>
</div>
<?php endif; ?>