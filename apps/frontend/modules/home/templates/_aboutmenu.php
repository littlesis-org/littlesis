<?php $this_page = $sf_request->getParameter('action') ?>
<?php if ($sf_request->getParameter('module') != 'home') : ?>
	<?php $this_page = 'blog' ?>
<?php endif; ?>
<?php $links = array(
	'Purpose' => 'purpose',
	'Features' => 'features',
	'Our Team' => 'team',
	'Funding' => 'funding',
	'Sign Up' => 'join',
	'Home' => 'homepage'
) ?>

<?php slot('usercol') ?>

<div id="about_links">
<p style="text-align: right;">
<?php foreach ($links as $name => $url) : ?>
	<span><?php echo link_to($name, '@' . $url, ($this_page == $url) ? 'class=active' : '') ?></span><br />
<?php endforeach; ?>
</p>
</div>

<?php end_slot(); ?>