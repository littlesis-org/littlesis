<?php echo LsDataFormat::toXml($entity1, 'Entity1') ?>
<?php echo LsDataFormat::toXml($entity2, 'Entity2') ?>
<Relationships>
<?php foreach ($rels as $rel) : ?>
  <?php echo LsDataFormat::toXml($rel, 'Relationship') ?>
<?php endforeach; ?>
</Relationships>

<?php slot('num_results', array(
  'Relationships' => count($rels)
)) ?>