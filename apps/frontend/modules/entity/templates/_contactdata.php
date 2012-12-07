<?php $addresses = array() ?>
<?php foreach ($entity->Address as $address) : ?>
  <?php $addresses[] = address_link($address) ?>
<?php endforeach; ?>

<?php $data = array(
  'Phone' => $entity->Phone,
  'Email' => $entity->Email,
  'Address' => $addresses
) ?>

<?php include_partial('global/datatable', array(
  'data' => $data,
  'label_width' => isset($label_width) ? $label_width : '80px'
)) ?>