<?php use_helper('Pager') ?>

<table class="list_table">

<?php if (isset($pager)) : ?>  
<?php $pager->execute() ?>
<?php if ($pager->getLastPage() > 1) : ?>
  <tr>
    <td class="list_table_nav" colspan="<?php echo count($columns) ?>">
      <?php $sort = isset($sort) ? $sort : null ?>
      <?php if (isset($more)) : ?>
        <?php echo pager_meta_sample($pager, $more, $sort) ?>
      <?php else : ?>
        <?php echo pager_meta($pager, $sort) ?>
      <?php endif; ?>
    </td>
  </tr>
<?php endif; ?>
<?php endif; ?>

<?php if ( (isset($pager) && $pager->getNumResults()) || (isset($rows) && count($rows)) ) : ?>
  <?php if (isset($columns)) : ?>
  <tr>
  <?php foreach ($columns as $column) : ?>
    <th><?php echo $column ?></th>
  <?php endforeach; ?>
  </tr>
  <?php endif; ?>
<?php endif; ?>

<?php $objects = isset($pager) ? $pager->execute() : $rows ?>

<?php $shaded = false ?>
<?php foreach ($objects as $object) : ?>
  <?php include_partial($row_partial, array(
    'object' => $object,
    'base_object' => isset($base_object) ? $base_object : null,
    'hover' => isset($hover) ? $hover : null,
    'shaded' => $shaded,
    'holder' => isset($holder) ? $holder : null
  )) ?>    
  <?php if (isset($alternate)) : ?>
    <?php $shaded = !$shaded ?>
  <?php endif; ?>
<?php endforeach; ?>

</table>