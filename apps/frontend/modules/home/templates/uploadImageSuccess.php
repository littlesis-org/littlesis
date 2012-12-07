<?php slot('header_text', 'Your Account') ?>

<h2>Upload Profile Image</h2>

<?php include_partial('global/formerrors', array('form' => $upload_form)) ?>

Select an image from your computer to upload, or enter the URL of a remote image file.<br />
<br />
PNG, JPEG, and GIF formats are allowed.
Images can have a maximum file size of 5MB.<br />
<br />

<?php if ($profile->filename) : ?>
  <strong>Warning!</strong> Your existing image will be permanently deleted if you upload a new one.
  <br /><br />
<?php endif; ?>

<form action="<?php echo url_for('home/uploadImage') ?>" method="POST" enctype="multipart/form-data">
<?php echo $upload_form['_csrf_token'] ?>

<?php $label_width = '100px' ?>

<table>
	<?php include_partial('global/formfield', array('field' => $upload_form['file'])) ?>
	<?php include_partial('global/formspacer', array('text' => 'Or you can specify a remote file:')) ?>
	<?php include_partial('global/formfield', array('field' => $upload_form['url'])) ?>
	<?php include_partial('global/formspacer') ?>
	<tr>
		<td></td>
		<td class="form_submit">
			<input type="submit" value="Upload" />
		</td>
	</tr>
</table>


</form>
