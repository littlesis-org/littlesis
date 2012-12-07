<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => false)) ?>


<?php include_partial('global/section', array('title' => 'Addresses')) ?>

<?php foreach ($addresses as $address) : ?>
  <strong><?php echo link_to($address->getOneLiner(), 'entity/address?id=' . $address->id) ?></strong>
  <br />
<?php endforeach; ?>