<?php $unique = microtime(true) . "_" . $related_entity["id"] ?>
<?php $entity_length = strlen($related_entity["name"]) ?>
<?php $blurb_length = 85 - round($entity_length * 1.2) ?>

<div class="related_entity">
  <div class="related_entity_entity">
    <?php echo entity_link($related_entity) ?>

    <?php if (isset($related_entity["blurb"])) : ?>
      &nbsp;
      <span class="related_entity_blurb">
        <?php echo excerpt($related_entity['blurb'], $blurb_length) ?>
      </span>
    <?php endif; ?>
  </div>

  <div class="related_entity_relationship">
    <?php $first = array_shift($relationships) ?>
	  <?php $order = $first["entity1_id"] == $profiled_entity["id"] ? 1 : 2 ?>

    <li>	
    <?php echo trim(get_partial('relationship/oneliner', array(
      'relationship' => $first,
      'profiled_entity' => $profiled_entity,
      'related_entity' => $related_entity
    ))) ?>

    <?php if (count($relationships) > 0) : ?>
      &nbsp;
	    [<a class="toggler" href="javascript:void(0);" onclick="toggle_rels('<?php echo $unique ?>')">
	      +<?php echo count($relationships) ?>
	    </a>]
	  <?php endif; ?>
    </li>

  </div>

  <?php if (count($relationships) > 0) : ?>
    <div id="related_entity_relationships_<?php echo $unique ?>" class="related_entity_relationships" style="display: none;">
    <?php foreach ($relationships as $rel) : ?>
      <li>
      <?php echo trim(get_partial('relationship/oneliner', array(
        'relationship' => $rel,
        'profiled_entity' => $profiled_entity,
        'related_entity' => $related_entity
      ))) ?> 
      </li>
    <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
