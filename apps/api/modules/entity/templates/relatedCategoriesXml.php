<?php $relCount = 0 ?>
<?php $entities = array() ?>
<?php echo LsDataFormat::toXml($entity, 'Entity') ?>
<RelationshipCategories>
<?php foreach ($categories as $catId => $relatedEntities) : ?>
  <Category>
    <id><?php echo $catId ?></id>
    <RelatedEntities>
    <?php foreach ($relatedEntities as $relatedEntityId => $relatedEntity) : ?>
      <Entity>
        <?php $rels = $relatedEntity['Relationships'] ?>
        <?php unset($relatedEntity['Relationships']) ?> 
        <?php echo LsDataFormat::toXml($relatedEntity) ?>
        <Relationships>
          <?php foreach ($rels as $rel) : ?>
            <?php echo LsDataFormat::toXml($rel, 'Relationship') ?>
            <?php $relCount++ ?>
          <?php endforeach; ?>
        </Relationships>
      </Entity>
      <?php $entities[$relatedEntityId] = 1 ?>
    <?php endforeach; ?>
    </RelatedEntities>
  </Category>
<?php endforeach; ?>
</RelationshipCategories>

<?php slot('num_results', array(
  'RelatedEntities' => count($entities),
  'Relationships' => $relCount,
  'RelationshipCategories' => count($categories)
)) ?>