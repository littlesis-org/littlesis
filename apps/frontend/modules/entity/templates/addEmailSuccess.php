<?php include_partial('entity/header', array('entity' => $entity)) ?>

<h2>Add Email</h2>

<?php include_partial('global/formerrors', array('form' => array($email_form, $reference_form))) ?>

<form action="<?php echo url_for($entity->getInternalUrl('addEmail', null, true)) ?>" method="POST">
<?php echo input_hidden_tag('id', $sf_request->getParameter('id')) ?>

<table>
  <?php include_partial('reference/required', array(
    'form' => $reference_form,
    'hide_bypass' => true
  )) ?>

  <?php include_partial('global/form', array('form' => $email_form)) ?>
  <tr>
    <td></td>
    <td>
      <?php echo submit_tag('Add') ?>
      <?php echo button_to('Cancel', $entity->getInternalUrl('editContact')) ?>
    </td>
  </tr>
</table>

</form>