<?php include_partial('global/section', array(
  'title' => 'References',
  'pointer' => 'Articles documenting info on this page',
  'actions' => array(
    array(
      'text' => 'add',
      'url' => 'reference/add?model=' . get_class($object) . '&id=' . $object->id,
      'credential' => 'contributor'
    ),
    array(
      'text' => 'details',
      'url' => 'reference/list?model=' . get_class($object) . '&id=' . $object->id
    )
  )
)) ?>

<div class="padded reference-list">
<?php $refs = method_exists($object, 'getAllReferences') ? $object->getAllReferences() : $object->getReferencesByFields() ?>
<?php foreach ($refs as $ref) : ?>
  <?php echo reference_link($ref, 50) ?><br />
<?php endforeach; ?>
</div>