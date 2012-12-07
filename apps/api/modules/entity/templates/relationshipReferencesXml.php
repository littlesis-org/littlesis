<?php $refCount = 0 ?>
<?php echo LsDataFormat::toXml($entity, 'Entity') ?>
<Relationships>
<?php foreach ($rels as $rel) : ?>
  <?php $refs = $rel['References'] ?>
  <?php unset($rel['References']) ?>
  <Relationship>
    <?php echo LsDataFormat::toXml($rel) ?>
    <References>
    <?php foreach ($refs as $ref) : ?>
      <?php echo LsDataFormat::toXml($ref, 'Reference') ?>
      <?php $refCount++ ?>
    <?php endforeach; ?>
    </References>
  </Relationship>
<?php endforeach; ?>
</Relationships>

<?php slot('num_results', array(
  'Relationships' => count($rels),
  'References' => $refCount
)) ?>