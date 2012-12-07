<?php slot('header_text', 'Add List') ?>

<?php include_partial('global/formerrors', array('form' => array($list_form, $reference_form))) ?>

<form action="<?php echo url_for('list/add') ?>" method="POST">

<table>
  <?php include_partial('reference/required', array(
    'form' => $reference_form,
    'hide_bypass' => true
  )) ?>
  
  <?php include_partial('global/form', array('form' => $list_form, 'field_names' => array('_csrf_token', 'name', 'description', 'is_ranked'))) ?>

  <?php if ($sf_user->hasCredential('admin')) : ?>
    <?php include_partial('global/formfield', array('field' => $list_form['is_admin'])) ?>
    <?php include_partial('global/formfield', array('field' => $list_form['is_featured'])) ?>
  <?php endif; ?>    

  <tr>
    <td></td>  
    <td>
      <?php echo submit_tag('Add') ?>
    </td>
  </tr>
</table>
</form>