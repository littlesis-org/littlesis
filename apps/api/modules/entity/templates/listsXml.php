<?php echo LsDataFormat::toXml($entity, 'Entity') ?>
<Lists>
<?php foreach ($lists as $list) : ?>
  <?php echo LsDataFormat::toXml($list, 'List') ?>
<?php endforeach; ?>
</Lists>

<?php slot('num_results', array(
  'Lists' => count($lists)
)) ?>