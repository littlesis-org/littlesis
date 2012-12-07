<?php use_helper('LsText') ?>

<?php slot('header_text', 'User Form Posts') ?>

<?php include_partial('global/datelimitform', array(
  'action' => 'post/list',
  'hidden_params' => array('user_id')
)) ?>

<br />
<br />

<?php if ($sf_request->getParameter('user_id')) : ?>
  <?php echo link_to('Show all', 'post/list') ?>
  <br />
  <br />
<?php endif; ?>

<?php include_partial('global/table', array(
  'columns' => array('Date', 'User', 'Module', 'Action', 'Params', 'Options'),
  'pager' => $post_pager,
  'row_partial' => 'post/listrow'
)) ?>