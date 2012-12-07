<div class="entity_profile_image">
<?php $image = $profile->filename ?>
<?php if ($image) : ?>
	<?php echo image_tag('profile'.DIRECTORY_SEPARATOR.$image) ?>
	</div>
<?php else : ?>
	<?php echo image_tag('system'.DIRECTORY_SEPARATOR.'user_profile.png') ?>
	</div>
<?php endif; ?>
