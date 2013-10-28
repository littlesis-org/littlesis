<?php use_helper('Date') ?>

<?php include_partial('user/header', array('user' => $user, 'show_actions' => true)) ?>

<?php slot('rightcol') ?>
  <?php if (!$sf_user->isAuthenticated() || !cache('rightcol')) : ?>
    <?php include_partial('user/rightcol', array('profile' => $user->getProfile())) ?>
    <?php if ($sf_user->isAuthenticated()) : ?>
      <?php cache_save() ?>
    <?php endif; ?>
  <?php endif; ?>
<?php end_slot() ?>


<?php include_partial('note/formteaser', array('user' => $user)) ?>


<?php include_partial('global/section', array(
  'title' => 'Note History',
  'pager' => $note_pager,
  'pointer' => 'Notes posted by or alerting ' . $user->getProfile()->public_name,
  'action' => array(
    'text' => $sf_request->getParameter('replies', 1) ? 'hide mentions' : 'show mentions',
    'url' => $user->getInternalUrl('notes', $sf_request->getParameter('replies', 1) ? array('replies' => 0) : null)
  )
)) ?>

<?php foreach ($note_pager->execute() as $note) : ?>
  <?php include_partial('note/full', array('note' => $note, 'reply' => ($note['user_id'] != $user->id))) ?>
<?php endforeach; ?>