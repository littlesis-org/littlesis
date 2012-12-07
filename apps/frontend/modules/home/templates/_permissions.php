<?php include_partial('global/section', array(
  'title' => 'Permissions',
  'pointer' => 'To request additional ' . link_to('permissions', '@guide#permissions') . ', ' . link_to('contact', '@contact') . ' the LittleSis team'
)) ?>

<div class="padded">
<?php include_partial('global/datatable', array(
	'label_width' => '150px',
	'data' => array(
    'Contributor' => $sf_user->hasCredential('contributor'),
    'Editor' => $sf_user->hasCredential('editor'),
    'Lister' => $sf_user->hasCredential('lister'),
    'Deleter' => $sf_user->hasCredential('deleter'),
    'Merger' => $sf_user->hasCredential('merger'),
    'Importer' => $sf_user->hasCredential('importer')
	)
)) ?>
</div>