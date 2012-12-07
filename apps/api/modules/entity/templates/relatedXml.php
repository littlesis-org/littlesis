<?php $relCount = 0 ?>
<?php $categories = array() ?>
<?php echo LsDataFormat::toXml($entity, 'Entity') ?>
<RelatedEntities>
<?php foreach ($entities as $entityId => $entity) : ?>
  <Entity>
    <?php $rels = $entity['Relationships'] ?>
    <?php unset($entity['Relationships']) ?> 
    <?php echo LsDataFormat::toXml($entity) ?>
    <Relationships>
      <?php foreach ($rels as $rel) : ?>
        <?php echo LsDataFormat::toXml($rel, 'Relationship') ?>
        <?php $categories[$rel['category_id']] = 1 ?>
        <?php $relCount++ ?>
      <?php endforeach; ?>
    </Relationships>
  </Entity>
<?php endforeach; ?>
</RelatedEntities>

<?php slot('num_results', array(
  'RelatedEntities' => count($entities),
  'Relationships' => $relCount,
  'RelationshipCategories' => count($categories)
)) ?>