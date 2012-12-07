<div class="entity_profile_image">
<?php $image = $profile->filename ?>
<?php if ($image) : ?>
	<?php echo image_tag('profile'.DIRECTORY_SEPARATOR.$image) ?>
	</div>
  <div class="padded">
	<?php echo link_to('Upload new image','home/uploadImage') ?>
	<br />
	<?php echo link_to('Remove this image', 'home/removeImage', 'post=true confirm=Are you sure you want to remove your current profile image?') ?>
  </div>
<?php else : ?>
	<?php echo image_tag('system'.DIRECTORY_SEPARATOR.'user_profile.png') ?>
	</div>
  <div class="padded">
	<?php echo link_to('Upload image','home/uploadImage') ?>
	</div>
<?php endif; ?>
