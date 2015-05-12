<?php include_partial('list/header', array('list' => $list)) ?>

<?php include_partial('global/formerrors', array('form' => array($list_form, $reference_form))) ?>

<form action="<?php echo url_for($list->getInternalUrl('edit', null, true)) ?>" method="POST">
<?php echo input_hidden_tag('id', $list->id) ?>

<table>
  <?php include_partial('reference/required', array('form' => $reference_form)) ?>

  <?php include_partial('global/form', array('form' => $list_form, 'field_names' => array('_csrf_token', 'name', 'description', 'is_ranked', 'custom_field_name'))) ?>

  <?php if ($sf_user->hasCredential('admin')) : ?>
    <?php include_partial('global/formfield', array('field' => $list_form['is_admin'])) ?>
    <?php include_partial('global/formfield', array('field' => $list_form['is_featured'])) ?>
  <?php endif; ?>    

  <tr>
    <td></td>
    <td>
      <?php echo submit_tag('Save') ?>
      </form>
      <?php echo button_to('Remove', 'list/remove?id=' . $list->id, 'post=true confirm=Are you sure you want to remove this list?') ?>
      <?php echo button_to('Cancel', 'list/view?id=' . $list->id) ?>
    </td>
  </tr>
</table>
</form>