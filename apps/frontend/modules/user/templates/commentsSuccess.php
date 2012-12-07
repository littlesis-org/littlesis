<?php use_helper('Date') ?>

<?php include_partial('user/header', array('user' => $user, 'show_actions' => true)) ?>

<?php slot('rightcol') ?>
  <?php if (!cache('rightcol')) : ?>
  <?php include_partial('user/profileimage', array('profile' => $user->getProfile())) ?>
  <br />
  <?php cache_save() ?>
  <?php endif; ?>
<?php end_slot() ?>


<?php if (!cache('main', 600)) : ?>

<span class="text_big">
Below is a history of comments <?php echo ($sf_user->isAuthenticated() && ($sf_user->getGuardUser()->id == $user->id)) ? 'you made' : 'made by this user' ?> about profiles and relationships, updated every 10 minutes.
</span>
<br />
<br />

<?php include_partial('global/section', array(
  'title' => 'Comments'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Date', 'Topic', 'Title'),
  'pager' => new LsDoctrinePager($user->getCommentsQuery(), $page, $num),
  'row_partial' => 'comment/userlistrow'
)) ?>

<?php cache_save() ?>
<?php endif; ?>