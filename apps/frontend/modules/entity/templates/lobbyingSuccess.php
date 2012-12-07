<?php use_helper('LsJavascript') ?>

<?php echo ls_link_to_remote(
  '&laquo; See all relationships',
  array(
    'url' => $entity->getInternalUrl('relationships'),
    'update' => 'relationship_tabs_content',
    'method' => 'get',
    'loading' => "document.getElementById('indicator').style.display = 'block';",
    'complete' => "document.getElementById('indicator').style.display = 'none';"
  ),
  array(
    'return' => true,
    'href' => '#relationships',
    'class' => 'text_big'
  )
) ?>

<br />
<br />

<?php include_partial('global/section', array(
  'title' => 'Lobbying',
  'pager' => $lobbying_pager
)) ?>

<div class="padded">
<?php foreach ($lobbying_pager->execute() as $id => $ary) : ?>
  <?php include_partial('entity/entitywithrelationships', array(
    'related_entity' => $ary['entity'],
    'relationships' => $ary['rels'],
    'profiled_entity' => $entity
  )) ?>
<?php endforeach; ?>
</div>