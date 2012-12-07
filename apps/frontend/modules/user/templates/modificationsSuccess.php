<?php include_partial('user/header', array('user' => $user, 'show_actions' => true)) ?>

<?php slot('rightcol') ?>
  <?php if (!cache('rightcol')) : ?>
    <?php include_partial('user/rightcol', array('profile' => $user->getProfile())) ?>
  <?php cache_save() ?>
  <?php endif; ?>
<?php end_slot() ?>


<?php if (!cache('main', 600)) : ?>

<span class="text_big">
Below is a history of modifications <?php echo ($sf_user->isAuthenticated() && ($sf_user->getGuardUser()->id == $user->id)) ? 'you made' : 'made by this user' ?>, updated every 10 minutes.
</span>
<br />
<br />

<?php include_partial('global/section', array(
  'title' => 'Modifications'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Date', 'Object', 'Action', 'Changes'),
  'pager' => new LsDoctrinePager($user->getModificationsQuery()->setHydrationMode(Doctrine::HYDRATE_ARRAY), $page, $num),
  'row_partial' => 'modification/userlistrow'
)) ?>

<?php cache_save() ?>
<?php endif; ?>