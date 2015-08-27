<div class="entity_profile_image">
<div onmouseover="showImageEdit();" onmouseout="hideImageEdit();" style="position: relative;">
  <a id="entity_profile_image_edit" href="<?php echo url_for(EntityTable::getInternalUrl($entity, 'uploadImage')) ?>" style="position: absolute; top: 0; right: 0; display: none; height: 16px; width: 16px;">
    <div style="background-color: #fff;">
      <?php echo image_tag('system/edit-pencil.png') ?>
    </div>
  </a>
<?php $image = EntityTable::getProfileImageById($entity['id']) ?>
<?php if ($image) : ?>
	<?php echo link_to(image_tag(ImageTable::getPath($image, 'face'), array('alt' => '')), EntityTable::getInternalUrl($entity, 'images')) ?>
  <?php slot('share_image', ImageTable::getPath($image, 'face')) ?>
<?php else : ?>
  <?php $file = ($entity['primary_ext'] == 'Person') ? 'anon.png' : 'anons.png' ?>
	<?php echo link_to(image_tag('system'.DIRECTORY_SEPARATOR.$file, array('alt' => 'Upload Image')), EntityTable::getInternalUrl($entity, 'uploadImage')) ?>
<?php endif; ?>

<?php if ($image['caption']) : ?>
  <div class="image_caption"><?php echo $image['caption'] ?></div>
<?php endif; ?>
</div>
</div>


<script>

function showImageEdit()
{
  document.getElementById('entity_profile_image_edit').style.display = 'block';
}

function hideImageEdit()
{
  document.getElementById('entity_profile_image_edit').style.display = 'none';
}

</script>