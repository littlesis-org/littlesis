<?php use_helper('Number') ?>


<?php include_partial('user/basic', array('profile' => $profile)) ?>



<?php include_partial('global/section', array(  
  'title' => 'Account Settings',
  'actions' => array(
    array(
      'text' => 'edit',
      'url' => 'home/editSettings'),
    array(
      'text' => 'change password',
      'url' => 'home/changePassword')
  )
)) ?>

<div class="padded">
<?php $network = Doctrine::getTable('LsList')->find($profile->home_network_id) ?>
<?php include_partial('global/datatable', array(
	'label_width' => '200px',
	'data' => array(
	  'Email' => $profile->email,
	  'Home Network' => network_link($network),
		'Receive announcements' => $profile->enable_announcements,
		'Receive Notes notifications' => $profile->enable_notes_notifications,
		'Show pointers' => $profile->enable_pointers,
		'Show Recent Views' => $profile->enable_recent_views,
		'Show Notes list' => $profile->enable_notes_list,
		'Opt out of rankings' => $profile->ranking_opt_out,
		'Opt out of Who\'s Watching' => $profile->watching_opt_out
	)
)) ?>
</div>

