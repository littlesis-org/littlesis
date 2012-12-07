<?php include_partial('entity/header', array('entity' => $entity)) ?>

<h2>Edit image</h2>

<?php include_partial('global/formerrors', array('form' => $edit_form)) ?>

<form action="<?php echo url_for('entity/editImage') ?>" method="POST">
<?php echo input_hidden_tag('id', $image->id) ?>
<?php echo $edit_form['_csrf_token'] ?>

<table>
  <tr>
    <td class="form_label">Image</td>
    <td>
      <?php echo image_tag('profile' . DIRECTORY_SEPARATOR . $image->filename) ?>
    </td>
  </tr>

  <?php include_partial('global/formspacer') ?>
  <?php include_partial('global/formfield', array('field' => $edit_form['title'])) ?>
  <?php include_partial('global/formfield', array('field' => $edit_form['caption'])) ?>
  <?php include_partial('global/formfield', array('field' => $edit_form['is_free'])) ?>

  <?php if (count($entity->Image) > 1 && !$image->is_featured) : ?>
    <?php include_partial('global/formfield', array('field' => $edit_form['is_featured'])) ?>
  <?php else : ?>
    <?php echo input_hidden_tag('image[is_featured]', 1) ?>
  <?php endif; ?>

	<tr>
		<td></td>
		<td class="form_submit">
			<input type="submit" value="Save" />
			<?php echo button_to('Cancel', $entity->getInternalUrl('images')) ?>
		</td>
	</tr>
</table>

</form>