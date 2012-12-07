<?php slot('header_text', 'Down For Maintenance') ?>

<div class="form_errors">
<?php echo image_tag(
	'system' . DIRECTORY_SEPARATOR . 'error.png',
	'align=top'
) ?> This page has been temporarily disabled due to technical difficulties. We're working to get it back up ASAP.
<strong><?php echo link_to('Contact us', '@contact') ?></strong> for details.
</div>

<?php include_partial('error/whatsnext') ?>