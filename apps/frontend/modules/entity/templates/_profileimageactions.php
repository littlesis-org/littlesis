<?php if ($image = $entity->getProfileImage()) : ?>
<div class="padded">
	<?php echo link_to('View all images', $entity->getInternalUrl('images')) ?><br />
	<?php echo link_to('Upload new image', $entity->getInternalUrl('uploadImage')) ?>
</div>
<?php endif; ?>
