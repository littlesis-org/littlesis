<?php include_partial('entity/header', array('entity' => $entity)) ?>

<h2>Upload Image</h2>

<?php include_partial('global/formerrors', array('form' => $upload_form)) ?>

Select an image from your computer to upload, or enter the URL of a remote image file. 
If you upload your own image, it must be "free".<br />
<br />
PNG, JPEG, and GIF formats are allowed.
Images can have a maximum file size of 5MB.<br />
<br />

<form action="<?php echo url_for(EntityTable::getInternalUrl($entity, 'uploadImage')) ?>" method="POST" enctype="multipart/form-data">
<?php echo input_hidden_tag('id', $entity['id']) ?>
<?php echo $upload_form['_csrf_token'] ?>

<?php $label_width = '100px' ?>

<table>
	<?php include_partial('global/formfield', array('field' => $upload_form['file'])) ?>
	<?php include_partial('global/formspacer', array('text' => 'Or you can specify a remote file:')) ?>
	<?php include_partial('global/formfield', array('field' => $upload_form['url'])) ?>
	<?php include_partial('global/formspacer') ?>
	<?php include_partial('global/formfield', array('field' => $upload_form['is_free'])) ?>
	<?php include_partial('global/formfield', array('field' => $upload_form['title'], 'required' => true)) ?>
	<?php include_partial('global/formfield', array('field' => $upload_form['caption'], 'label' => 'Caption and/or attribution')) ?>
<?php if ($has_image) : ?>
	<?php include_partial('global/formfield', array('field' => $upload_form['is_featured'])) ?>
<?php endif; ?>
	<tr>
		<td></td>
		<td class="form_submit">
			<br />
			By submitting this form, I am ensuring that this content does not violate any copyrights. <br />
			Please see our <?php echo link_to("policy regarding copyright violations and repeat infringers", '@disclaimer') ?>. <br />
			<br />
			<input type="submit" value="Upload" />
		</td>
	</tr>
</table>


</form>
