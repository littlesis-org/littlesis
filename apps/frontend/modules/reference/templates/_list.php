<?php include_partial('global/section', array(
  'title' => 'Source Links',
  'pointer' => 'Articles documenting info on this page',
  'actions' => array(
    array(
      'text' => 'add',
      'url' => 'reference/add?model=' . $model . '&id=' . $object['id'],
      'credential' => 'contributor'
    ),
    array(
      'text' => 'edit',
      'url' => $more_uri,
      'credential' => 'contributor'
    )
  ),
  'pager' => $pager,
  'more' => $more_uri
)) ?>

<div class="padded reference-list">
<?php foreach ($pager->execute() as $ref) : ?>
  <?php echo reference_link($ref, 40) ?><br />
<?php endforeach; ?>
</div>