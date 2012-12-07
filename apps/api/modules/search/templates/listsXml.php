<Lists>
<?php foreach ($lists as $list) : ?>
  <List>
  <?php $num = $list['num_entities'] ?>
  <?php echo LsDataFormat::toXml(LsApi::filterResponseFields($list, 'LsList')) ?>
    <num_entities><?php echo $num ?></num_entities>
  </List>
<?php endforeach; ?>
</Lists>

<?php slot('num_results', array(
  'Lists' => count($lists)
)) ?>