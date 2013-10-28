<?php slot('header_text', $entity['name']) ?>
<?php slot('header_link', EntityTable::getInternalUrl($entity)) ?>
<?php slot('description_meta', EntityTable::generateMetaDescription($entity)) ?>

<?php $actions = array(); ?>
<?php if (!$entity['is_deleted'] && isset($show_actions) && $show_actions) : ?>
  <?php $actions = array(
    'add relationship' => array(
      'url' => EntityTable::getInternalUrl($entity,'addRelationship')
    ),
    'edit' => array(
      'url' => EntityTable::getInternalUrl($entity, 'edit')
    ),
    'flag' => array(
      'url' => 'home/contact?type=flag'
    ),
    'remove' => array(
      'url' => EntityTable::getInternalUrl($entity, 'remove'),
      'options' => 'post=true confirm=Are you sure you want to remove this ' . strtolower(EntityTable::getPrimaryExtensionName($entity)) . '?',
      'credential' => 'deleter'
    )
  ) ?>

  <?php if ($entity['primary_ext'] == 'Person' && class_exists('OsEntityTransaction') && $sf_user->hasCredential('importer')) : ?>
    <?php $donations = array(
      'url' => EntityTable::getInternalUrl($entity, 'matchDonations'),
      'condition' => $sf_user->hasCredential('importer')
    ) ?>

    <?php if (!OsEntityTransactionTable::countByEntityId($entity['id']) > 0) : ?>
      <?php $donations['disabled'] = true ?>
    <?php endif; ?>
    <?php $actions['match donations'] = $donations ?>
  <?php endif; ?>

  <?php $actions = $actions + array(
    'add bulk' => array(
      'url' => EntityTable::getInternalUrl($entity, 'addBulk'),
      'condition' => $sf_user->hasCredential('bulker')
    ),
    'refresh' => array(
      'url' => EntityTable::getInternalUrl($entity, 'refresh', array('ref' => $sf_request->getUri())),
      'credential' => 'admin'
    )
  ) ?>
<?php endif; ?>

<?php slot('header_actions', $actions) ?>