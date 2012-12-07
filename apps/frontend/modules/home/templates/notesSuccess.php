<?php use_helper('Date') ?>


<?php include_partial('user/basic', array('profile' => $profile)) ?>


<?php
if ($groupId = $sf_request->getParameter('group_id'))
{
  $record = Doctrine::getTable('sfGuardGroup')->find($groupId);
  if ($sf_request->getParameter('analysts') == 1)
  {
    $analysts = LsDoctrineQuery::create()
        ->from('sfGuardUser u')
        ->leftJoin('u.Profile p')
        ->leftJoin('u.sfGuardUserGroup ug')
        ->where('ug.group_id = ?', $groupId)
        ->orderBy('ug.created_at DESC')
        ->execute();
  }
}
else if ($entityId = $sf_request->getParameter('entity_id'))
{
  $record = Doctrine::getTable('Entity')->find($entityId);
}
elseif ($relationshipId = $sf_request->getParameter('relationship_id'))
{
  $record = Doctrine::getTable('Relationship')->find($relationshipId);
}
elseif ($listId = $sf_request->getParameter('list_id'))
{
  $record = Doctrine::getTable('LsList')->find($listId);
}
if ($sf_request->getParameter('analysts') == 1 && !isset($groupId))
{
  $analysts = $record->getWatchersQuery()->execute();
}

?>


<?php include_partial('note/form', array(
  'form' => $note_form, 
  'user' => ($userId = $sf_request->getParameter('user_id')) ? Doctrine::getTable('sfGuardUser')->find($userId) : null,
  'record' => isset($record) ? $record : null,
  'analysts' => isset($analysts) ? $analysts : null,
  'compose' => $sf_request->getParameter('compose'),
  'private' => $sf_request->getParameter('private'),
  'static_header' => true,
  'networks' => isset($networks) ? $networks : null,
  'network_ids' => $sf_request->getParameter('network_ids')
)) ?>

<?php include_partial('global/section', array(
  'title' => $unread_notes > 0 ? 'Notes (' . $unread_notes . ')' : 'Notes',
  'pager' => $note_pager,
  'action' => array(
    'text' => $sf_request->getParameter('replies', 1) ? 'hide replies' : 'show replies',
    'url' => $sf_request->getParameter('replies', 1) ? 'home/notes?replies=0' : 'home/notes'
  )
)) ?>

<?php foreach ($new_notes as $note) : ?>
  <?php include_partial('note/full', array('note' => $note, 'reply' => ($note['user_id'] != $profile['User']['id']))) ?>    
<?php endforeach; ?>

<?php $ct = 0 ?>
<?php foreach ($note_pager->execute() as $note) : ?>

  <?php if ($ct < $unread_notes && $unread_notes > 0) : ?>
  
    <?php include_partial('note/full', array('note' => $note, 'reply' => ($note['user_id'] != $profile['User']['id']), 'new' => 1)) ?>
  <?php else : ?>
    <?php include_partial('note/full', array('note' => $note, 'reply' => ($note['user_id'] != $profile['User']['id']))) ?>    
  <?php endif; ?>
  <?php $ct++ ?>
<?php endforeach; ?>
