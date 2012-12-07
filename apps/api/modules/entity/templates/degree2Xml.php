<?php $degree1Ids = '' ?>
<?php echo LsDataFormat::toXml($entity, 'Entity') ?>
<Degree2Entities>
<?php foreach ($entities as $entity) : ?>
  <?php echo LsDataFormat::toXml($entity, 'Entity') ?>
  <?php $degree1Ids .= ',' . $entity['degree1_ids'] ?>
<?php endforeach; ?>
</Degree2Entities>

<?php $degree1Ary = array_unique(explode(',', $degree1Ids)) ?>

<?php slot('num_results', array(
  'Degree2Entities' => count($entities),
  'Degree1Entities' => count($degree1Ary)
)) ?>