<?php slot('header_text', 'User Views') ?>

<?php include_partial('global/datelimitform', array(
  'action' => 'view/list',
  'hidden_params' => array(
    'user_id',
    'object_model',
    'object_id'
  )
)) ?>

<br />
<br />

<?php foreach ($constraints as $constraint) : ?>
  <strong><?php echo $constraint ?></strong>
  <br />
<?php endforeach; ?>

<?php if (count($constraints)) : ?>
  <strong><?php echo link_to('Show all', 'view/list') ?></strong>
  <br />
<?php endif; ?>

<?php include_partial('global/table', array(
  'columns' => array('Date', 'User', 'Page', 'Options'),
  'pager' => $view_pager,
  'row_partial' => 'view/listrow'
)) ?>