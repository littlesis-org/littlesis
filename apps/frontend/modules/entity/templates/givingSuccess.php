<?php use_helper('Pager') ?>


<?php if ($entity['primary_ext'] == 'Person') : ?>
<!-- IF ENTITY IS PERSON -->


<?php include_partial('global/section', array(
  'title' => 'Donors to Similar Recipients',
  'pointer' => 'People and orgs that ' . $entity['name'] . ' donated to also received donations from:'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Donor', 'Recipients'),
  'pager' => $donor_pager,
  'row_partial' => 'entity/similardonorrow',
  'base_object' => $entity
)) ?>


<?php else : ?>
<!-- IF ENTITY IS ORG -->

<?php include_partial('global/section', array(
  'title' => 'People Have Given To',
  'pointer' => 'People with positions in ' . $entity['name'] . ' have made donations to:'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Recipient', 'Total', 'People'),
  'pager' => $person_recipient_pager,
  'row_partial' => 'entity/personrecipientrow',
  'base_object' => $entity
)) ?>

<?php endif; ?>