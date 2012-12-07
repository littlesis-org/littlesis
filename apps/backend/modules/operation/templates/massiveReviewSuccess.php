<?php use_helper('Javascript') ?>

<?php slot('header_text', 'Review Massive') ?>

<?php slot('top') ?>
  <?php include_partial('global/topmenu') ?>
<?php end_slot() ?>


<?php echo link_to('Upload another file','operation/upload') ?>&nbsp;
<br />


<?php if (isset($edits)) : ?>

<?php if ($edits && count($edits)) : ?>
<table>
  <tr>
    <?php $keys = array_keys($edits[0]) ?>
    <?php foreach ($keys as $key) : ?>
      <th>
        <?php echo $key ?>
      </th>
    <?php endforeach; ?>
  </tr>
  <?php foreach ($edits as $edit) : ?>
    <tr class="hover">
    <?php foreach ($edit as $e) : ?>
      <td width="150px">
        <?php if (is_array($e)) : ?>
          <?php foreach ($e as $n): ?>
            <?php echo link_to($n->name, frontend_base() . '/entity/view?id= ' . $n->id) ?>
            <br />
          <?php endforeach; ?>
        <?php elseif (get_class($e) == 'Entity') : ?>   
          <?php echo link_to($e->name, frontend_base() . '/entity/view?id= ' . $e->id) ?>
        <?php elseif (get_class($e) == 'Relationship') : ?>
          <?php echo link_to($e->name, frontend_base() . '/' . $e->getInternalUrl()) ?>
        <?php else : ?>
          <?php echo $e ?>
        <?php endif; ?>
      </td>
    <?php endforeach; ?>
    </tr>
  <?php endforeach; ?>
</table>
<?php else : ?>
  <div class="padded">
    <em>No changes were made.</em>
  </div>
<?php endif; ?>

<?php endif; ?>


<?php if (isset($errors)) : ?>

  <?php foreach($errors as $error) : ?>
    <span class="text_small"><?php echo $error ?></span><br />
  <?php endforeach; ?>
  
<?php endif; ?>