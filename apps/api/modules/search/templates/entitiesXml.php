<Entities>
<?php foreach ($entities as $entity) : ?>
  <?php echo LsDataFormat::toXml($entity, 'Entity') ?>
<?php endforeach; ?>
</Entities>

<?php slot('num_results', array(
  'Entities' => count($entities)
)) ?>