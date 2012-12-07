<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<?php include_partial('entity/edittabs', array('entity' => $entity)) ?>

<?php include_partial('global/pointer', array('text' => 
'Hierarchy is only for when an organization has a single parent. 
For partnerships and other joint-ventures, add ownership relationships.'
)) ?>


<span class="text_big">Parent:</span>

<?php if ($entity->Parent->exists()) : ?>
  <?php echo entity_link($entity->Parent) ?> 
  <?php if ($sf_user->hasCredential('editor')) : ?>
    <?php echo link_to('remove', $entity->getInternalUrl('removeParent'), 'post=true confirm=Are you sure?') ?>
  <?php endif; ?>
<?php else : ?>
  NONE
<?php endif; ?>
  <?php if ($sf_user->hasCredential('editor')) : ?>
    <?php echo link_to('change', $entity->getInternalUrl('changeParent')) ?>
  <?php endif; ?>

<br />
<br />
<br />

<?php include_partial('global/section', array(
  'title' => 'Child Organizations',
  'action' => array(
    'credential' => 'editor',
    'text' => 'add',
    'url' => $entity->getInternalUrl('addChild')
  ),
  'pointer' => 'Subgroups of ' . $entity->name
)) ?>

<div class="padded">
<?php foreach ($childs as $child) : ?>
  <?php echo entity_link($child) ?> 
  <?php if ($sf_user->hasCredential('editor')) : ?>
    <?php echo link_to('remove', $entity->getInternalUrl('removeChild', array('child_id' => $child->id)), 'post=true confirm=Are you sure?') ?>
  <?php endif; ?>
  <br>
<?php endforeach; ?>
</div>