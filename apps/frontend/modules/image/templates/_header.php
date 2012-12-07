<?php slot('header_text', $image->title) ?>
<?php slot('header_link', 'entity/image?id=' . $image->id) ?>

<?php if (isset($show_actions) && $show_actions) : ?>
  <?php slot('header_actions', array(
    'edit' => array(
      'url' => 'entity/editImage?id=' . $image->id,
      'credential' => 'editor'
    ),
    'remove' => array(
      'url' => 'entity/removeImage?id=' . $image->id,
      'options' => 'post=true confirm=Are you sure you want to remove this image?',
      'credential' => 'deleter'
    ),
    'changes' => array(
      'url' => 'entity/imageModifications?id=' . $image->id
    )
  )) ?>
<?php endif; ?>