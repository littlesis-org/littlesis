<?php $aliases = $entity->getAliasNames(false,$sf_user->hasCredential('admin') ? false : true) ?>
<?php if ($aliases) : ?>

<?php include_partial('global/section', array(
  'title' => 'Aliases',
  'pointer' => $entity->name . ' is also known as:',
  'action' => array(
    'text' => 'edit',
    'url' => $entity->getInternalUrl('editAliases'),
    'credential' => 'editor'
  )
)) ?>

<div class="padded alias-list">
<?php foreach ($aliases as $alias) : ?>
  <?php echo $alias ?><br />
<?php endforeach; ?>
</div>

<br />

<?php endif; ?>