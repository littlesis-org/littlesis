<?php include_partial('entity/header', array('entity' => $entity)) ?>

<?php slot('rightcol') ?>
  <?php include_partial('address/sidebar', array('address' => $address)) ?>
<?php end_slot() ?>

<h2>Edit Address</h2>

<?php include_partial('global/formerrors', array('form' => array($address_form, $reference_form))) ?>

<form action="<?php echo url_for('entity/editAddress') ?>" method="POST">
<?php echo input_hidden_tag('id', $address->id) ?>

<table>
  <?php include_partial('reference/required', array('form' => $reference_form)) ?>

  <?php include_partial('global/form', array('form' => $address_form)) ?>
  <td></td>
  <td>
    <?php echo submit_tag('Save') ?>
    <?php echo button_to('Cancel', 'entity/address?id=' . $address->id) ?>
</table>
</form>