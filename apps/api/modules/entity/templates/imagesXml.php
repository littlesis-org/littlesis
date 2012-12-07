<?php echo LsDataFormat::toXml($entity, 'Entity') ?>
<Images>
<?php foreach ($images as $image) : ?>
  <?php echo LsDataFormat::toXml($image, 'Image') ?>
<?php endforeach; ?>
</Images>

<?php slot('num_results', array(
  'Images' => count($images)
)) ?>