<RelationshipCategories>
<?php foreach ($categories as $category) : ?>
  <?php echo LsDataFormat::toXml($category, 'RelationshipCategory') ?>
<?php endforeach; ?>
</RelationshipCategories>