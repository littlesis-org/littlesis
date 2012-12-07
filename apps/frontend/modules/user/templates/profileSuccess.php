<?php use_helper('Number') ?>

<?php include_partial('user/header', array('user' => $user, 'show_actions' => true)) ?>

<?php slot('rightcol') ?>
  <?php if (!cache('rightcol')) : ?>
  <?php include_partial('user/profileimage', array('profile' => $profile)) ?>
  <br />
  <?php cache_save() ?>
  <?php endif; ?>
<?php end_slot() ?>


<?php if (!cache('public', 600)) : ?>

<?php include_partial('global/section', array(
  'title' => 'Personal Info'
)) ?>

<div class="padded">
<?php include_partial('global/datatable', array(
  'data' => array(
    'About' => $profile->bio
  )
)) ?>
</div>


<?php if (!$profile->ranking_opt_out) : ?>

<?php $stats = $profile->getShortSummary() ?>

  <br />

  <?php include_partial('global/section', array(
      'title' => 'Analyst Summary',
      'pointer' => $sf_user->isAuthenticated() ? 'A brief summary of this analyst\'s edits.  Analysts earn points for making edits to the database.' : 'A brief summary of this analyst\'s edits.  Analysts earn points for making edits to the database. ' . link_to('Sign up','home/join') . ' to become an analyst.'
      )) ?>

  <div class="padded">

  Updated every 10 minutes
  <br />
  <br />  
  
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
<?php endif; ?>

<?php cache_save() ?>
<?php endif; ?>


<!-- ONLY USERS CAN SEE OTHER USERS' PERMISSIONS -->
<?php if ($sf_user->isAuthenticated()) : ?>
<br />


<?php include_partial('global/section', array(
      'title' => 'Permissions',
      'pointer' => 'A list of this user\'s editing capabilities.'
      )) ?>

<div class="padded">
<?php include_partial('global/datatable', array(
	'label_width' => '150px',
	'data' => array(
    'Contributor' => $profile->hasCredential('contributor'),
    'Editor' => $profile->hasCredential('editor'),
    'Lister' => $profile->hasCredential('lister'),
    'Deleter' => $profile->hasCredential('deleter'),
    'Merger' => $profile->hasCredential('merger'),
    'Importer' => $profile->hasCredential('importer')
	)
)) ?>
</div>
<?php endif; ?>
