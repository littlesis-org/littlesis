<Relationships>
<?php foreach ($relationships as $relationship) : ?>
  <?php echo LsDataFormat::toXml($relationship, 'Relationship') ?>
<?php endforeach; ?>
</Relationships>

<?php slot('num_results', array(
  'Relationships' => count($relationships)
)) ?>