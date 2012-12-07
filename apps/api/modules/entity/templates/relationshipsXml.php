<?php echo LsDataFormat::toXml($entity, 'Entity') ?>
<Relationships>
<?php foreach ($rels as $rel) : ?>
  <?php echo LsDataFormat::toXml($rel, 'Relationship') ?>
<?php endforeach; ?>
</Relationships>

<?php slot('num_results', array(
  'Relationships' => count($rels)
)) ?>