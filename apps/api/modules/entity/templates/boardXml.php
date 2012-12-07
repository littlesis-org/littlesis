<?php $relCount = 0 ?>
<?php echo LsDataFormat::toXml($entity, 'Entity') ?>
<BoardMembers>
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
</BoardMembers>

<?php slot('num_results', array(
  'BoardMembers' => count($entities),
  'Relationships' => $relCount
)) ?>