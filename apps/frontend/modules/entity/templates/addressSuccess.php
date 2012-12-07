<?php include_partial('address/header', array('address' => $address, 'show_actions' => true)) ?>

<?php slot('rightcol') ?>
  <?php include_partial('address/sidebar', array('address' => $address)) ?>
  <br />
  <?php include_partial('global/references', array('object' => $address)) ?>
<?php end_slot() ?>


<?php $data = array(
  $entity->getPrimaryExtension(true) => entity_link($entity),
  'Type' => $address->Category->name,
  'Latitude' => $address->latitude,
  'Longitude' => $address->longitude
) ?>

<?php include_partial('global/datatable', array(
  'data' => $data
)) ?>

<br />
<br />


<?php if (isset($neighbors_pager)) : ?>

<?php if (count($neighbors_pager)) : ?>

<?php include_partial('global/section', array(
  'title' => 'Also at this address',
  'pager' => $neighbors_pager
)) ?>

<div class="padded">
<?php foreach ($neighbors_pager->execute() as $entity) : ?>
  <?php echo entity_link($entity) ?>
  <br />
<?php endforeach; ?>
</div>

<br />
<br />

<?php endif; ?>

<?php endif; ?>

<?php if (isset($nearby_address_pager) && $nearby_address_pager->getCountQuery()->count()) : ?>

<?php include_partial('global/section', array(
  'title' => 'Nearby Entities',
  'pager' => $nearby_address_pager
)) ?>

<div class="padded">
<?php foreach ($nearby_address_pager->execute() as $address) : ?>
  <?php echo entity_link($address->Entity) ?> (<?php echo link_to($address->getOneLiner(), 'entity/address?id=' . $address->id) ?>)
  <br />
<?php endforeach; ?>
</div>

<?php endif; ?>
