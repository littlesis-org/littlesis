<Entities>
<?php foreach ($data["entities"] as $e) : ?>
  <?php echo LsDataFormat::toXml($e, 'Entity') ?>
<?php endforeach; ?>
</Entities>
<Relationships>
<?php foreach ($data["rels"] as $r) : ?>
  <?php echo LsDataFormat::toXml($r, 'Relationship') ?>
<?php endforeach; ?>
</Relationships>