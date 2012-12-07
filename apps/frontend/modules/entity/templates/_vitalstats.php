<!-- NAME-VALUE PAIRS -->
<?php include_partial('global/section', array(
  'title' => 'Basic Info',
  'action' => array(
    'text' => 'edit',
    'url' => EntityTable::getInternalUrl($entity, 'edit')
  )
)) ?>

<div class="padded margin_bottom">
<?php include_partial('extension/' . strtolower($entity['primary_ext']) . 'data', array(
  'entity' => $entity,
  'label_width' => '100px'
)) ?>

<?php $networkLinks = array() ?>
<?php if (EntityTable::isLocal($entity['id'])) : ?>
  <?php $networks = EntityTable::getNetworksById($entity['id']) ?>
  <?php foreach ($networks as $network) : ?>
    <?php $networkLinks[] = network_link($network) ?>
  <?php endforeach; ?>
<?php endif; ?>

<?php $addressLinks = array() ?>
<?php if ($entity['primary_ext'] == 'Org') : ?>
  <?php $addresses = Doctrine::getTable('Address')->findByEntityId($entity['id']) ?>
  <?php foreach ($addresses as $address) : ?>
    <?php $addressLinks[] = address_link($address) ?>
  <?php endforeach; ?>
<?php endif; ?>

<?php $categoryLinks = array() ?>
<?php $categories = OsEntity::getCategoriesForDisplay($entity['id']) ?>
<?php foreach ($categories as $category) : ?>
  <?php $categoryLinks[] = category_link($category) ?>
<?php endforeach; ?>

<?php include_partial('global/datatable', array(
  'data' => array(
    'Aliases' => $aliases = EntityTable::getAliasNamesById($entity['id'], false),
    'Networks' => $networkLinks,
    'Industries' => $categoryLinks,
    'Addresses<br>' . link_to('[edit]',EntityTable::getInternalUrl($entity, 'editContact')) => $addressLinks
  ),
  'label_width' => '100px'
)) ?>
</div>