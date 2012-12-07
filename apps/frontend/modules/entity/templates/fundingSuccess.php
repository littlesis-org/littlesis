<?php use_helper('Pager') ?>


<?php include_partial('global/section', array(
  'title' => 'Committees With Common Donors',
  'pointer' => 'Donors to  ' . $entity->name . ' also made donations to these fundraising committees'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Committee', 'Donors'),
  'pager' => $recipient_pager,
  'row_partial' => 'entity/similarrecipientrow',
  'base_object' => $entity
)) ?>