<?php include_partial('global/section', array(
  'title' => 'Public Info',
  'action' => array(
    'text' => 'edit',
    'url' => 'home/editProfile'
  )
)) ?>

<div class="padded">
<?php include_partial('global/datatable', array(
	'label_width' => '40px',
	'data' => array(
		'Name' => $profile->show_full_name ? $profile->getName() : null,
		'About' => $profile->bio ? get_partial('global/excerpt', array('text' => $profile->bio, 'id' => 'about', 'less' => true, 'length' => 500)) : null
	)
)) ?>
</div>