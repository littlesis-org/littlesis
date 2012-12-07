<?php $relCount = 0 ?>
<?php echo LsDataFormat::toXml($entity, 'Entity') ?>
<Boards>
<?php foreach ($entities as $entityId => $entity) : ?>
  <Entity>
    <?php $rels = $entity['Relationships'] ?>
    <?php unset($entity['Relationships']) ?> 
    <?php echo LsDataFormat::toXml($entity) ?>
    <Relationships>
      <?php foreach ($rels as $rel) : ?>
        <?php echo LsDataFormat::toXml($rel, 'Relationship') ?>
        <?php $relCount++ ?>
      <?php endforeach; ?>
    </Relationships>
  </Entity>
<?php endforeach; ?>
</Boards>

<?php slot('num_results', array(
  'Boards' => count($entities),
  'Relationships' => $relCount
)) ?>