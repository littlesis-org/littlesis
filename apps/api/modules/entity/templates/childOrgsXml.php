<?php echo LsDataFormat::toXml($entity, 'Entity') ?>
<ChildOrgs>
<?php foreach ($child_orgs as $child) : ?>
  <?php echo LsDataFormat::toXml($child, 'ChildOrg') ?>
<?php endforeach; ?>
</ChildOrgs>

<?php slot('num_results', array(
  'ChildOrgs' => count($child_orgs)
)) ?>