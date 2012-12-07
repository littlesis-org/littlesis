<ul class="content_tabs">
<?php foreach ($tabs as $text => $url) : ?>
	<?php if ($url) : ?>
		<li><a href="<?php echo url_for($url) ?>"><?php echo $text ?></a></li>
	<?php else : ?>
		<li><a class="active"><?php echo $text ?></a></li>
	<?php endif; ?>
<?php endforeach; ?>
</ul>