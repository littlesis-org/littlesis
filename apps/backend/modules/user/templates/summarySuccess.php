<?php slot('header_text', 'Editing Summary') ?>
<?php slot('header_link', 'user/summary') ?>

<?php echo link_to('Active', 'user/list?active=1') ?>&nbsp;
<?php echo link_to('Inactive', 'user/list?active=0') ?>&nbsp;
<?php echo link_to('All', 'user/list') ?>&nbsp;
<?php echo link_to('Invite', 'user/invite') ?>&nbsp;
<?php echo link_to('Editing Summary', 'user/summary') ?>

<br />
<br />

<?php include_partial('global/datelimitform', array(
  'action' => 'user/summary',
  'hidden_params' => array(
    'user_id',
    'object_model',
    'object_id'
  )
)) ?>

<br />
<br />

<?php include_partial('global/userlimitform', array(
  'action' => 'user/summary'
)) ?>


&nbsp;&nbsp;

Edits in Last: &nbsp;

<?php //echo link_to('show users only', 'modification/list?users_only=1') ?>
<?php echo link_to('hour', 'user/summary?span=1') ?>&nbsp;
<?php echo link_to('6 hours', 'user/summary?span=6') ?>&nbsp;
<?php echo link_to('12 hours', 'user/summary?span=12') ?>&nbsp;
<?php echo link_to('day', 'user/summary?span=24') ?>&nbsp;
<?php echo link_to('3 days', 'user/summary?span=72') ?>&nbsp;
<?php echo link_to('week', 'user/summary?span=168') ?>&nbsp;
<?php echo link_to('month', 'user/summary?span=744') ?>
&nbsp;&nbsp;
Order by: &nbsp;
<?php echo link_to('last login', 'user/summary?order=login') ?>&nbsp;
<?php echo link_to('modifications', 'user/summary?order=modifications') ?>&nbsp;
<br />
<br />

<?php include_partial('global/table', array(
  'columns' => array('Name', 'Public Name', 'Email','Last login', 'Modifications'),
  'pager' => $user_pager,
  'row_partial' => 'user/summaryrow'
)) ?>