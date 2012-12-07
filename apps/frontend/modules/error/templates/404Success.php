<?php slot('header_text', 'Oops! Page Not Found') ?>

<div class="form_errors">
<?php echo image_tag(
  'system' . DIRECTORY_SEPARATOR . 'error.png',
	'align=top'
) ?> The server returned a 404 response.
</div>

<p><strong>Did you type the URL?</strong><br />
You may have typed the address (URL) incorrectly. Check it to make sure you've got the exact right spelling, capitalization, etc.
</p>

<p><strong>Did you follow a link from somewhere else at this site?</strong><br />
If you reached this page from another part of this site, please <?php echo link_to('contact', '@contact') ?> us so we can correct our mistake.
</p>

<p><strong>Did you follow a link from another site?</strong><br />
Links from other sites can sometimes be outdated or misspelled. <?php echo link_to('Contact', '@contact') ?> us with details and we can try to contact the other site in order to fix the problem.
</p>

<?php include_partial('error/whatsnext') ?>