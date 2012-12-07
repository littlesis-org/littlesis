<div id="image_gallery" style="visibility: hidden;<?php echo isset($max_height) ? ' max-height: ' . $max_height . ';' : '' ?>">
<?php foreach ($images as $image) : ?>
  <?php echo link_to(image_tag('small/' . $image['filename'], array('title' => $image['Entity']['name'], 'alt' => '')), EntityTable::generateRoute($image['Entity'])) ?>
<?php endforeach; ?>
</div>

<script type="text/javascript">
if (window.innerHeight !== undefined)
{
  document.getElementById('image_gallery').style.height = window.innerHeight + 'px';
}
else
{
  document.getElementById('image_gallery').style.height = document.documentElement.clientHeight + 'px';
}

window.onload = function() {
  gallery = document.getElementById('image_gallery');
  gallery.style.visibility = 'visible';
}
</script>