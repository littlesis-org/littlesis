<?php $orgAry = array() ?>
<?php echo LsDataFormat::toXml($entity, 'Entity') ?>
<Leaders>
<?php foreach ($entities as $entityId => $entityAry) : ?>
  <Leader>
    <?php $orgs = $entityAry['orgs'] ?>
    <?php unset($entityAry['orgs']) ?> 
    <?php echo LsDataFormat::toXml($entityAry['entity']) ?>
    <Orgs>
      <?php foreach ($orgs as $org) : ?>
        <?php echo LsDataFormat::toXml($org, 'Entity') ?>
        <?php $orgAry[$org['id']] = true ?>
      <?php endforeach; ?>
    </Orgs>
  </Leader>
<?php endforeach; ?>
</Leaders>

<?php slot('num_results', array(
  'Leaders' => count($entities),
  'Orgs' => count($orgAry)
)) ?>