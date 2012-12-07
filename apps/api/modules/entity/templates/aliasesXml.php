<Entity>
  <?php echo LsDataFormat::toXml($entity) ?>
  <Aliases>
  <?php foreach ($aliases as $alias) : ?>
    <Alias><?php echo LsDataFormat::toXml($alias) ?></Alias>  
  <?php endforeach; ?>
  </Aliases>
</Entity>