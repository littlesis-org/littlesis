<Entities>
<?php foreach ($entities as $entity) : ?>
  <?php echo LsDataFormat::toXml(LsApi::filterResponseFields($entity, 'Entity'), 'Entity') ?>
<?php endforeach; ?>
</Entities>

<?php slot('num_results', array(
  'Entities' => count($entities)
)) ?>