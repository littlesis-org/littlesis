<?php include_partial('global/section', array(
  'title' => 'Permissions',
  'pointer' => 'Read more about ' . link_to('permissions', '@guide#permissions')
)) ?>

<div class="padded">
<?php include_partial('global/datatable', array(
	'label_width' => '150px',
	'data' => array(
    'Contributor' => $user->hasCredential('contributor'),
    'Editor' => $user->hasCredential('editor'),
    'Lister' => $user->hasCredential('lister'),
    'Deleter' => $user->hasCredential('deleter'),
    'Merger' => $user->hasCredential('merger'),
    'Importer' => $user->hasCredential('importer')
	)
)) ?>
</div>