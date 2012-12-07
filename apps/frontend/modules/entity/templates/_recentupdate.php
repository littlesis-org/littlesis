<div class="entity_update">
  <?php if ($entity->primary_ext == 'Person' && count($images = $entity->Image)) : ?>
  <?php $image = $images[0] ?>
  <div class="entity_update_image">
    <?php echo link_to(image_tag($image->getSmallPath(), array('alt' => '', 'style' => 'height: 35px; border: 0px solid #fff')), $entity->getInternalUrl(), array('title' => $entity->name)) ?>
  </div>
  <?php endif; ?>
  
  <?php echo entity_link($entity, 'entity-update-link') ?>
  <br />
  &nbsp; &nbsp; <span class="text_small">updated by <?php echo user_link($entity->LastUser ? $entity->LastUser : $entity->getLastModifiedUser()) ?>
  <?php echo time_ago_in_words(strtotime($entity->updated_at)) ?> ago</span>
</div>
