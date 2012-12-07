<?php $tabs = array(
  'Profile' => array(
    'url' => $entity->getInternalUrl(),
    'actions' => array('view', 'edit', 'editContact', 'images', 'uploadImage', 'addAddress', 'editAddress', 'addPhone', 'editPhone', 'addEmail', 'editEmail')
  ),
  'Network' => array(
    'url' => $entity->getInternalUrl('network'),
    'actions' => array('network')
  ),
  'Giving' => array(
    'url' => $entity->getInternalUrl('giving'),
    'actions' => array('giving', 'contributions', 'recipientDonors')
  )
) ?>

<?php if ($entity->hasExtension('Org')) : ?>
  <?php $tabs['Schools'] = array(
    'url' => $entity->getInternalUrl('schools'),
    'actions' => array('schools')
  ) ?>
<?php endif; ?>

<?php include_partial('global/tabs', array('tabs' => $tabs)) ?>