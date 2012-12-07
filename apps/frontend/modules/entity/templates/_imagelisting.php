<?php use_helper('Date') ?>

<div class="image_listing hover">

<table>
	<tr>
		<td class="thumb">
			<?php echo link_to(image_tag($image->getProfilePath()), $image->url ? $image->url : 'entity/image?id=' . $image->id) ?>
		</td>
		<td class="info">
			<strong><?php echo $image->title ?></strong><br />
      <?php if ($image->url) : ?>
        (remote file)<br />
      <?php endif; ?>
			<br />
			<?php if ($image->caption) : ?>
				<?php echo $image->caption ?><br /><br />
			<?php endif; ?>
			<em>Posted <?php echo format_date($image->created_at) ?></em>
		</td>
		<?php if (isset($actions)) : ?>
		<td class="actions spaced">
			<?php foreach ($actions as $action) : ?>
				<?php if (isset($action['icon'])) : ?>
					<?php echo image_tag(
						sfConfig::get('sf_image_system_dir') . DIRECTORY_SEPARATOR . $action['icon'],
						'align=absmiddle'
					) ?>&nbsp;
				<?php endif; ?>
				<?php echo link_to($action['text'], $action['url'], isset($action['options']) ? $action['options'] : '') ?><br />
			<?php endforeach; ?>
		</td>
		<?php endif; ?>
	</tr>
</table>

</div>
