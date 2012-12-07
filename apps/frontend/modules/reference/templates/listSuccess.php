<?php use_helper('Date', 'LsText') ?>

<?php slot('header_text', $object->getName()) ?>
<?php slot('header_link', method_exists($object, 'getInternalUrl') ? $object->getInternalUrl() : strtolower(get_class($object)) . '/view?id=' . $object->id) ?>


<?php include_partial('global/section', array(
  'title' => 'Source Links',
  'action' => array(
    'credential' => 'contributor',
    'text' => 'add',
    'url' => 'reference/add?model=' . get_class($object) . '&id=' . $object->id
  ),
  'pointer' => 'Articles documenting info about ' . $object->getName()
)) ?>

<div class="padded">
<?php $refs = method_exists($object, 'getAllReferences') ? $object->getAllReferences() : $object->getReferencesByFields() ?>
<?php foreach ($refs as $ref) : ?>
  <span class="text_big"><?php echo link_to(ReferenceTable::getDisplayName($ref), $ref['source']) ?></span>
  <?php if ($sf_user->hasCredential('editor')) : ?>
    &nbsp;
    <span class="text_small"><?php echo link_to('edit', 'reference/edit?id=' . $ref['id']) ?></span>
  <?php endif; ?>
  <?php if ($sf_user->hasCredential('deleter')) : ?>
    &nbsp;
    <span class="text_small"><?php echo link_to('remove', 'reference/remove?id=' . $ref['id'], 'post=true confirm=Are you sure?') ?></span>  
  <?php endif; ?>
  <br />
<?php endforeach; ?>
</div>