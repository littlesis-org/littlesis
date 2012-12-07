<?php include_partial('global/subsection', array(
  'title' => 'Child Organizations',
  'pager' => $children_pager,
  'pointer' => 'Subgroups of ' . $entity->name
)) ?>

<div class="padded">
<?php foreach ($children_pager->execute() as $child) : ?>
  <?php echo entity_link($child) ?>
  <br />
<?php endforeach; ?>
</div>
<br />
