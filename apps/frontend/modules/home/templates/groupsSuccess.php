<?php include_partial('user/header', array('user' => $profile->User, 'show_actions' => true)) ?>

<?php include_partial('user/basic', array('profile' => $profile)) ?>

Research groups allow LittleSis analysts to work together on research projects. 
Currently groups can only be created by LittleSis staff, but soon any analyst will be able to create them.
In the meantime, if you want to create a group to help you organize a project on LittleSis,
please <?php echo link_to('contact us', '@contact') ?>.

<br />
<br />

<?php include_partial('global/section', array(
  'title' => 'Your Research Groups'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Name', 'Analysts', 'Description', 'Created'),
  'rows' => $groups,
  'row_partial' => 'group/listrow'
)) ?>