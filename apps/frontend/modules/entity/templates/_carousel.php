<?php use_helper('LsText') ?>

<?php if ($entity) : ?>
<div class="entity-carousel">
  <div class="entity-carousel-image" style="background-image: url(<?php echo image_path('large' . DIRECTORY_SEPARATOR . $entity['image_path']) ?>);">
    <?php //$anonFile = ($entity['primary_ext'] == 'Person') ? 'anon.png' : 'anons.png' ?>
    <?php //$image_path = $entity['image_path'] ? 'profile' . DIRECTORY_SEPARATOR . $entity['image_path'] : 'system' . DIRECTORY_SEPARATOR . $anonFile ?>
    <?php //echo link_to(image_tag($image_path, array('alt' => '')), EntityTable::generateRoute($entity), array('title' => $entity['name'])) ?>
  </div>

  <div class="entity-carousel-text">
    <?php echo entity_link($entity, 'entity-carousel-name') ?><br />
  
    <?php if ($entity['blurb']) : ?>
      <span class="entity-carousel-description"><?php echo $entity['blurb'] ?><br /></span>
    <?php endif; ?>

    <br />
  
    <?php if ($entity['primary_ext'] == 'Person') : ?>
      <span class="entity-carousel-count">Key affiliations: </span>&nbsp;
    <?php else : ?>
      <span class="entity-carousel-count">Key people: </span>&nbsp;    
    <?php endif; ?>

    <?php $connection_links = array() ?>
    <?php foreach ($connections as $related) : ?>
      <?php $connection_links[] = entity_link($related, 'entity-carousel-related', false, excerpt($related['name'], 40)) ?>
    <?php endforeach; ?>
    <?php $connection_links[] = '<nobr><strong>' . entity_link($entity, 'entity-carousel-related', false, 'More &raquo;') . '</strong></nobr>' ?>
    
    <?php echo implode(" &nbsp;&bull;&nbsp; ", $connection_links) ?>

    <br />
    <br />
  
    <span class="entity-carousel-count">Related <?php echo $entity['primary_ext'] == 'Person' ? 'people' : 'orgs' ?>: </span>&nbsp;
   
    <?php $related_links = array() ?>
    <?php foreach ($interlocks as $related) : ?>
      <?php $related_links[] = entity_link($related, 'entity-carousel-related', false, excerpt($related['name'], 40)) ?>
    <?php endforeach; ?>
    <?php $related_links[] = '<nobr><strong>' . link_to('More &raquo;', EntityTable::generateRoute($entity, 'interlocks')) . '</strong></nobr>' ?>
    
    <?php echo implode(" &nbsp;&bull;&nbsp; ", $related_links) ?>
  </div>
</div>
<?php endif; ?>