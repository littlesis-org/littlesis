<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<?php include_partial('entity/edittabs', array('entity' => $entity)) ?>

<?php include_partial('global/section', array('title' => 'Fields', 'action' => array(
  'text' => 'add',
  'url' => $entity->getInternalUrl('editCustomField')
))) ?>
  

<table class="custom-key-table">
<?php foreach ($keys as $key => $value) : ?>
  <tr>
    <td style="width: 80px;">
      <?php if ($sf_user->hasCredential('editor')) : ?>
        <?php echo link_to('edit', 'entity/editCustomField?id=' . $entity->id . '&key=' . $key) ?>
        &nbsp;
      <?php endif; ?>
      <?php if ($sf_user->hasCredential('deleter')) : ?>
        <?php echo link_to('remove', 'entity/removeCustomField?id=' . $entity->id . '&key=' . $key, 'post=true confirm=Are you sure you want to remove this field?') ?>
      <?php endif; ?>
    </td>
    <td><strong><?php echo $key ?></strong></td>
    <td><?php echo nl2br($value) ?></td>
  </tr>  
<?php endforeach; ?>
</table>