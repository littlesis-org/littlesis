<?php include_partial('user/header', array('user' => $user, 'show_actions' => true)) ?>

<?php slot('rightcol') ?>
  <?php if (!cache('rightcol')) : ?>
    <?php include_partial('user/rightcol', array('profile' => $user->getProfile())) ?>
  <?php cache_save() ?>
  <?php endif; ?>
<?php end_slot() ?>

Research groups allow LittleSis analysts to work together on research projects. 
Currently groups can only be created by LittleSis staff, but soon any analyst will be able to create them.
In the meantime, if you want to create a group to help you organize a project on LittleSis,
please <?php echo link_to('contact us', '@contact') ?>.

<br />
<br />

<?php include_partial('global/section', array(
  'title' => 'Research Groups'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Name', 'Analysts', 'Description', 'Created'),
  'rows' => $groups,
  'row_partial' => 'group/listrow'
)) ?>