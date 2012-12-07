<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<?php include_partial('entity/edittabs', array('entity' => $entity)) ?>


<h2><?php echo $sf_request->getParameter('key') ? 'Edit' : 'Add' ?> Custom Field</h2>



<?php include_partial('global/formerrors', array('forms' => array($key_form, $reference_form))) ?>


<form action="<?php echo url_for($entity->getInternalUrl('editCustomField')) ?>" method="POST">

<?php if ($key) : ?>
  <?php echo input_hidden_tag('key', $key) ?>
<?php endif; ?>

<?php echo $key_form['_csrf_token'] ?>

<table>
  <?php include_partial('reference/required', array(
    'form' => $reference_form,
    'bypass_text' => $sf_request->getParameter('key') ? '' : 'No source'
  )) ?>

<?php if (!$key && count($reserved_keys)) : ?>
  <tr>
    <td class="form_label">Reserved field names:</td>
    <td><?php echo implode(', ', $reserved_keys) ?></td>
  </tr>
  <?php include_partial('global/formspacer') ?>

<?php endif; ?>

<?php if ($key) : ?>
  <tr>
    <td class="form_label"><?php echo $key_form['name']->renderLabel() ?></td>  
    <td class="form_value">
      <strong><?php echo $key_form['name']->getValue() ?></strong>
      <?php echo input_hidden_tag('custom_key[name]', $key_form['name']->getValue()) ?>
    </td>
  </tr>
<?php else : ?>
  <?php include_partial('global/formfield', array('field' => $key_form['name'])) ?>
<?php endif; ?>

  <?php include_partial('global/formfield', array('field' => $key_form['value'])) ?>

  <tr>
    <td></td>
    <td>
      <input class="button_small" type="submit" value="Save">
      <?php echo button_to('Cancel', $entity->getInternalUrl('editCustomFields'), 'class=button_small') ?>
    </td>
  </tr>
</table>

</form>
