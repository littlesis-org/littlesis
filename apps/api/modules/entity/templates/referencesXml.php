<?php echo LsDataFormat::toXml($entity, 'Entity') ?>
<References>
<?php foreach ($references as $reference) : ?>
  <?php echo LsDataFormat::toXml($reference, 'Reference') ?>
<?php endforeach; ?>
</References>

<?php slot('num_results', array(
  'References' => count($references)
)) ?>