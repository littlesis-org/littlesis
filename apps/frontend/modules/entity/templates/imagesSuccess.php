<?php use_helper('Pager') ?>

<?php include_partial('entity/header', array('entity' => $entity)) ?>


<?php include_partial('global/section', array(
  'title' => 'Images',
  'pager' => $image_pager,
  'action' => array(
  	'credential' => 'contributor',
  	'text' => 'upload',
  	'url' => $entity->getInternalUrl('uploadImage')
  )
)) ?>

<div class="padded">
<?php foreach ($image_pager->execute() as $image) : ?>
  <?php $actions = array(
    array('text' => 'View',  'url' => $image->url ? $image->url : 'entity/image?id=' . $image->id)
  ); ?>

  <?php if ($sf_user->hasCredential('editor') && !$image->url) : ?>
    <?php $actions[] = array('text' => 'Edit', 'url' => 'entity/editImage?id=' . $image->id) ?>
  <?php endif; ?>

  <?php if ($sf_user->hasCredential('deleter')) : ?>
    <?php $actions[] = array('text' => 'Remove', 'url' => 'entity/removeImage?id=' . $image->id, 'options' => 'post=true confirm=Are you sure?') ?>
  <?php endif; ?>

  <?php if ($sf_user->hasCredential('editor') && (!$entity->getProfileImage() || $entity->getProfileImage()->id != $image->id)) : ?>
    <?php $actions[] = array('text' => 'Put on profile', 'url' => 'entity/featureImage?id=' . $image->id) ?>
  <?php endif; ?>
  
  <?php include_partial('entity/imagelisting', array(
    'image' => $image,
    'actions' => $actions
  )) ?>
<?php endforeach; ?>
</div>