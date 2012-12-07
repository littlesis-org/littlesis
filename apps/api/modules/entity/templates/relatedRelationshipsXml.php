<?php $entities = array() ?>
<?php $categories = array() ?>
<?php echo LsDataFormat::toXml($entity, 'Entity') ?>
<Relationships>
  <?php foreach ($relationships as $rel) : ?>
  <Relationship>
    <?php $relatedEntity = ($rel['entity1_id'] == $entity['id']) ? $rel['Entity2'] : $rel['Entity1'] ?>
    <?php unset($rel['Entity1']) ?>
    <?php unset($rel['Entity2']) ?>
    <?php echo LsDataFormat::toXml($rel) ?>
    <?php echo LsDataFormat::toXml($relatedEntity, 'RelatedEntity') ?>
    <?php $entities[$relatedEntity['id']] = 1 ?>
    <?php $categories[$rel['category_id']] = 1 ?>
  </Relationship>
  <?php endforeach; ?>
</Relationships>

<?php slot('num_results', array(
  'RelatedEntities' => count($entities),
  'Relationships' => count($relationships),
  'RelationshipCategories' => count($categories)
)) ?>