<?php include_partial('entity/basic', array('entity' => $entity)) ?>



<!-- RELATIONSHIPS -->
<?php include_partial('entity/relationshiptabs', array('entity' => $entity, 'active' => $sf_request->getParameter('action'))) ?>

<div id="relationship_tabs_content">
<?php if (!cache('relationship_tabs_content', 86400)) : ?>

  <?php if (isset($relationship_pager)) : ?>
    <?php include_partial('entity/relationshipSection', array(
      'entity' => $entity,
      'title' => isset($title) ? $title : null,
      'pager' => isset($pager) ? $pager : null,
      'more_action' => isset($more_action) ? $more_action : null,
      'pointer' => isset($pointer) ? $pointer : null  
    )) ?>
  <?php else : ?>
    <?php include_component('entity', $tab_name, array(
      'entity' => $entity,
      'page' => isset($page) ? $page : null,
      'num' => isset($num) ? $num : null,
      'title' => isset($title) ? $title : null,
      'pager' => isset($pager) ? $pager : null,
      'more_action' => isset($more_action) ? $more_action : null,
      'pointer' => isset($pointer) ? $pointer : null
    )) ?>
  <?php endif; ?>

  <?php cache_save() ?>

<?php endif; ?>
</div>


<!-- RELATIONSHIP JS -->

<script type="text/javascript">

function toggle_rels(id) {
  rels = $('#related_entity_relationships_' + id);
  rels.toggle('blind');
}

</script>