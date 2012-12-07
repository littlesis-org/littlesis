<?php $tabs = array(
  'Basic' => array(
    'url' => $entity->getInternalUrl('edit'),
    'actions' => array('edit')
  ),
  'Aliases' => array(
    'url' => $entity->getInternalUrl('editAliases'),
    'actions' => array('editAliases')
  )
) ?>

<?php if ($sf_user->hasCredential('admin')) : ?>
  <?php $tabs['API Keys'] = array(
    'url' => $entity->getInternalUrl('editExternalKeys'),
    'actions' => array('editExternalKeys')
  ) ?>  
<?php endif; ?>

<?php if ($sf_user->hasCredential('editor')) : ?>
  <?php $tabs['Custom Fields'] = array(
    'url' => $entity->getInternalUrl('editCustomFields'),
    'actions' => array('editCustomFields', 'editCustomField')
  ) ?>
<?php endif; ?>

<?php if ($entity['primary_ext'] == 'Org') : ?>
  <?php $tabs['Contact Info'] = array(
    'url' => $entity->getInternalUrl('editContact'),
    'actions' => array('editContact')
  ) ?>
<?php elseif ($sf_user->hasCredential('contacter') || $sf_user->hasCredential('admin')) : ?>
  <?php $tabs['Contact Info'] = array(
    'url' => $entity->getInternalUrl('editContact'),
    'actions' => array('editContact')
  ) ?>
<?php endif; ?>

<?php if ($entity->hasExtension('Org')) : ?>
<?php $tabs['Hierarchy'] = array(
  'url' => $entity->getInternalUrl('editHierarchy'),
  'actions' => array('editHierarchy', 'addChild', 'changeParent')
) ?>
<?php endif; ?>

<?php if ($sf_user->hasCredential('editor')) : ?>
  <?php $tabs['Industries'] = array(
    'url' => $entity->getInternalUrl('editIndustries'),
    'actions' => array('editIndustries', 'addIndustry')
  ) ?>
<?php endif; ?>

<?php include_partial('global/tabs', array('tabs' => $tabs)) ?>