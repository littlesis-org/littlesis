<?php if (isset($notes) && count($notes)) : ?>

<?php include_partial('global/section', array(
  'title' => 'Your Notes',
  'action' => array(
    'text' => 'see all',
    'url' => $record->getInternalUrl('notes', array('user_id' => $sf_user->getGuardUser()->id))
  )
)) ?>

<?php foreach ($notes as $note) : ?>
  <?php include_partial('note/short', array('note' => $note)) ?>
<?php endforeach; ?>

<br />

<?php endif; ?>