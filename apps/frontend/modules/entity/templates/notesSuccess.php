<?php use_helper('Date') ?>

<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<?php slot('rightcol') ?>
  <?php include_partial('entity/profileimage', array('entity' => $entity)) ?>
<?php end_slot() ?>


<?php include_partial('note/formteaser', array('form' => $note_form, 'record' => $entity)) ?>


<?php include_partial('global/section', array(
  'title' => $sf_request->getParameter('user_id') ? 'Your Notes' : 'Notes',
  'pager' => $note_pager,
  'pointer' => ($sf_request->getParameter('user_id') ? 'Your notes' : 'Analyst notes') . ' about ' . $entity['name'],
  'action' => !$sf_user->isAuthenticated() ? null : array(
    'text' => $sf_request->getParameter('user_id') ? 'all notes' : 'your notes',
    'url' => $entity->getInternalUrl('notes', array('user_id' => $sf_request->getParameter('user_id') ? null : $sf_user->getGuardUser()->id))
   )
)) ?>

<?php foreach ($note_pager->execute() as $note) : ?>
  <?php include_partial('note/full', array('note' => $note)) ?>
<?php endforeach; ?>