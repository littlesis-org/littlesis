<?php if ($chain) : ?>
<Chain>
<?php foreach ($entities as $id => $entity) : ?>
  <?php echo LsDataFormat::toXml($entity, 'Entity') ?>
<?php endforeach; ?>
</Chain>
<?php endif; ?>

<Chains>
<?php foreach ($chains as $degree => $ary) : ?>
  <Degree<?php echo $degree ?>>
  <?php foreach ($ary as $chain) : ?>
    <Chain>
    <?php foreach ($chain as $num => $entityId) : ?>
      <Entity><?php echo $entityId ?></Entity>
    <?php endforeach; ?>
    </Chain>
  <?php endforeach; ?>
  </Degree<?php echo $degree ?>>
<?php endforeach; ?>
</Chains>

<?php slot('num_results', array(
  'Chains' => count($chains[1]) + count($chains[2]) + count($chains[3]) + count($chains[4])
)) ?>