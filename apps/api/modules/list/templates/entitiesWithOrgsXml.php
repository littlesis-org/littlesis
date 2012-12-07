<?php $orgAry = array() ?>
<?php echo LsDataFormat::toXml($list, 'List') ?>
<Entities>
<?php foreach ($entities as $entityId => $entityAry) : ?>
  <Entity>
    <?php $orgs = $entityAry['orgs'] ?>
    <?php unset($entityAry['orgs']) ?> 
    <?php echo LsDataFormat::toXml($entityAry['entity']) ?>
    <Orgs>
      <?php foreach ($orgs as $org) : ?>
        <?php echo LsDataFormat::toXml($org, 'Entity') ?>
        <?php $orgAry[$org['id']] = true ?>
      <?php endforeach; ?>
    </Orgs>
  </Entity>
<?php endforeach; ?>
</Entities>

<?php slot('num_results', array(
  'Entities' => count($entities),
  'Orgs' => count($orgAry)
)) ?>