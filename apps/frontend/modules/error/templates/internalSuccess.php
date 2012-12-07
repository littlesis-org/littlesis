<?php slot('header_text', 'Error') ?>

<div class="form_errors">
<?php echo image_tag(
	'system' . DIRECTORY_SEPARATOR . 'error.png',
	'align=top'
) ?> The action you requested produced an internal site error. 
If you encounter this error repeatedly, please <?php echo link_to('let us know', '@contact') ?> so we can fix it.
</div>

<?php include_partial('error/whatsnext') ?>