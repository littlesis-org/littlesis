<?php use_helper('Number') ?>


<?php include_partial('user/basic', array('profile' => $profile)) ?>


<?php slot('rightcol') ?>
  <?php include_partial('home/rightcol', array('profile' => $profile)) ?>
<?php end_slot() ?>


<?php if (!cache('main', 600)) : ?>

<span class="text_big">
Below is a history of modifications you've made, updated every 10 minutes.
</span>
<br />
<br />

<?php include_partial('global/section', array(
  'title' => 'Modifications'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Date', 'Object', 'Action', 'Changes'),
  'pager' => $modification_pager,
  'row_partial' => 'modification/userlistrow'
)) ?>

<?php cache_save() ?>
<?php endif; ?>