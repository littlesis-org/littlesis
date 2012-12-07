<EntityTypes>
<?php foreach ($types as $type) : ?>
  <?php echo LsDataFormat::toXml($type, 'EntityType') ?>
<?php endforeach; ?>
</EntityTypes>