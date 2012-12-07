<?php use_helper('LsText') ?>

<?php slot('header_text', 'API Log') ?>

<?php if (isset($api_user)) : ?>
  Limiting to requests by <strong><?php echo $api_user['email'] ?></strong>.
  <?php echo link_to('See all &raquo;', 'api/log') ?>
  <br />
  <br />
<?php endif; ?>

<?php include_partial('global/table', array(
  'columns' => array('Name', 'Email', 'Resource', 'Date/Time'),
  'pager' => $request_pager,
  'row_partial' => 'api/requestlistrow'
)) ?>