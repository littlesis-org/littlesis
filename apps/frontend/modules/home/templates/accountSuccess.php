<?php use_helper('Number') ?>

<?php slot('header_text', $profile->public_name) ?>
<?php slot('header_link', '@account') ?>

<?php slot('header_actions', array(
  'changes' => array(
    'url' => 'home/modifications'
  ),
  'notes' => array(
    'url' => 'home/notes'
  )
)) ?>  


<?php slot('rightcol') ?>
  <?php include_partial('home/profileimage', array('profile' => $profile)) ?>
  <br />
<?php end_slot() ?>

<?php include_partial('global/section', array(
  'title' => 'Public Profile',
  'action' => array(
    'text' => 'edit',
    'url' => 'home/editProfile'
  )
)) ?>

<div class="padded">
<?php include_partial('global/datatable', array(
	'label_width' => '150px',
	'data' => array(
		'Full Name' => $profile->show_full_name ? $profile->getName() : null,
		'About' => $profile->bio
	)
)) ?>
</div>

<br />

<?php if ($sf_user->hasCredential('contributor')) : ?>
  <?php include_partial('global/section', array('title' => 'Analyst Summary')) ?>
    <div class="padded">
  <?php if ($refreshed) : ?>
    <em>Your score has been refreshed.</em></div><div class="padded">
  <?php endif; ?>
  <table class="datatable">
    <tr class="text_big">  
      <td style="text-align: right; padding-right: .7em;">
        <strong><?php echo $profile->score ?></strong> 
      </td>
      <td>
        points    
      </td>
    </tr>
  
  <?php foreach ($stats as $label => $count) : ?>
  
    <tr class="text_big">
      <td style="text-align: right; padding-right: .7em;">
        <strong><?php echo format_number($count) ?></strong>
      </td>
      <td>
        new <?php echo LsLanguage::pluralize($label) ?>
      </td>
    </tr>
  
  <?php endforeach; ?>
  </table>
  </div>
  <br />
<?php endif; ?>


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
<?php include_partial('global/datatable', array(
	'label_width' => '150px',
	'data' => array(
	  'Email' => $profile->email,
		//'Use HTML Editor' => $profile->enable_html_editor,
		'Show Pointers' => $profile->enable_pointers,
		'Show Recent Views' => $profile->enable_recent_views,
		'Show Favorites' => $profile->enable_favorites,
		'Opt out of rankings' => $profile->ranking_opt_out,
		'Opt out of Who\'s Watching' => $profile->watching_opt_out
	)
)) ?>
</div>

<br />

<?php include_partial('global/section', array('title' => 'Permissions')) ?>

<div class="padded">
To request additional <?php echo link_to('permissions', '@guide#permissions') ?>, <?php echo link_to('contact', '@contact') ?> the LittleSis team.
<br />
<br />

<?php include_partial('global/datatable', array(
	'label_width' => '150px',
	'data' => array(
    'Contributor' => $sf_user->hasCredential('contributor'),
    'Editor' => $sf_user->hasCredential('editor'),
    'Lister' => $sf_user->hasCredential('lister'),
    'Deleter' => $sf_user->hasCredential('deleter'),
    'Merger' => $sf_user->hasCredential('merger'),
    'Admin' => $sf_user->hasCredential('admin')
	)
)) ?>
</div>


