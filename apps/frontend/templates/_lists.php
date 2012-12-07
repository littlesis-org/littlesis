<?php include_partial('global/section', array(
  'title' => 'Lists',
  'pointer' => 'Analyst-created lists of people and orgs',
  'action' => array(
    'text' => 'add',
    'url' => EntityTable::getInternalUrl($entity, 'addList')
  )
)) ?>

<div class="padded">
<?php foreach (EntityTable::getLsListsById($entity['id']) as $list) : ?>
  <?php if (!$list['is_admin'] || $sf_user->hasCredential('editor')) : ?>
    <strong><?php echo link_to(($list['is_admin'] ? '*' : '') . $list['name'], LsListTable::getInternalUrl($list)) ?></strong>    
    <?php if ($list['rank']) : ?>
      [#<?php echo $list['rank'] ?>]
    <?php endif; ?>
    <?php if (($sf_user->hasCredential('editor') && !$list['is_admin']) || $sf_user->hasCredential('admin')) : ?>
      <span class="text_small">
       (<?php echo link_to('remove', 'entity/removeList?id=' . $list['le_id'], 'post=true') ?>)
      </span>
    <?php endif; ?>
    <br />
  <?php endif; ?>
<?php endforeach; ?>
</div>