<?php use_helper('Pager') ?>

<?php slot('header_text', 'User List') ?>

<?php slot('top') ?>
  <?php include_partial('global/topmenu') ?>
<?php end_slot() ?>

<?php echo link_to('Active', 'user/list?active=1') ?>&nbsp;
<?php echo link_to('Inactive', 'user/list?active=0') ?>&nbsp;
<?php echo link_to('All', 'user/list') ?>&nbsp;
<?php echo link_to('Invite', 'user/invite') ?>&nbsp;
<?php echo link_to('Editing Summary', 'user/summary') ?>


<br />
<br />
Limit user list by create date.&nbsp;

<?php include_partial('global/datelimitform', array(
  'action' => 'user/list',
  'hidden_params' => array(
    'user_id',
    'object_model',
    'object_id'
  )
)) ?>

<br />
<br />

<?php include_partial('global/userlimitform', array(
  'action' => 'user/list'
)) ?>

&nbsp;&nbsp;
Order by: 
<?php echo link_to('last login', 'user/list?order=login') ?>&nbsp;
<?php echo link_to('created', 'user/list?order=created') ?>&nbsp;
<?php echo link_to('alphabetic', 'user/list?order=alpha') ?>&nbsp;
<br />
<br />


<?php include_partial('global/table', array(
  'columns' => array('Name', 'Public Name', 'Email', 'Created On', 'Active', 'Last Login', 'Actions'),
  'pager' => $users_pager,
  'row_partial' => 'user/listrow'
)) ?>